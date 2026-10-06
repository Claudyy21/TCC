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
                Baseado em <?= $resultado['total'] ?> atendimento<?= $resultado['total'] > 1 ? 's' : '' ?> anterior<?= $resultado['total'] > 1 ? 'es' : '' ?> com esse padrão.
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

        <?php if (count($historico) >= 2): ?>
            <?php
                // --- geometria do gráfico de linha (SVG puro) ---
                $largura = 640;
                $altura  = 220;
                $padEsq  = 42;
                $padDir  = 16;
                $padTopo = 20;
                $padBase = 34;
                $plotW   = $largura - $padEsq - $padDir;
                $plotH   = $altura - $padTopo - $padBase;

                $n = count($historico);
                $maiorValor = max(array_merge(array_column($historico, 'tempo_min'), [$resultado['mediana_min']]));
                $maiorValor = $maiorValor > 0 ? $maiorValor : 1;

                $x = static fn (int $i) => $n > 1
                    ? $padEsq + ($i / ($n - 1)) * $plotW
                    : $padEsq + $plotW / 2;
                $y = static fn (float $valor) => $padTopo + $plotH - ($valor / $maiorValor) * $plotH;

                $pontosReal = [];
                foreach ($historico as $i => $h) {
                    $pontosReal[] = sprintf('%.1F,%.1F', $x($i), $y($h['tempo_min']));
                }
                $linhaReal = implode(' ', $pontosReal);

                $yEstimado = $y($resultado['mediana_min']);
                $linhaEstimada = sprintf('%.1F,%.1F %.1F,%.1F', $padEsq, $yEstimado, $padEsq + $plotW, $yEstimado);
            ?>
            <h2 class="nexus-section-title">Tempo real x Tempo Estimado</h2>
            <div class="nexus-panel">

                <div class="nexus-chart-legenda">
                    <span class="nexus-chart-legenda__item">
                        <span class="nexus-chart-legenda__amostra nexus-chart-legenda__amostra--real"></span>
                        Tempo real
                    </span>
                    <span class="nexus-chart-legenda__item">
                        <span class="nexus-chart-legenda__amostra nexus-chart-legenda__amostra--estimado"></span>
                        Tempo estimado (mediana)
                    </span>
                </div>

                <svg viewBox="0 0 <?= $largura ?> <?= $altura ?>" class="nexus-linha-chart" role="img"
                     aria-label="Comparação entre o tempo real de cada atendimento e o tempo estimado">

                    <!-- eixo -->
                    <line x1="<?= $padEsq ?>" y1="<?= $padTopo ?>" x2="<?= $padEsq ?>" y2="<?= $padTopo + $plotH ?>" class="nexus-linha-chart__eixo" />
                    <line x1="<?= $padEsq ?>" y1="<?= $padTopo + $plotH ?>" x2="<?= $padEsq + $plotW ?>" y2="<?= $padTopo + $plotH ?>" class="nexus-linha-chart__eixo" />

                    <!-- rótulos do eixo Y -->
                    <text x="<?= $padEsq - 8 ?>" y="<?= $padTopo + 4 ?>" class="nexus-linha-chart__rotulo-y" text-anchor="end">
                        <?= esc(\App\Libraries\GraficoPizza::tempoFormatado($maiorValor)) ?>
                    </text>
                    <text x="<?= $padEsq - 8 ?>" y="<?= $padTopo + $plotH + 4 ?>" class="nexus-linha-chart__rotulo-y" text-anchor="end">0</text>

                    <!-- linha do tempo estimado (constante, mediana atual) -->
                    <polyline points="<?= $linhaEstimada ?>" class="nexus-linha-chart__linha nexus-linha-chart__linha--estimado" />

                    <!-- linha do tempo real -->
                    <polyline points="<?= $linhaReal ?>" class="nexus-linha-chart__linha nexus-linha-chart__linha--real" />

                    <?php foreach ($historico as $i => $h): ?>
                        <circle
                            cx="<?= sprintf('%.1F', $x($i)) ?>"
                            cy="<?= sprintf('%.1F', $y($h['tempo_min'])) ?>"
                            r="4"
                            class="nexus-linha-chart__ponto"
                        >
                            <title><?= date('d/m/Y', strtotime($h['data_abertura'])) ?>: <?= esc(\App\Libraries\GraficoPizza::tempoFormatado($h['tempo_min'])) ?></title>
                        </circle>
                        <text
                            x="<?= sprintf('%.1F', $x($i)) ?>"
                            y="<?= $padTopo + $plotH + 20 ?>"
                            class="nexus-linha-chart__rotulo-x"
                            text-anchor="middle"
                        ><?= date('d/m', strtotime($h['data_abertura'])) ?></text>
                    <?php endforeach; ?>

                </svg>

                <p class="nexus-comparativo-chart__legenda">
                    Cada ponto azul é o tempo real de um atendimento já finalizado com esse padrão; a linha laranja
                    marca o tempo estimado (mediana) atual.
                </p>
            </div>
        <?php endif; ?>

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
