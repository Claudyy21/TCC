<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\ModuloModel;
use App\Models\RotinaModel;
use App\Models\TipoFalhaModel;

class Analitica extends BaseController
{
    public function index(): string
    {
        $atendimentoModel = new AtendimentoModel();
        $moduloModel      = new ModuloModel();

        $indicadores = $atendimentoModel->indicadoresGerais();

        $porModulo = $moduloModel->comEstatisticas();
        usort($porModulo, static fn ($a, $b) => $b['total_atendimentos'] <=> $a['total_atendimentos']);

        $porFalha = $atendimentoModel->falhasRecorrentes(6);
        $porModo  = $atendimentoModel->porModoAtendimento();

        return view('analitica/index', [
            'titulo'               => 'Dashboard Analítico',
            'indicadores'          => $indicadores,
            'porModulo'            => $porModulo,
            'porFalha'             => $porFalha,
            'porModo'              => $porModo,
            'totalModoAtendimento' => array_sum(array_column($porModo, 'total')),
        ]);
    }

    /**
     * Comparação lado a lado dos indicadores de até 3 módulos,
     * opcionalmente filtrados por período.
     */
    public function comparativo(): string
    {
        $atendimentoModel = new AtendimentoModel();
        $moduloModel      = new ModuloModel();

        $todosModulos = $moduloModel->where('status', true)->orderBy('nome', 'ASC')->findAll();

        $selecionados = (array) ($this->request->getGet('modulos') ?? []);
        $selecionados = array_slice(array_unique(array_filter(array_map('intval', $selecionados))), 0, 3);

        if (empty($selecionados)) {
            $porVolume = $moduloModel->comEstatisticas();
            usort($porVolume, static fn ($a, $b) => $b['total_atendimentos'] <=> $a['total_atendimentos']);
            $selecionados = array_slice(array_column($porVolume, 'id'), 0, 2);
        }

        $periodo = $this->request->getGet('periodo') ?: null;

        $colunas = [];
        foreach ($selecionados as $id) {
            $modulo = $moduloModel->find($id);
            if (! $modulo) {
                continue;
            }
            $colunas[] = [
                'modulo'      => $modulo,
                'indicadores' => $atendimentoModel->indicadores(['modulo_id' => $id, 'periodo' => $periodo]),
            ];
        }

        $destaques = ['volume' => null, 'tempo_medio' => null, 'tempo_resposta' => null];
        if (! empty($colunas)) {
            $porCampo = static function (array $colunas, string $campo) {
                usort($colunas, static fn ($a, $b) => $b['indicadores'][$campo] <=> $a['indicadores'][$campo]);
                return $colunas[0]['modulo']['nome'];
            };
            $destaques = [
                'volume'         => $porCampo($colunas, 'total'),
                'tempo_medio'    => $porCampo($colunas, 'tempo_medio_min'),
                'tempo_resposta' => $porCampo($colunas, 'tempo_resposta_min'),
            ];
        }

        return view('analitica/comparativo', [
            'titulo'             => 'Comparativo entre Módulos',
            'todosModulos'       => $todosModulos,
            'selecionados'       => $selecionados,
            'periodos'           => $atendimentoModel->periodosDisponiveis(),
            'periodoSelecionado' => $periodo,
            'colunas'            => $colunas,
            'destaques'          => $destaques,
        ]);
    }

    /**
     * Concentração de cada tipo de falha por rotina, com filtros
     * opcionais de módulo, período e tipo de falha.
     */
    public function padroes(): string
    {
        $atendimentoModel = new AtendimentoModel();
        $moduloModel      = new ModuloModel();
        $tipoFalhaModel   = new TipoFalhaModel();

        $moduloId    = (int) ($this->request->getGet('modulo_id') ?? 0) ?: null;
        $tipoFalhaId = (int) ($this->request->getGet('tipo_falha_id') ?? 0) ?: null;
        $periodo     = $this->request->getGet('periodo') ?: null;

        $filtros = ['modulo_id' => $moduloId, 'tipo_falha_id' => $tipoFalhaId, 'periodo' => $periodo];

        $padroes          = $atendimentoModel->concentracaoPorTipoFalha($filtros);
        $totalGeralFiltro = array_sum(array_column($padroes, 'total_geral'));

        return view('analitica/padroes', [
            'titulo'           => 'Análise de Padrões',
            'modulos'          => $moduloModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'tiposFalha'       => $tipoFalhaModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'periodos'         => $atendimentoModel->periodosDisponiveis(),
            'moduloId'         => $moduloId,
            'tipoFalhaId'      => $tipoFalhaId,
            'periodo'          => $periodo,
            'padroes'          => $padroes,
            'totalGeralFiltro' => $totalGeralFiltro,
        ]);
    }

    /**
     * Estimativa de tempo com base na média histórica de
     * atendimentos com o mesmo padrão (rotina + tipo de falha).
     */
    public function estimativa(): string
    {
        $atendimentoModel = new AtendimentoModel();
        $moduloModel      = new ModuloModel();
        $rotinaModel      = new RotinaModel();
        $tipoFalhaModel   = new TipoFalhaModel();

        $moduloId    = (int) ($this->request->getGet('modulo_id') ?? 0) ?: null;
        $rotinaId    = (int) ($this->request->getGet('rotina_id') ?? 0) ?: null;
        $tipoFalhaId = (int) ($this->request->getGet('tipo_falha_id') ?? 0) ?: null;

        $resultado = null;
        $historico = [];
        if ($rotinaId && $tipoFalhaId) {
            $resultado = $atendimentoModel->estimativaTempo($rotinaId, $tipoFalhaId);
            $historico = $atendimentoModel->temposHistoricos($rotinaId, $tipoFalhaId);
        }

        return view('analitica/estimativa', [
            'titulo'      => 'Estimativa de Tempo',
            'modulos'     => $moduloModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'rotinas'     => $rotinaModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'tiposFalha'  => $tipoFalhaModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'moduloId'    => $moduloId,
            'rotinaId'    => $rotinaId,
            'tipoFalhaId' => $tipoFalhaId,
            'resultado'   => $resultado,
            'historico'   => $historico,
        ]);
    }

    /**
     * Indicadores agrupados por semana corrida, dentro de um módulo e
     * período opcionais.
     */
    public function evolucao(): string
    {
        $atendimentoModel = new AtendimentoModel();
        $moduloModel      = new ModuloModel();

        $periodos = $atendimentoModel->periodosDisponiveis();

        $moduloId = (int) ($this->request->getGet('modulo_id') ?? 0) ?: null;
        $periodo  = $this->request->getGet('periodo') ?: null;

        // Sem período escolhido, usa o mais recente disponível como
        // ponto de partida (evita somar a história inteira em "semanas").
        if ($periodo === null && ! empty($periodos)) {
            $periodo = $periodos[0];
        }

        $semanas = $atendimentoModel->evolucaoPorSemana(['modulo_id' => $moduloId, 'periodo' => $periodo]);

        $destaques = ['volume' => null, 'tempo_medio' => null, 'tempo_resposta' => null];
        if (! empty($semanas)) {
            $porVolume = $semanas;
            usort($porVolume, static fn ($a, $b) => $b['total'] <=> $a['total']);
            $porTempoMedio = $semanas;
            usort($porTempoMedio, static fn ($a, $b) => $a['tempo_medio_min'] <=> $b['tempo_medio_min']);
            $porTempoResposta = $semanas;
            usort($porTempoResposta, static fn ($a, $b) => $a['tempo_resposta_min'] <=> $b['tempo_resposta_min']);

            $destaques = [
                'volume'         => $porVolume[0],
                'tempo_medio'    => $porTempoMedio[0],
                'tempo_resposta' => $porTempoResposta[0],
            ];
        }

        return view('analitica/evolucao', [
            'titulo'         => 'Evolução dos Indicadores',
            'modulos'        => $moduloModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'periodos'       => $periodos,
            'moduloId'       => $moduloId,
            'periodoSelecionado' => $periodo,
            'semanas'        => $semanas,
            'destaques'      => $destaques,
        ]);
    }

    /**
     * Correlação (eta²) entre cada característica categórica e o
     * tempo de atendimento, mais um destaque de qual categoria tem o
     * maior tempo médio em cada caso.
     */
    public function relacoes(): string
    {
        $atendimentoModel = new AtendimentoModel();
        $moduloModel      = new ModuloModel();

        $moduloId = (int) ($this->request->getGet('modulo_id') ?? 0) ?: null;
        $periodo  = $this->request->getGet('periodo') ?: null;
        $filtros  = ['modulo_id' => $moduloId, 'periodo' => $periodo];

        // Correlação de "módulo" só faz sentido quando NÃO se está
        // filtrando por um módulo específico (senão a variável fica
        // constante e a comparação perde o sentido).
        $mostrarModulo = empty($moduloId);

        $relacoes = [
            [
                'chave'      => 'tipo_falha',
                'nome'       => 'Tipo de Falha',
                'eta2'       => $atendimentoModel->etaQuadrado('a.tipo_falha_id', $filtros),
                'destaque'   => $atendimentoModel->maiorTempoPorTipoFalha($filtros),
            ],
        ];

        if ($mostrarModulo) {
            $relacoes[] = [
                'chave'    => 'modulo',
                'nome'     => 'Módulo',
                'eta2'     => $atendimentoModel->etaQuadrado('r.modulo_id', $filtros),
                'destaque' => $atendimentoModel->maiorTempoPorModulo($filtros),
            ];
        }

        $relacoes[] = [
            'chave'    => 'modo_atendimento',
            'nome'     => 'Modo Atendimento',
            'eta2'     => $atendimentoModel->etaQuadrado('a.modo_atendimento_id', $filtros),
            'destaque' => $atendimentoModel->maiorTempoPorModoAtendimento($filtros),
        ];

        usort($relacoes, static fn ($a, $b) => $b['eta2'] <=> $a['eta2']);

        return view('analitica/relacoes', [
            'titulo'    => 'Análise de Relações',
            'modulos'   => $moduloModel->where('status', true)->orderBy('nome', 'ASC')->findAll(),
            'periodos'  => $atendimentoModel->periodosDisponiveis(),
            'moduloId'  => $moduloId,
            'periodo'   => $periodo,
            'relacoes'  => $relacoes,
        ]);
    }
}
