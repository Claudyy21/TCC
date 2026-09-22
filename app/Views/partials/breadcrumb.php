<?php
/**
 * Espera receber $itens: array de ['label' => string, 'url' => string|null].
 * O último item normalmente vem com 'url' => null (página atual, sem link).
 */
?>
<nav class="nexus-breadcrumb" aria-label="breadcrumb">
    <?php foreach ($itens as $i => $item): ?>
        <?php if (! empty($item['url'])): ?>
            <a href="<?= esc($item['url'], 'attr') ?>"><?= esc($item['label']) ?></a>
        <?php else: ?>
            <span><?= esc($item['label']) ?></span>
        <?php endif; ?>
        <?php if ($i < count($itens) - 1): ?>
            <span class="nexus-breadcrumb__sep" aria-hidden="true">&rarr;</span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
