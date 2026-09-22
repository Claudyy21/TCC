<?php
/** Espera receber $ativo: 'dashboard' | 'comparativo' | 'padroes' | 'evolucao' | 'relacoes' | 'estimativa'. */
?>
<nav class="nexus-tabs" aria-label="Seções da Analítica">
    <a class="nexus-tabs__item <?= $ativo === 'dashboard' ? 'is-ativo' : '' ?>" href="<?= base_url('analitica') ?>">
        Dashboard
    </a>
    <a class="nexus-tabs__item <?= $ativo === 'comparativo' ? 'is-ativo' : '' ?>" href="<?= base_url('analitica/comparativo') ?>">
        Comparativo entre Módulos
    </a>
    <a class="nexus-tabs__item <?= $ativo === 'padroes' ? 'is-ativo' : '' ?>" href="<?= base_url('analitica/padroes') ?>">
        Análise de Padrões
    </a>
    <a class="nexus-tabs__item <?= $ativo === 'evolucao' ? 'is-ativo' : '' ?>" href="<?= base_url('analitica/evolucao') ?>">
        Evolução dos Indicadores
    </a>
    <a class="nexus-tabs__item <?= $ativo === 'relacoes' ? 'is-ativo' : '' ?>" href="<?= base_url('analitica/relacoes') ?>">
        Análise de Relações
    </a>
    <a class="nexus-tabs__item <?= $ativo === 'estimativa' ? 'is-ativo' : '' ?>" href="<?= base_url('analitica/estimativa') ?>">
        Estimativa de Tempo
    </a>
</nav>
