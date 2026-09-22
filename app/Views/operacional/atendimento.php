<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <?= view('partials/botao_voltar', ['url' => base_url('operacional/rotina/' . $a['rotina_id'])]) ?>

    <?= view('partials/breadcrumb', [
        'itens' => [
            ['label' => 'Dashboard', 'url' => base_url('operacional')],
            ['label' => $a['modulo_nome'], 'url' => base_url('operacional/modulo/' . $a['modulo_id'])],
            ['label' => $a['rotina_nome'], 'url' => base_url('operacional/rotina/' . $a['rotina_id'])],
            ['label' => 'Atendimento #' . str_pad((string) $a['id'], 5, '0', STR_PAD_LEFT), 'url' => null],
        ],
    ]) ?>

    <h1 class="nexus-page-title nexus-page-title--md">
        Atendimento #<?= str_pad((string) $a['id'], 5, '0', STR_PAD_LEFT) ?>
    </h1>

    <dl class="nexus-detalhe-grid">
        <div class="nexus-detalhe-linha">
            <dt>Data de abertura</dt>
            <dd><?= date('d/m/Y', strtotime($a['data_abertura'])) ?></dd>
        </div>
        <div class="nexus-detalhe-linha">
            <dt>Status</dt>
            <dd>
                <?php if (! empty($a['data_fechamento'])): ?>
                    <span class="nexus-badge nexus-badge--ok">Finalizado</span>
                <?php else: ?>
                    <span class="nexus-badge nexus-badge--pendente">Em andamento</span>
                <?php endif; ?>
            </dd>
        </div>
        <div class="nexus-detalhe-linha">
            <dt>Módulo</dt>
            <dd><?= esc($a['modulo_nome']) ?></dd>
        </div>
        <div class="nexus-detalhe-linha">
            <dt>Rotina</dt>
            <dd><?= esc($a['rotina_nome']) ?></dd>
        </div>
        <div class="nexus-detalhe-linha">
            <dt>Modo de atendimento</dt>
            <dd><?= esc($a['modo_atendimento_nome']) ?></dd>
        </div>
    </dl>

    <section class="nexus-bloco-texto">
        <h2>Tipo de Falha:</h2>
        <p><?= esc($a['tipo_falha_nome']) ?></p>
    </section>

    <section class="nexus-bloco-texto">
        <h2>Problema</h2>
        <p><?= nl2br(esc($a['descricao_problema'])) ?></p>
    </section>

    <section class="nexus-bloco-texto">
        <h2>Solução:</h2>
        <p><?= $a['descricao_solucao'] ? nl2br(esc($a['descricao_solucao'])) : '<span class="nexus-vazio">Ainda sem solução registrada.</span>' ?></p>
    </section>

    <div class="nexus-stats-row">
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= $a['tempo_atendimento_min'] !== null ? $a['tempo_atendimento_min'] . ' minutos' : '—' ?></span>
            <span class="nexus-stat__label">Tempo de atendimento</span>
        </div>
        <div class="nexus-stat">
            <span class="nexus-stat__valor"><?= $a['tempo_resposta_min'] !== null ? $a['tempo_resposta_min'] . ' minutos' : '—' ?></span>
            <span class="nexus-stat__label">Tempo de Resposta</span>
        </div>
    </div>

<?= $this->endSection() ?>
