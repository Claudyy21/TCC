<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('operacional/modulo/' . $rotina['modulo_id'])]) ?>

    <?= view('partials/breadcrumb', [
        'itens' => [
            ['label' => 'Dashboard', 'url' => base_url('operacional')],
            ['label' => $rotina['modulo_nome'], 'url' => base_url('operacional/modulo/' . $rotina['modulo_id'])],
            ['label' => $rotina['nome'], 'url' => null],
        ],
    ]) ?>

    <h1 class="nexus-page-title nexus-page-title--md"><?= esc($rotina['nome']) ?></h1>
    <p class="nexus-page-subtitle">Atendimentos da rotina</p>

    <div class="nexus-stats-row">
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= $rotina['total_atendimentos'] ?></span>
            <span class="nexus-stat__label">Atendimentos</span>
        </div>
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= \App\Libraries\GraficoPizza::tempoFormatado($rotina['tempo_total_min']) ?></span>
            <span class="nexus-stat__label">Tempo total</span>
        </div>
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= \App\Libraries\GraficoPizza::tempoFormatado($rotina['tempo_medio_min']) ?></span>
            <span class="nexus-stat__label">Tempo médio</span>
        </div>
    </div>

    <form class="nexus-search-bar" method="get">
        <div class="nexus-search-bar__campo">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <circle cx="7" cy="7" r="5.2" stroke="currentColor" stroke-width="1.6"/>
                <path d="M11 11l3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <input type="text" name="busca" placeholder="Pesquisar por tipo de falha ou problema"
                   value="<?= esc($busca ?? '') ?>">
        </div>

        <label class="nexus-search-bar__filtro">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M2 3h12M4.5 8h7M7 13h2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                <option value="finalizado" <?= ($status ?? '') === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                <option value="aberto" <?= ($status ?? '') === 'aberto' ? 'selected' : '' ?>>Em andamento</option>
            </select>
        </label>

        <button type="submit" class="nexus-btn nexus-btn--pequeno nexus-btn--pesquisar">Pesquisar</button>
    </form>

    <div class="nexus-list">
        <?php foreach ($itens as $item): ?>
            <div class="nexus-list-item">
                <div>
                    <p class="nexus-list-item__titulo">
                        #<?= str_pad((string) $item['id'], 5, '0', STR_PAD_LEFT) ?>
                        &nbsp;<?= esc($item['tipo_falha_nome']) ?>
                    </p>
                    <p class="nexus-list-item__meta">
                        Tempo total: <?= \App\Libraries\GraficoPizza::tempoFormatado($item['tempo_atendimento_min'] ?? 0) ?>
                        &middot; <?= date('d/m/Y', strtotime($item['data_abertura'])) ?>
                    </p>
                </div>
                <a class="nexus-btn nexus-btn--pequeno" href="<?= base_url('operacional/atendimento/' . $item['id']) ?>">
                    <span>Acessar</span>
                    <svg width="22" height="10" viewBox="0 0 26 12" fill="none" aria-hidden="true">
                        <path d="M1 6h22M18 1l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($itens)): ?>
            <p class="nexus-vazio">Nenhum atendimento encontrado com esses filtros.</p>
        <?php endif; ?>
    </div>

    <?php
        $queryBase = array_filter(['busca' => $busca ?? '', 'status' => $status ?? '']);
        $comQuery  = fn (int $p) => '?' . http_build_query($queryBase + ['pagina' => $p]);
    ?>
    <?php if ($totalPaginas > 1): ?>
        <nav class="nexus-pagination" aria-label="Paginação de atendimentos">
            <span class="nexus-pagination__label">Página <?= $pagina ?> de <?= $totalPaginas ?></span>
            <div class="nexus-pagination__controles">
                <a class="nexus-pagination__seta" href="<?= $comQuery(max(1, $pagina - 1)) ?>" aria-label="Página anterior">&larr;</a>
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <a class="nexus-pagination__numero <?= $p === $pagina ? 'is-ativo' : '' ?>" href="<?= $comQuery($p) ?>"><?= $p ?></a>
                <?php endfor; ?>
                <a class="nexus-pagination__seta" href="<?= $comQuery(min($totalPaginas, $pagina + 1)) ?>" aria-label="Próxima página">&rarr;</a>
            </div>
        </nav>
    <?php endif; ?>

<?= $this->endSection() ?>
