<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('analitica')]) ?>
    <?= view('partials/analitica_tabs', ['ativo' => 'padroes']) ?>

    <h1 class="nexus-dashboard-title">Análise de Padrões</h1>
    <p class="nexus-dashboard-subtitle">Identificação entre módulos</p>

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

        <label class="nexus-filtros__campo">
            <span>Selecionar tipo de falha</span>
            <select name="tipo_falha_id" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach ($tiposFalha as $tf): ?>
                    <option value="<?= $tf['id'] ?>" <?= $tipoFalhaId === (int) $tf['id'] ? 'selected' : '' ?>><?= esc($tf['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <?php if (empty($padroes)): ?>

        <p class="nexus-vazio">Nenhum atendimento encontrado com esses filtros.</p>

    <?php else: ?>

        <h2 class="nexus-section-title">Tipos de falha e maior concentração</h2>

        <div class="nexus-padrao-lista">
            <?php foreach (array_slice($padroes, 0, 4) as $p): ?>
                <?= view('analitica/_padrao_card', ['p' => $p, 'totalGeralFiltro' => $totalGeralFiltro]) ?>
            <?php endforeach; ?>

            <?php if (count($padroes) > 4): ?>
                <details class="nexus-ver-mais">
                    <summary>Ver mais &darr;</summary>
                    <div class="nexus-padrao-lista">
                        <?php foreach (array_slice($padroes, 4) as $p): ?>
                            <?= view('analitica/_padrao_card', ['p' => $p, 'totalGeralFiltro' => $totalGeralFiltro]) ?>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>

        <h2 class="nexus-section-title">Principais padrões identificados</h2>

        <div class="nexus-list">
            <?php foreach (array_slice($padroes, 0, 3) as $p): ?>
                <div class="nexus-list-item">
                    <p class="nexus-list-item__titulo" style="max-width: 520px;">
                        A rotina de <strong><?= esc($p['top_rotina_nome']) ?></strong> concentra o maior volume de
                        atendimentos do tipo <strong><?= esc(mb_strtolower($p['tipo_falha_nome'])) ?></strong>,
                        no módulo <strong><?= esc($p['top_modulo_nome']) ?></strong>.
                    </p>
                    <span class="nexus-badge-info">
                        <?= $p['top_total'] ?> atendimentos<br>
                        <?= number_format($p['top_pct'], 1, ',', '.') ?>% desse tipo
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

<?= $this->endSection() ?>
