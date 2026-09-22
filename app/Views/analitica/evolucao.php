<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('analitica')]) ?>
    <?= view('partials/analitica_tabs', ['ativo' => 'evolucao']) ?>

    <h1 class="nexus-dashboard-title">Evolução dos Indicadores</h1>
    <p class="nexus-dashboard-subtitle">Comportamentos ao longo dos períodos</p>

    <form class="nexus-filtros" method="get">
        <label class="nexus-filtros__campo">
            <span>Selecionar módulo</span>
            <select name="modulo_id" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach ($modulos as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $moduloId === (int) $m['id'] ? 'selected' : '' ?>><?= esc($m['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="nexus-filtros__campo">
            <span>Selecionar período</span>
            <select name="periodo" onchange="this.form.submit()">
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= esc($p) ?>" <?= $periodoSelecionado === $p ? 'selected' : '' ?>><?= esc(\App\Libraries\Formatador::periodo($p)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <?php if (empty($semanas)): ?>

        <p class="nexus-vazio">Nenhum atendimento encontrado nesse período/módulo.</p>

    <?php else: ?>

        <?php
            $maxTotal = max(array_column($semanas, 'total'));
            $maxTempoMedio = max(array_column($semanas, 'tempo_medio_min')) ?: 1;
            $maxTempoResposta = max(array_column($semanas, 'tempo_resposta_min')) ?: 1;

            $itensVolume = array_map(static fn ($s) => [
                'label' => $s['label'],
                'altura_pct' => $maxTotal > 0 ? round($s['total'] / $maxTotal * 100) : 0,
                'valor_exibido' => $s['total'] . ' atendimentos',
            ], $semanas);

            $itensTempoMedio = array_map(static fn ($s) => [
                'label' => $s['label'],
                'altura_pct' => round($s['tempo_medio_min'] / $maxTempoMedio * 100),
                'valor_exibido' => \App\Libraries\GraficoPizza::tempoFormatado($s['tempo_medio_min']),
            ], $semanas);

            $itensTempoResposta = array_map(static fn ($s) => [
                'label' => $s['label'],
                'altura_pct' => round($s['tempo_resposta_min'] / $maxTempoResposta * 100),
                'valor_exibido' => \App\Libraries\GraficoPizza::tempoFormatado($s['tempo_resposta_min']),
            ], $semanas);
        ?>

        <?= view('analitica/_grafico_barras_simples', [
            'titulo' => 'Evolução dos Volumes',
            'itens' => $itensVolume,
            'eixoMax' => (string) $maxTotal,
            'eixoMeio' => (string) round($maxTotal / 2),
        ]) ?>

        <?= view('analitica/_grafico_barras_simples', [
            'titulo' => 'Evolução dos Tempos Médios',
            'itens' => $itensTempoMedio,
            'eixoMax' => \App\Libraries\GraficoPizza::tempoFormatado($maxTempoMedio),
            'eixoMeio' => \App\Libraries\GraficoPizza::tempoFormatado($maxTempoMedio / 2),
        ]) ?>

        <?= view('analitica/_grafico_barras_simples', [
            'titulo' => 'Evolução dos Tempos de Resposta',
            'itens' => $itensTempoResposta,
            'eixoMax' => \App\Libraries\GraficoPizza::tempoFormatado($maxTempoResposta),
            'eixoMeio' => \App\Libraries\GraficoPizza::tempoFormatado($maxTempoResposta / 2),
        ]) ?>

        <h2 class="nexus-section-title">Destaques</h2>
        <div class="nexus-destaques">
            <div class="nexus-destaques__linha">
                <span>Maior Volume:</span>
                <strong><?= esc($destaques['volume']['label']) ?> &mdash; <?= $destaques['volume']['total'] ?> Atendimentos</strong>
            </div>
            <div class="nexus-destaques__linha">
                <span>Menor Tempo Médio:</span>
                <strong><?= esc($destaques['tempo_medio']['label']) ?> &mdash; <?= \App\Libraries\GraficoPizza::tempoFormatado($destaques['tempo_medio']['tempo_medio_min']) ?></strong>
            </div>
            <div class="nexus-destaques__linha">
                <span>Menor Tempo de Resposta:</span>
                <strong><?= esc($destaques['tempo_resposta']['label']) ?> &mdash; <?= \App\Libraries\GraficoPizza::tempoFormatado($destaques['tempo_resposta']['tempo_resposta_min']) ?></strong>
            </div>
        </div>

    <?php endif; ?>

<?= $this->endSection() ?>
