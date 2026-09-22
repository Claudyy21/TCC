<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('operacional/modulo/' . $modulo['id'])]) ?>

    <?= view('partials/breadcrumb', [
        'itens' => [
            ['label' => 'Dashboard', 'url' => base_url('operacional')],
            ['label' => $modulo['nome'], 'url' => base_url('operacional/modulo/' . $modulo['id'])],
            ['label' => 'Rotinas', 'url' => null],
        ],
    ]) ?>

    <h1 class="nexus-page-title nexus-page-title--md">MÓDULO <?= esc(mb_strtoupper($modulo['nome'])) ?></h1>
    <p class="nexus-page-subtitle">Visão Geral das rotinas</p>

    <div class="nexus-grid-cards">
        <?php foreach ($rotinas as $rotina): ?>
            <div class="nexus-grid-card">
                <h3 class="nexus-grid-card__titulo"><?= esc($rotina['nome']) ?></h3>
                <p class="nexus-grid-card__linha"><?= $rotina['total_atendimentos'] ?> atendimentos</p>
                <p class="nexus-grid-card__linha">
                    Tempo total:<br>
                    <?= \App\Libraries\GraficoPizza::tempoFormatado($rotina['tempo_total_min']) ?>
                </p>
                <p class="nexus-grid-card__linha">
                    Tempo médio:<br>
                    <?= \App\Libraries\GraficoPizza::tempoFormatado($rotina['tempo_medio_min']) ?>
                </p>
                <a class="nexus-btn nexus-btn--pequeno" href="<?= base_url('operacional/rotina/' . $rotina['id']) ?>">
                    <span>Acessar</span>
                    <svg width="22" height="10" viewBox="0 0 26 12" fill="none" aria-hidden="true">
                        <path d="M1 6h22M18 1l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($rotinas)): ?>
            <p class="nexus-vazio">Nenhuma rotina encontrada para este módulo.</p>
        <?php endif; ?>
    </div>

    <div class="nexus-footer-stats">
        <span><?= $modulo['total_atendimentos'] ?> Atendimentos</span>
        <span><?= $modulo['total_rotinas'] ?> Rotinas</span>
        <span>Tempo total: <?= \App\Libraries\GraficoPizza::tempoFormatado($modulo['tempo_total_min']) ?></span>
    </div>

<?= $this->endSection() ?>
