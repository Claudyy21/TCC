<?php
/** Espera receber $p (um item de concentracaoPorTipoFalha()) e $totalGeralFiltro. */
$pctDoTotal = $totalGeralFiltro > 0 ? round(($p['total_geral'] / $totalGeralFiltro) * 100, 1) : 0.0;
?>
<div class="nexus-padrao-card">
    <div class="nexus-padrao-card__falha">
        <strong><?= esc($p['tipo_falha_nome']) ?></strong>
        <span>Total de atendimentos: <?= $p['total_geral'] ?> (<?= number_format($pctDoTotal, 1, ',', '.') ?>%)</span>
    </div>
    <div class="nexus-padrao-card__concentracao">
        <span class="nexus-padrao-card__rotulo">Maior concentração</span>
        <span><strong>Rotina:</strong> <?= esc($p['top_rotina_nome']) ?></span>
        <span><strong>Módulo:</strong> <?= esc($p['top_modulo_nome']) ?></span>
    </div>
    <div class="nexus-padrao-card__numero">
        <?= $p['top_total'] ?> Atendimentos<br>
        (<?= number_format($p['top_pct'], 1, ',', '.') ?>% <?= esc(mb_strtolower($p['tipo_falha_nome'])) ?>)
    </div>
</div>
