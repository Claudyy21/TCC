<?php
/**
 * Espera receber $url: para onde o botão "Voltar" deve levar.
 *
 * Propositalmente NÃO usa window.history.back(). O histórico do
 * navegador segue a ordem literal de páginas visitadas, que nem
 * sempre bate com a hierarquia lógica das telas (ex.: se a pessoa
 * navega até a Rotina, volta ao Dashboard pela breadcrumb, e só
 * depois clica em "Voltar" — o histórico mandaria de volta pra
 * Rotina, não faz sentido). Um link fixo para o nível acima é
 * sempre previsível.
 */
?>
<a href="<?= esc($url, 'attr') ?>" class="nexus-btn-voltar">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
        <path d="M10 3 5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span>Voltar</span>
</a>
