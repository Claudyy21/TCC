<?php

namespace App\Models;

use CodeIgniter\Model;

class AtendimentoModel extends Model
{
    protected $table            = 'atendimentos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'rotina_id',
        'tipo_falha_id',
        'modo_atendimento_id',
        'descricao_problema',
        'descricao_solucao',
        'data_abertura',
        'data_resposta',
        'data_fechamento',
        'tempo_resposta_min',
        'tempo_atendimento_min',
    ];

    // As datas já vêm do próprio fluxo do atendimento (abertura,
    // resposta, fechamento), então não usamos os timestamps
    // automáticos do CI4 (created_at/updated_at).
    protected $useTimestamps = false;

    protected $validationRules = [
        'rotina_id'            => 'required|is_natural_no_zero',
        'tipo_falha_id'        => 'required|is_natural_no_zero',
        'modo_atendimento_id'  => 'required|is_natural_no_zero',
        'descricao_problema'   => 'required',
        'descricao_solucao'    => 'permit_empty',
        'data_abertura'        => 'required|valid_date',
        'data_resposta'        => 'permit_empty|valid_date',
        'data_fechamento'      => 'permit_empty|valid_date',
        'tempo_resposta_min'   => 'permit_empty|integer',
        'tempo_atendimento_min'=> 'permit_empty|integer',
    ];

    /**
     * Atendimentos com os nomes já resolvidos via join — é o que
     * alimenta a tela Operacional (listagem/histórico).
     */
    public function listaCompleta()
    {
        return $this->select('
                atendimentos.*,
                rotinas.nome as rotina_nome,
                modulos.nome as modulo_nome,
                tipos_falha.nome as tipo_falha_nome,
                modos_atendimento.nome as modo_atendimento_nome
            ')
            ->join('rotinas', 'rotinas.id = atendimentos.rotina_id')
            ->join('modulos', 'modulos.id = rotinas.modulo_id')
            ->join('tipos_falha', 'tipos_falha.id = atendimentos.tipo_falha_id')
            ->join('modos_atendimento', 'modos_atendimento.id = atendimentos.modo_atendimento_id')
            ->orderBy('atendimentos.data_abertura', 'DESC');
    }

    /**
     * Contagem de atendimentos por tipo de falha — alimenta a tela
     * Analítica (indicadores / falhas recorrentes).
     */
    public function falhasRecorrentes(int $limite = 10)
    {
        return $this->select('tipos_falha.nome as tipo_falha, COUNT(*) as total')
            ->join('tipos_falha', 'tipos_falha.id = atendimentos.tipo_falha_id')
            ->groupBy('tipos_falha.nome')
            ->orderBy('total', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    /**
     * Atendimentos de uma rotina, paginados, com busca por texto
     * (tipo de falha ou descrição do problema) e filtro por status.
     * Alimenta a tela "Atendimentos da rotina".
     *
     * Retorna: itens, total, porPagina, pagina, totalPaginas.
     */
    public function daRotina(int $rotinaId, array $opcoes = []): array
    {
        $porPagina = 6;
        $pagina    = max(1, (int) ($opcoes['pagina'] ?? 1));
        $busca     = trim((string) ($opcoes['busca'] ?? ''));
        $status    = $opcoes['status'] ?? null; // 'finalizado' | 'aberto' | null

        $builder = $this->db->table('atendimentos a')
            ->select("
                a.id,
                a.descricao_problema,
                a.data_abertura,
                a.data_fechamento,
                minutos_uteis(a.data_abertura, a.data_resposta) as tempo_resposta_min,
                minutos_uteis(a.data_resposta, a.data_fechamento) as tempo_atendimento_min,
                tf.nome as tipo_falha_nome
            ", false)
            ->join('tipos_falha tf', 'tf.id = a.tipo_falha_id')
            ->where('a.rotina_id', $rotinaId);

        if ($busca !== '') {
            $builder->groupStart()
                ->like('tf.nome', $busca)
                ->orLike('a.descricao_problema', $busca)
                ->groupEnd();
        }

        if ($status === 'finalizado') {
            $builder->where('a.data_fechamento IS NOT NULL', null, false);
        } elseif ($status === 'aberto') {
            $builder->where('a.data_fechamento IS NULL', null, false);
        }

        // false = não reseta os WHERE/JOIN, para reaproveitar o
        // mesmo builder na consulta seguinte (com LIMIT/OFFSET).
        $total = $builder->countAllResults(false);

        $itens = $builder
            ->orderBy('a.data_abertura', 'DESC')
            ->limit($porPagina, ($pagina - 1) * $porPagina)
            ->get()
            ->getResultArray();

        return [
            'itens'        => $itens,
            'total'        => $total,
            'porPagina'    => $porPagina,
            'pagina'       => $pagina,
            'totalPaginas' => max(1, (int) ceil($total / $porPagina)),
        ];
    }

    /**
     * Um atendimento com todos os nomes já resolvidos (módulo,
     * rotina, tipo de falha, modo de atendimento). Alimenta a tela
     * de detalhe do atendimento.
     */
    public function detalhe(int $id): ?array
    {
        $resultado = $this->db->table('atendimentos a')
            ->select("
                a.id,
                a.descricao_problema,
                a.descricao_solucao,
                a.data_abertura,
                a.data_resposta,
                a.data_fechamento,
                minutos_uteis(a.data_abertura, a.data_resposta) as tempo_resposta_min,
                minutos_uteis(a.data_resposta, a.data_fechamento) as tempo_atendimento_min,
                r.id as rotina_id, r.nome as rotina_nome,
                m.id as modulo_id, m.nome as modulo_nome,
                tf.nome as tipo_falha_nome,
                ma.nome as modo_atendimento_nome
            ", false)
            ->join('rotinas r', 'r.id = a.rotina_id')
            ->join('modulos m', 'm.id = r.modulo_id')
            ->join('tipos_falha tf', 'tf.id = a.tipo_falha_id')
            ->join('modos_atendimento ma', 'ma.id = a.modo_atendimento_id')
            ->where('a.id', $id)
            ->get()
            ->getRowArray();

        return $resultado ?: null;
    }

    /**
     * Indicadores gerais do Dashboard Analítico: total de
     * atendimentos, tempo médio, tempo médio de resposta, backlog e
     * reincidência.
     *
     * Backlog: atendimentos que não fecharam no mesmo dia em que
     * abriram (fecharam depois, ou ainda seguem abertos desde antes
     * de hoje).
     *
     * Reincidência: % de atendimentos cuja combinação rotina + tipo
     * de falha já havia ocorrido antes — ou seja, não é a primeira
     * vez que aquele padrão de problema aparece.
     */
    public function indicadoresGerais(): array
    {
        return $this->indicadores();
    }

    /**
     * Mesma lógica de indicadoresGerais(), mas aceitando filtros
     * opcionais (modulo_id, tipo_falha_id, periodo no formato
     * 'AAAA-MM'). Usado no Comparativo entre Módulos e na Análise de
     * Padrões, além do próprio Dashboard (sem filtro nenhum).
     */
    public function indicadores(array $filtros = []): array
    {
        $geral = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->select("
                COUNT(*) as total,
                COALESCE(AVG(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_medio_min,
                COALESCE(AVG(minutos_uteis(a.data_abertura, a.data_resposta)), 0) as tempo_resposta_min
            ", false)
            ->get()
            ->getRowArray();

        $total = (int) $geral['total'];

        $backlogRow = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->where(
                '(a.data_fechamento IS NOT NULL AND a.data_fechamento::date > a.data_abertura::date)
                 OR (a.data_fechamento IS NULL AND a.data_abertura::date < CURRENT_DATE)',
                null,
                false
            )
            ->select('COUNT(*) as total', false)
            ->get()
            ->getRowArray();

        $padroesRow = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->select('COUNT(DISTINCT (a.rotina_id, a.tipo_falha_id)) as padroes_unicos', false)
            ->get()
            ->getRowArray();

        $padroesUnicos   = (int) $padroesRow['padroes_unicos'];
        $reincidenciaPct = $total > 0
            ? round((($total - $padroesUnicos) / $total) * 100, 1)
            : 0.0;

        return [
            'total'              => $total,
            'tempo_medio_min'    => (float) $geral['tempo_medio_min'],
            'tempo_resposta_min' => (float) $geral['tempo_resposta_min'],
            'backlog'            => (int) $backlogRow['total'],
            'reincidencia_pct'   => $reincidenciaPct,
        ];
    }

    /**
     * Builder base com o join em rotinas (necessário para poder
     * filtrar por módulo). Sempre novo, para não acumular estado
     * entre chamadas.
     */
    private function builderComRotina()
    {
        return $this->db->table('atendimentos a')
            ->join('rotinas r', 'r.id = a.rotina_id', 'inner');
    }

    /**
     * Aplica os filtros opcionais de módulo, tipo de falha e período
     * (formato 'AAAA-MM') a um builder já com o alias `a` para
     * atendimentos e `r` para rotinas.
     */
    private function aplicarFiltros($builder, array $filtros)
    {
        if (! empty($filtros['modulo_id'])) {
            $builder->where('r.modulo_id', $filtros['modulo_id']);
        }
        if (! empty($filtros['tipo_falha_id'])) {
            $builder->where('a.tipo_falha_id', $filtros['tipo_falha_id']);
        }
        if (! empty($filtros['periodo'])) {
            $builder->where("to_char(a.data_abertura, 'YYYY-MM')", $filtros['periodo']);
        }

        return $builder;
    }

    /**
     * Lista de períodos (AAAA-MM) com pelo menos um atendimento,
     * mais recentes primeiro. Alimenta os seletores de período.
     */
    public function periodosDisponiveis(): array
    {
        $linhas = $this->db->table('atendimentos')
            ->select("DISTINCT to_char(data_abertura, 'YYYY-MM') as periodo", false)
            ->orderBy('periodo', 'DESC')
            ->get()
            ->getResultArray();

        return array_column($linhas, 'periodo');
    }

    /**
     * Para cada tipo de falha (dentro dos filtros informados),
     * calcula o total de atendimentos e qual rotina concentra mais
     * casos daquele tipo. Alimenta a tela "Análise de Padrões".
     */
    public function concentracaoPorTipoFalha(array $filtros = []): array
    {
        $linhas = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->join('tipos_falha tf', 'tf.id = a.tipo_falha_id')
            ->join('modulos m', 'm.id = r.modulo_id')
            ->select("
                tf.id as tipo_falha_id,
                tf.nome as tipo_falha_nome,
                r.nome as rotina_nome,
                m.nome as modulo_nome,
                COUNT(*) as total
            ", false)
            ->groupBy('tf.id, tf.nome, r.nome, m.nome')
            ->orderBy('tf.nome', 'ASC')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        // Agrupa em PHP: como as linhas já vêm ordenadas por tipo de
        // falha e, dentro dele, da rotina com mais casos para a com
        // menos, a primeira linha de cada tipo de falha já é o "maior
        // concentrador" — só somamos o total geral daquele tipo.
        $agrupado = [];
        foreach ($linhas as $linha) {
            $id = $linha['tipo_falha_id'];

            if (! isset($agrupado[$id])) {
                $agrupado[$id] = [
                    'tipo_falha_nome' => $linha['tipo_falha_nome'],
                    'total_geral'     => 0,
                    'top_rotina_nome' => $linha['rotina_nome'],
                    'top_modulo_nome' => $linha['modulo_nome'],
                    'top_total'       => (int) $linha['total'],
                ];
            }

            $agrupado[$id]['total_geral'] += (int) $linha['total'];
        }

        $resultado = array_values($agrupado);
        usort($resultado, static fn ($a, $b) => $b['total_geral'] <=> $a['total_geral']);

        foreach ($resultado as &$item) {
            $item['top_pct'] = $item['total_geral'] > 0
                ? round(($item['top_total'] / $item['total_geral']) * 100, 1)
                : 0.0;
        }
        unset($item);

        return $resultado;
    }

    /**
     * Estimativa de tempo para uma combinação rotina + tipo de
     * falha, baseada na MEDIANA histórica (não na média) dos
     * atendimentos com o mesmo padrão. A mediana foi escolhida no
     * lugar da média porque um único atendimento atípico (muito
     * acima do normal) puxaria a média para cima e distorceria a
     * estimativa; a mediana não sofre esse efeito.
     */
    public function estimativaTempo(int $rotinaId, int $tipoFalhaId): array
    {
        $linha = $this->db->table('atendimentos')
            ->select("
                COUNT(*) as total,
                COALESCE(PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY minutos_uteis(data_resposta, data_fechamento)), 0) as mediana_min,
                MIN(minutos_uteis(data_resposta, data_fechamento)) as minimo_min,
                MAX(minutos_uteis(data_resposta, data_fechamento)) as maximo_min
            ", false)
            ->where('rotina_id', $rotinaId)
            ->where('tipo_falha_id', $tipoFalhaId)
            ->get()
            ->getRowArray();

        return [
            'total'      => (int) $linha['total'],
            'mediana_min'=> (float) $linha['mediana_min'],
            'minimo_min' => $linha['minimo_min'] !== null ? (int) $linha['minimo_min'] : null,
            'maximo_min' => $linha['maximo_min'] !== null ? (int) $linha['maximo_min'] : null,
        ];
    }

    /**
     * Lista, em ordem cronológica, o tempo real (em minutos de
     * expediente) de cada atendimento já finalizado com o mesmo
     * padrão (rotina + tipo de falha). Alimenta o gráfico "Tempo real
     * x Tempo Estimado" na tela de Estimativa de Tempo.
     */
    public function temposHistoricos(int $rotinaId, int $tipoFalhaId): array
    {
        $linhas = $this->db->table('atendimentos')
            ->select("
                id,
                data_abertura,
                minutos_uteis(data_resposta, data_fechamento) as tempo_min
            ", false)
            ->where('rotina_id', $rotinaId)
            ->where('tipo_falha_id', $tipoFalhaId)
            ->where('data_fechamento IS NOT NULL', null, false)
            ->orderBy('data_abertura', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($linhas as &$linha) {
            $linha['tempo_min'] = (float) $linha['tempo_min'];
        }
        unset($linha);

        return $linhas;
    }

    /**
     * Quantidade de atendimentos por modo de atendimento (Chat,
     * Ligação, Registro Web...). Alimenta a faixa "Modo de
     * Atendimento" do rodapé do Dashboard Analítico.
     */
    public function porModoAtendimento(): array
    {
        return $this->db->table('atendimentos a')
            ->select('ma.nome as modo, COUNT(*) as total', false)
            ->join('modos_atendimento ma', 'ma.id = a.modo_atendimento_id')
            ->groupBy('ma.nome')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Indicadores agrupados por semana corrida (segunda a domingo),
     * dentro dos filtros informados. As semanas são numeradas em
     * ordem de ocorrência (Sem. 1, Sem. 2...), não pelo número ISO
     * da semana no calendário. Alimenta "Evolução dos Indicadores".
     */
    public function evolucaoPorSemana(array $filtros = []): array
    {
        $linhas = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->select("
                to_char(a.data_abertura, 'IYYY-IW') as semana_chave,
                MIN(a.data_abertura)::date as inicio,
                COUNT(*) as total,
                COALESCE(AVG(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_medio_min,
                COALESCE(AVG(minutos_uteis(a.data_abertura, a.data_resposta)), 0) as tempo_resposta_min
            ", false)
            ->groupBy("to_char(a.data_abertura, 'IYYY-IW')")
            ->orderBy('semana_chave', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($linhas as $i => &$linha) {
            $linha['label']              = 'Sem. ' . ($i + 1);
            $linha['total']              = (int) $linha['total'];
            $linha['tempo_medio_min']    = (float) $linha['tempo_medio_min'];
            $linha['tempo_resposta_min'] = (float) $linha['tempo_resposta_min'];
        }
        unset($linha);

        return $linhas;
    }

    /**
     * Razão de correlação (eta²) entre uma coluna categórica (ex.:
     * a.tipo_falha_id) e o tempo de atendimento. Diferente da
     * correlação de Pearson (para duas variáveis numéricas), eta² é
     * o método adequado para medir o quanto uma categoria explica a
     * variação de um número — vai de 0 (nenhuma relação) a 1
     * (a categoria explica toda a variação).
     *
     * Só considera atendimentos já finalizados (com data_fechamento).
     */
    public function etaQuadrado(string $colunaGrupo, array $filtros = []): float
    {
        $y = 'minutos_uteis(a.data_resposta, a.data_fechamento)';

        $overall = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->where('a.data_fechamento IS NOT NULL', null, false)
            ->select("COUNT(*) as n, SUM($y) as soma, SUM(($y) * ($y)) as soma2", false)
            ->get()
            ->getRowArray();

        $n = (int) $overall['n'];
        if ($n < 2) {
            return 0.0;
        }

        $soma  = (float) $overall['soma'];
        $soma2 = (float) $overall['soma2'];
        $ssTotal = $soma2 - ($soma * $soma) / $n;
        if ($ssTotal <= 0) {
            return 0.0;
        }

        $grupos = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->where('a.data_fechamento IS NOT NULL', null, false)
            ->select("$colunaGrupo as grupo, COUNT(*) as n_g, SUM($y) as soma_g", false)
            ->groupBy($colunaGrupo)
            ->get()
            ->getResultArray();

        $ssBetween = 0.0;
        foreach ($grupos as $g) {
            $ng = (int) $g['n_g'];
            if ($ng < 1) {
                continue;
            }
            $somaG = (float) $g['soma_g'];
            $ssBetween += ($somaG * $somaG) / $ng;
        }
        $ssBetween -= ($soma * $soma) / $n;

        $eta2 = $ssBetween / $ssTotal;

        return max(0.0, min(1.0, round($eta2, 2)));
    }

    /**
     * Qual tipo de falha tem o maior tempo médio de atendimento
     * (só atendimentos finalizados). Alimenta a narrativa de
     * "Tipo de falha x Tempo de Atendimento" na Análise de Relações.
     */
    public function maiorTempoPorTipoFalha(array $filtros = []): ?array
    {
        $linha = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->join('tipos_falha tf', 'tf.id = a.tipo_falha_id')
            ->where('a.data_fechamento IS NOT NULL', null, false)
            ->select('tf.nome as nome, AVG(minutos_uteis(a.data_resposta, a.data_fechamento)) as tempo_medio_min', false)
            ->groupBy('tf.nome')
            ->orderBy('tempo_medio_min', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $linha ?: null;
    }

    /**
     * Qual módulo tem o maior tempo médio de atendimento.
     */
    public function maiorTempoPorModulo(array $filtros = []): ?array
    {
        $linha = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->join('modulos m', 'm.id = r.modulo_id')
            ->where('a.data_fechamento IS NOT NULL', null, false)
            ->select('m.nome as nome, AVG(minutos_uteis(a.data_resposta, a.data_fechamento)) as tempo_medio_min', false)
            ->groupBy('m.nome')
            ->orderBy('tempo_medio_min', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $linha ?: null;
    }

    /**
     * Qual modo de atendimento tem o maior tempo médio de atendimento.
     */
    public function maiorTempoPorModoAtendimento(array $filtros = []): ?array
    {
        $linha = $this->aplicarFiltros($this->builderComRotina(), $filtros)
            ->join('modos_atendimento ma', 'ma.id = a.modo_atendimento_id')
            ->where('a.data_fechamento IS NOT NULL', null, false)
            ->select('ma.nome as nome, AVG(minutos_uteis(a.data_resposta, a.data_fechamento)) as tempo_medio_min', false)
            ->groupBy('ma.nome')
            ->orderBy('tempo_medio_min', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $linha ?: null;
    }
}
