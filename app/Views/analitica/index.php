<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url()]) ?>
    <?= view('partials/analitica_tabs', ['ativo' => 'dashboard']) ?>

    <h1 class="nexus-dashboard-title">Dashboard Analítico</h1>
    <p class="nexus-dashboard-subtitle">Visão geral dos indicadores</p>

    <div class="nexus-kpi-row">
        <div class="nexus-kpi-card">
            <span class="nexus-kpi-card__label">Atendimentos</span>
            <span class="nexus-kpi-card__valor"><?= $indicadores['total'] ?></span>
        </div>
        <div class="nexus-kpi-card">
            <span class="nexus-kpi-card__label">Tempo médio</span>
            <span class="nexus-kpi-card__valor"><?= \App\Libraries\GraficoPizza::tempoFormatado($indicadores['tempo_medio_min']) ?></span>
        </div>
        <div class="nexus-kpi-card">
            <span class="nexus-kpi-card__label">Tempo de resposta</span>
            <span class="nexus-kpi-card__valor"><?= round($indicadores['tempo_resposta_min']) ?> minutos</span>
        </div>
    </div>

    <div class="nexus-kpi-row">
        <div class="nexus-kpi-card">
            <span class="nexus-kpi-card__label">Backlog</span>
            <span class="nexus-kpi-card__valor"><?= $indicadores['backlog'] ?></span>
        </div>
        <div class="nexus-kpi-card">
            <span class="nexus-kpi-card__label">Reincidência</span>
            <span class="nexus-kpi-card__valor"><?= number_format($indicadores['reincidencia_pct'], 1, ',', '.') ?>%</span>
        </div>
    </div>

    <div class="nexus-panels-grid">

        <div class="nexus-panel">
            <h2 class="nexus-panel__titulo">Atendimentos por módulo</h2>
            <?php $maiorModulo = max(array_column($porModulo, 'total_atendimentos') ?: [1]); ?>
            <div class="nexus-barras">
                <?php foreach ($porModulo as $m): ?>
                    <?php $segmentos = $maiorModulo > 0 ? (int) round(($m['total_atendimentos'] / $maiorModulo) * 10) : 0; ?>
                    <div class="nexus-barra-linha">
                        <span class="nexus-barra-linha__label"><?= esc($m['nome']) ?></span>
                        <span class="nexus-barra-segmentos" aria-hidden="true">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <span class="<?= $i <= $segmentos ? 'preenchido' : '' ?>"></span>
                            <?php endfor; ?>
                        </span>
                        <span class="nexus-barra-linha__valor"><?= $m['total_atendimentos'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nexus-panel">
            <h2 class="nexus-panel__titulo">Tipos de falhas mais recorrentes</h2>
            <?php $maiorFalha = max(array_column($porFalha, 'total') ?: [1]); ?>
            <div class="nexus-barras">
                <?php foreach ($porFalha as $f): ?>
                    <?php $segmentos = $maiorFalha > 0 ? (int) round(($f['total'] / $maiorFalha) * 10) : 0; ?>
                    <div class="nexus-barra-linha">
                        <span class="nexus-barra-linha__label"><?= esc($f['tipo_falha']) ?></span>
                        <span class="nexus-barra-segmentos" aria-hidden="true">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <span class="<?= $i <= $segmentos ? 'preenchido' : '' ?>"></span>
                            <?php endfor; ?>
                        </span>
                        <span class="nexus-barra-linha__valor"><?= $f['total'] ?> atend.</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="nexus-footer-stats nexus-footer-stats--modo">
        <span class="nexus-footer-stats__titulo">Modo de atendimento:</span>
        <?php foreach ($porModo as $i => $modo): ?>
            <span>
                <?= esc($modo['modo']) ?>:
                <?= $totalModoAtendimento > 0 ? round(($modo['total'] / $totalModoAtendimento) * 100, 1) : 0 ?>%
            </span>
            <?php if ($i < count($porModo) - 1): ?><span class="nexus-footer-stats__sep">|</span><?php endif; ?>
        <?php endforeach; ?>
    </div>

<?= $this->endSection() ?>
