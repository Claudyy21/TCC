<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('analitica')]) ?>
    <?= view('partials/analitica_tabs', ['ativo' => 'relacoes']) ?>

    <h1 class="nexus-dashboard-title">Análise de Relações</h1>
    <p class="nexus-dashboard-subtitle">Relação entre características</p>

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
                <option value="">Todos</option>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= esc($p) ?>" <?= $periodo === $p ? 'selected' : '' ?>><?= esc(\App\Libraries\Formatador::periodo($p)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <?php if ($moduloId): ?>
        <p class="nexus-dica">Com um módulo específico selecionado, a correlação de "Módulo" fica oculta — comparar o módulo consigo mesmo não faz sentido.</p>
    <?php endif; ?>

    <h2 class="nexus-section-title">Relação com o tempo de atendimento</h2>

    <div class="nexus-panel">
        <div class="nexus-barras">
            <?php $maiorEta = max(array_column($relacoes, 'eta2') ?: [0.0001]) ?: 0.0001; ?>
            <?php foreach ($relacoes as $r): ?>
                <?php $segmentos = (int) round(($r['eta2'] / max($maiorEta, 0.0001)) * 10); ?>
                <div class="nexus-barra-linha">
                    <span class="nexus-barra-linha__label"><?= esc($r['nome']) ?></span>
                    <span class="nexus-barra-segmentos" aria-hidden="true">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <span class="<?= $i <= $segmentos ? 'preenchido' : '' ?>"></span>
                        <?php endfor; ?>
                    </span>
                    <span class="nexus-barra-linha__valor"><?= number_format($r['eta2'], 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h2 class="nexus-section-title">Relações identificadas</h2>

    <div class="nexus-padrao-lista">
        <?php foreach ($relacoes as $r): ?>
            <div class="nexus-relacao-card">
                <h3 class="nexus-relacao-card__titulo"><?= esc($r['nome']) ?> x Tempo de Atendimento</h3>
                <p class="nexus-relacao-card__meta">
                    Correlação: <?= number_format($r['eta2'], 2, ',', '.') ?>
                    &middot; <?= esc(\App\Libraries\Formatador::classificarAssociacao($r['eta2'])) ?>
                </p>
                <?php if ($r['destaque']): ?>
                    <p class="nexus-relacao-card__texto">
                        <strong><?= esc($r['destaque']['nome']) ?></strong> apresenta o maior tempo médio de atendimento
                        (<?= \App\Libraries\GraficoPizza::tempoFormatado($r['destaque']['tempo_medio_min']) ?>) entre as categorias de <?= esc(mb_strtolower($r['nome'])) ?>.
                    </p>
                <?php else: ?>
                    <p class="nexus-relacao-card__texto nexus-vazio">Sem dados suficientes para essa comparação.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

<?= $this->endSection() ?>
