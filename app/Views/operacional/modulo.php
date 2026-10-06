<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('operacional')]) ?>

    <?= view('partials/breadcrumb', [
        'itens' => [
            ['label' => 'Dashboard', 'url' => base_url('operacional')],
            ['label' => $modulo['nome'], 'url' => null],
        ],
    ]) ?>

    <h1 class="nexus-page-title nexus-page-title--md">MÓDULO <?= esc(mb_strtoupper($modulo['nome'])) ?></h1>
    <p class="nexus-page-subtitle">Visão Geral dos Atendimentos</p>

    <div class="nexus-stats-row">
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= $modulo['total_atendimentos'] ?></span>
            <span class="nexus-stat__label">Atendimentos</span>
        </div>
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= $modulo['total_rotinas'] ?></span>
            <span class="nexus-stat__label">Rotinas</span>
        </div>
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= \App\Libraries\GraficoPizza::tempoFormatado($modulo['tempo_medio_min']) ?></span>
            <span class="nexus-stat__label">Tempo médio</span>
        </div>
    </div>

    <h2 class="nexus-section-title">Rotinas mais atendidas</h2>

    <div class="nexus-list">
        <?php foreach ($rotinas as $rotina): ?>
            <div class="nexus-list-item">
                <div>
                    <p class="nexus-list-item__titulo"><?= esc($rotina['nome']) ?></p>
                    <p class="nexus-list-item__meta"><?= $rotina['total_atendimentos'] ?> Atendimentos</p>
                </div>
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

    <?php if ($totalPaginas > 1): ?>
        <nav class="nexus-pagination" aria-label="Paginação de rotinas">
            <span class="nexus-pagination__label">Página <?= $pagina ?> de <?= $totalPaginas ?></span>
            <div class="nexus-pagination__controles">
                <a class="nexus-pagination__seta" href="?pagina=<?= max(1, $pagina - 1) ?>" aria-label="Página anterior">&larr;</a>
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <a class="nexus-pagination__numero <?= $p === $pagina ? 'is-ativo' : '' ?>" href="?pagina=<?= $p ?>"><?= $p ?></a>
                <?php endfor; ?>
                <a class="nexus-pagination__seta" href="?pagina=<?= min($totalPaginas, $pagina + 1) ?>" aria-label="Próxima página">&rarr;</a>
            </div>
        </nav>
    <?php endif; ?>

    <a class="nexus-link" href="<?= base_url('operacional/modulo/' . $modulo['id'] . '/rotinas') ?>">Ver todas as rotinas</a>

<?= $this->endSection() ?>
