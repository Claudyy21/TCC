<?php

namespace App\Controllers;

use App\Libraries\GraficoPizza;
use App\Models\AtendimentoModel;
use App\Models\ModuloModel;
use App\Models\RotinaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Operacional extends BaseController
{
    public function index(): string
    {
        $moduloModel = new ModuloModel();
        $modulos     = $moduloModel->comEstatisticas();

        $totalAtendimentos = array_sum(array_column($modulos, 'total_atendimentos'));
        $totalModulos      = count($modulos);
        $ultimaSync        = date('d/m/Y H:i');

        // Guarda o total atual na sessão — é a referência usada pelo
        // botão "Atualizar dados" para calcular quantos registros
        // novos surgiram desde a última verificação.
        session()->set('nexus_total_atendimentos', $totalAtendimentos);
        session()->set('nexus_ultima_sync', $ultimaSync);

        return view('operacional/index', [
            'titulo'            => 'Dashboard Operacional',
            'fatias'            => GraficoPizza::fatias($modulos),
            'modulosJson'       => json_encode($modulos),
            'totalAtendimentos' => $totalAtendimentos,
            'totalModulos'      => $totalModulos,
            'ultimaSync'        => $ultimaSync,
        ]);
    }

    /**
     * Endpoint chamado via AJAX pelo botão "Atualizar dados".
     * Recalcula as estatísticas, compara com o total salvo na sessão
     * e devolve em JSON quantos registros novos apareceram.
     */
    public function verificarAtualizacoes()
    {
        $moduloModel = new ModuloModel();
        $modulos     = $moduloModel->comEstatisticas();

        $totalAtual    = array_sum(array_column($modulos, 'total_atendimentos'));
        $totalAnterior = session('nexus_total_atendimentos') ?? $totalAtual;
        $novos         = max(0, $totalAtual - $totalAnterior);
        $ultimaSync    = date('d/m/Y H:i');

        session()->set('nexus_total_atendimentos', $totalAtual);
        session()->set('nexus_ultima_sync', $ultimaSync);

        return $this->response->setJSON([
            'novos'             => $novos,
            'totalAtendimentos' => $totalAtual,
            'totalModulos'      => count($modulos),
            'ultimaSync'        => $ultimaSync,
            'modulos'           => $modulos,
        ]);
    }

    /**
     * Visão geral de um módulo: estatísticas + as rotinas mais
     * atendidas, 3 por página (paginação simples via ?pagina=).
     */
    public function modulo(int $id): string
    {
        $moduloModel = new ModuloModel();
        $rotinaModel = new RotinaModel();

        $modulo = $moduloModel->estatisticasPorId($id);
        if (! $modulo) {
            throw PageNotFoundException::forPageNotFound();
        }

        $todasRotinas = $rotinaModel->comEstatisticas($id); // já vem ordenado por mais atendidas

        $porPagina    = 3;
        $totalPaginas = max(1, (int) ceil(count($todasRotinas) / $porPagina));
        $pagina       = max(1, min($totalPaginas, (int) ($this->request->getGet('pagina') ?? 1)));

        $rotinasPagina = array_slice($todasRotinas, ($pagina - 1) * $porPagina, $porPagina);

        return view('operacional/modulo', [
            'titulo'       => 'Módulo ' . $modulo['nome'],
            'modulo'       => $modulo,
            'rotinas'      => $rotinasPagina,
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
        ]);
    }

    /**
     * Todas as rotinas de um módulo, em grid, sem paginação.
     */
    public function moduloRotinas(int $id): string
    {
        $moduloModel = new ModuloModel();
        $rotinaModel = new RotinaModel();

        $modulo = $moduloModel->estatisticasPorId($id);
        if (! $modulo) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('operacional/modulo_rotinas', [
            'titulo'  => 'Rotinas do Módulo ' . $modulo['nome'],
            'modulo'  => $modulo,
            'rotinas' => $rotinaModel->comEstatisticas($id),
        ]);
    }

    /**
     * Atendimentos de uma rotina: paginado, com busca e filtro por status.
     */
    public function rotina(int $id): string
    {
        $rotinaModel      = new RotinaModel();
        $atendimentoModel = new AtendimentoModel();

        $rotina = $rotinaModel->estatisticasPorId($id);
        if (! $rotina) {
            throw PageNotFoundException::forPageNotFound();
        }

        $busca     = (string) ($this->request->getGet('busca') ?? '');
        $status    = $this->request->getGet('status') ?: null;
        $resultado = $atendimentoModel->daRotina($id, [
            'pagina' => $this->request->getGet('pagina'),
            'busca'  => $busca,
            'status' => $status,
        ]);

        return view('operacional/rotina', array_merge($resultado, [
            'titulo' => $rotina['nome'],
            'rotina' => $rotina,
            'busca'  => $busca,
            'status' => $status,
        ]));
    }

    /**
     * Detalhe completo de um atendimento.
     */
    public function atendimento(int $id): string
    {
        $atendimentoModel = new AtendimentoModel();
        $atendimento      = $atendimentoModel->detalhe($id);

        if (! $atendimento) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('operacional/atendimento', [
            'titulo' => 'Atendimento #' . str_pad((string) $id, 5, '0', STR_PAD_LEFT),
            'a'      => $atendimento,
        ]);
    }
}
