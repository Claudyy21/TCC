<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('analitica')]) ?>
    <?= view('partials/analitica_tabs', ['ativo' => 'comparativo']) ?>

    <h1 class="nexus-dashboard-title">Análise Comparativa entre Módulos</h1>
    <p class="nexus-dashboard-subtitle">Comparação de Desempenho</p>

    <form class="nexus-filtros" method="get">
        <label class="nexus-filtros__campo">
            <span>Selecionar módulos (até 3)</span>
            <select name="modulos[]" id="selectModulos" multiple size="4">
                <?php foreach ($todosModulos as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= in_array((int) $m['id'], $selecionados, true) ? 'selected' : '' ?>>
                        <?= esc($m['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="nexus-filtros__campo">
            <span>Selecionar período</span>
            <select name="periodo">
                <option value="">Todos os períodos</option>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= esc($p) ?>" <?= $periodoSelecionado === $p ? 'selected' : '' ?>>
                        <?= esc(\App\Libraries\Formatador::periodo($p)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" class="nexus-btn nexus-btn--pequeno">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M13.5 8a5.5 5.5 0 1 1-1.7-3.98M13.5 2v3.5H10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Aplicar filtros</span>
        </button>
    </form>
    <p class="nexus-dica">Segure Ctrl (ou Cmd no Mac) para selecionar mais de um módulo.</p>

    <?php if (empty($colunas)): ?>
        <p class="nexus-vazio">Selecione ao menos um módulo para comparar.</p>
    <?php else: ?>

        <h2 class="nexus-section-title">Indicadores</h2>

        <div class="nexus-tabela-wrap">
            <table class="nexus-tabela-comparativo">
                <thead>
                    <tr>
                        <th>Módulo:</th>
                        <?php foreach ($colunas as $c): ?>
                            <th><?= esc($c['modulo']['nome']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Atendimentos:</th>
                        <?php foreach ($colunas as $c): ?>
                            <td><?= $c['indicadores']['total'] ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Tempo médio:</th>
                        <?php foreach ($colunas as $c): ?>
                            <td><?= \App\Libraries\GraficoPizza::tempoFormatado($c['indicadores']['tempo_medio_min']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Tempo de resposta:</th>
                        <?php foreach ($colunas as $c): ?>
                            <td><?= round($c['indicadores']['tempo_resposta_min']) ?>min</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Reincidência:</th>
                        <?php foreach ($colunas as $c): ?>
                            <td><?= number_format($c['indicadores']['reincidencia_pct'], 1, ',', '.') ?>%</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Backlog:</th>
                        <?php foreach ($colunas as $c): ?>
                            <td><?= $c['indicadores']['backlog'] ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2 class="nexus-section-title">Destaques</h2>
        <div class="nexus-destaques">
            <div class="nexus-destaques__linha">
                <span>Maior Volume de Atendimentos:</span>
                <strong><?= esc($destaques['volume']) ?></strong>
            </div>
            <div class="nexus-destaques__linha">
                <span>Maior Tempo Médio:</span>
                <strong><?= esc($destaques['tempo_medio']) ?></strong>
            </div>
            <div class="nexus-destaques__linha">
                <span>Maior Tempo de Resposta:</span>
                <strong><?= esc($destaques['tempo_resposta']) ?></strong>
            </div>
        </div>

    <?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Limita a seleção do múltiplo a 3 módulos.
    document.getElementById('selectModulos').addEventListener('change', function () {
        const selecionadas = Array.from(this.selectedOptions);
        if (selecionadas.length > 3) {
            selecionadas[selecionadas.length - 1].selected = false;
            alert('Você pode comparar no máximo 3 módulos por vez.');
        }
    });
</script>
<?= $this->endSection() ?>
