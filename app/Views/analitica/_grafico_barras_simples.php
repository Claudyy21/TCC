<?php
/**
 * Espera receber:
 *   $titulo: string
 *   $itens: array de ['label' => string, 'altura_pct' => int 0-100, 'valor_exibido' => string]
 *   $eixoMax, $eixoMeio: string (rótulos do eixo, já formatados)
 */
?>
<div class="nexus-panel">
    <h3 class="nexus-panel__titulo"><?= esc($titulo) ?></h3>
    <div class="nexus-chart-simples">
        <div class="nexus-chart-simples__eixo">
            <span><?= esc($eixoMax) ?></span>
            <span><?= esc($eixoMeio) ?></span>
            <span>0</span>
        </div>
        <div class="nexus-chart-simples__barras">
            <?php foreach ($itens as $it): ?>
                <div class="nexus-chart-simples__coluna">
                    <div
                        class="nexus-chart-simples__barra"
                        style="height: <?= max(2, (int) $it['altura_pct']) ?>%"
                        title="<?= esc($it['label']) ?>: <?= esc($it['valor_exibido']) ?>"
                    ></div>
                    <span class="nexus-chart-simples__rotulo"><?= esc($it['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
