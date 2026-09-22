<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('analitica')]) ?>
    <?= view('partials/analitica_tabs', ['ativo' => 'estimativa']) ?>

    <h1 class="nexus-dashboard-title">Estimativa de Tempo</h1>
    <p class="nexus-dashboard-subtitle">Relação entre tempos</p>

    <p class="nexus-aviso">
        Esta estimativa é calculada a partir da <strong>mediana histórica</strong> de atendimentos com o mesmo
        padrão (mesma rotina + mesmo tipo de falha) — não a média, e não um modelo estatístico de regressão.
        A mediana foi escolhida porque um único atendimento fora do padrão (bem mais longo que o normal) puxaria
        a média para cima e distorceria a estimativa; a mediana não sofre esse efeito. Por isso também não
        exibimos R² ou margem de erro: seria dar uma precisão que a conta não tem de verdade.
    </p>

    <form class="nexus-filtros" method="get">
        <label class="nexus-filtros__campo">
            <span>Módulo</span>
            <select id="selectModulo" name="modulo_id">
                <option value="">Selecione</option>
                <?php foreach ($modulos as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $moduloId === (int) $m['id'] ? 'selected' : '' ?>><?= esc($m['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="nexus-filtros__campo">
            <span>Rotina</span>
            <select id="selectRotina" name="rotina_id">
                <option value="">Selecione o módulo primeiro</option>
                <?php foreach ($rotinas as $r): ?>
                    <option value="<?= $r['id'] ?>" data-modulo="<?= $r['modulo_id'] ?>" <?= $rotinaId === (int) $r['id'] ? 'selected' : '' ?>>
                        <?= esc($r['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="nexus-filtros__campo">
            <span>Tipo de Falha</span>
            <select name="tipo_falha_id">
                <option value="">Selecione</option>
                <?php foreach ($tiposFalha as $tf): ?>
                    <option value="<?= $tf['id'] ?>" <?= $tipoFalhaId === (int) $tf['id'] ? 'selected' : '' ?>><?= esc($tf['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" class="nexus-btn nexus-btn--pequeno">Calcular estimativa</button>
    </form>

    <?php if ($resultado === null): ?>

        <p class="nexus-vazio">Selecione módulo, rotina e tipo de falha para calcular a estimativa.</p>

    <?php elseif ($resultado['total'] === 0): ?>

        <p class="nexus-vazio">Não há atendimentos anteriores com esse padrão (essa rotina + esse tipo de falha) para basear uma estimativa.</p>

    <?php else: ?>

        <div class="nexus-estimativa-resultado">
            <h2 class="nexus-section-title">Tempo estimado</h2>
            <p class="nexus-estimativa-resultado__valor">
                <?= \App\Libraries\GraficoPizza::tempoFormatado($resultado['mediana_min']) ?>
            </p>
            <p class="nexus-estimativa-resultado__base">
                Baseado em <?= $resultado['total'] ?> atendimento<?= $resultado['total'] > 1 ? 's' : '' ?> anterior<?= $resultado['total'] > 1 ? 'es' : '' ?> com esse padrão (mediana, não média).
                <?php if ($resultado['minimo_min'] !== null && $resultado['maximo_min'] !== null && $resultado['minimo_min'] !== $resultado['maximo_min']): ?>
                    Já variou de <?= \App\Libraries\GraficoPizza::tempoFormatado($resultado['minimo_min']) ?>
                    a <?= \App\Libraries\GraficoPizza::tempoFormatado($resultado['maximo_min']) ?>.
                <?php endif; ?>
            </p>

            <?php if ($resultado['total'] < 3): ?>
                <p class="nexus-aviso nexus-aviso--atencao">
                    Poucos atendimentos históricos (<?= $resultado['total'] ?>) para esse padrão — a estimativa pode
                    não ser muito confiável ainda.
                </p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Filtra as opções de rotina de acordo com o módulo escolhido
    // (sem AJAX: as rotinas já vêm todas na página, só escondemos as
    // que não pertencem ao módulo selecionado).
    (function () {
        const selectModulo = document.getElementById('selectModulo');
        const selectRotina = document.getElementById('selectRotina');
        const opcoesRotina = Array.from(selectRotina.options);

        function aplicarFiltro() {
            const moduloId = selectModulo.value;
            let primeiraVisivel = null;

            opcoesRotina.forEach((opt) => {
                if (!opt.dataset.modulo) return; // opção "Selecione..."
                const pertence = !moduloId || opt.dataset.modulo === moduloId;
                opt.hidden = !pertence;
                if (pertence && primeiraVisivel === null) primeiraVisivel = opt;
            });

            const selecionadaAindaValida = selectRotina.selectedOptions[0] && !selectRotina.selectedOptions[0].hidden;
            if (!selecionadaAindaValida) {
                selectRotina.value = '';
            }
        }

        selectModulo.addEventListener('change', aplicarFiltro);
        aplicarFiltro();
    })();
</script>
<?= $this->endSection() ?>
