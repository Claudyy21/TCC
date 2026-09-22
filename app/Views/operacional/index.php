<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url()]) ?>

    <div class="nexus-toolbar">
        <button type="button" id="nexusBtnAtualizar" class="nexus-btn nexus-btn--sync">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M13.5 8a5.5 5.5 0 1 1-1.7-3.98M13.5 2v3.5H10"
                      stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Atualizar dados</span>
        </button>
    </div>

    <h1 class="nexus-dashboard-title">Dashboard Operacional</h1>
    <p class="nexus-dashboard-subtitle">Clique em um módulo para visualizar detalhes</p>

    <div class="nexus-chart-wrap" id="nexusChartWrap">
        <svg id="nexusGraficoPizza" data-base-url="<?= base_url() ?>" viewBox="0 0 320 320" class="nexus-pizza">
            <circle cx="160" cy="160" r="152" fill="none" stroke="#fff" stroke-width="4"></circle>
            <g id="nexusGrupoFatias">
                <?php foreach ($fatias as $f): ?>
                    <path
                        class="nexus-fatia"
                        d="<?= $f['path'] ?>"
                        fill="<?= $f['cor'] ?>"
                        data-id="<?= $f['id'] ?>"
                        data-nome="<?= esc($f['nome'], 'attr') ?>"
                        data-atendimentos="<?= $f['atendimentos'] ?>"
                        data-rotinas="<?= $f['rotinas'] ?>"
                        data-tempo-medio-min="<?= $f['tempo_medio_min'] ?>"
                    ></path>
                <?php endforeach; ?>
            </g>
        </svg>

        <div id="nexusTooltip" class="nexus-tooltip" hidden></div>
    </div>

    <div class="nexus-footer-stats">
        <span id="nexusStatAtendimentos"><?= $totalAtendimentos ?> Atendimentos</span>
        <span id="nexusStatModulos"><?= $totalModulos ?> Módulos</span>
        <span id="nexusStatSync">Última Sync: <?= esc($ultimaSync) ?></span>
    </div>

    <div class="nexus-modal-overlay" id="nexusModalOverlay" hidden>
        <div class="nexus-modal" role="dialog" aria-modal="true">
            <h2 class="nexus-modal__title" id="nexusModalTitulo">MÓDULO</h2>
            <p class="nexus-modal__linha" id="nexusModalAtendimentos"></p>
            <p class="nexus-modal__linha" id="nexusModalRotinas"></p>
            <p class="nexus-modal__linha">
                Tempo médio<br>
                <strong id="nexusModalTempo"></strong>
            </p>
            <div class="nexus-modal__acoes">
                <a href="#" id="nexusModalExplorar" class="nexus-btn nexus-btn--modal">Explorar Módulo</a>
                <button type="button" id="nexusModalVoltar" class="nexus-btn nexus-btn--voltar">Voltar</button>
            </div>
        </div>
    </div>

    <div id="nexusToast" class="nexus-toast" hidden></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('assets/js/grafico-pizza.js') ?>"></script>
<?= $this->endSection() ?>
