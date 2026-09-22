<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>

    <h1 class="nexus-page-title">Central de Inteligência &ndash; Nexus</h1>
    <p class="nexus-page-subtitle">Acesse a área desejada</p>

    <div class="nexus-cards">
        <?php foreach ($areas as $area): ?>
            <article class="nexus-card">
                <div class="nexus-card__header">
                    <h2 class="nexus-card__title"><?= esc($area['titulo']) ?></h2>
                </div>

                <div class="nexus-card__body">
                    <p class="nexus-card__lead"><?= esc($area['chamada']) ?></p>
                    <p class="nexus-card__text"><?= esc($area['descricao']) ?></p>
                </div>

                <div class="nexus-card__footer">
                    <a class="nexus-btn" href="<?= esc($area['rota'], 'attr') ?>">
                        <span>Acessar</span>
                        <svg class="nexus-btn__icon" width="26" height="12" viewBox="0 0 26 12" fill="none" aria-hidden="true">
                            <path d="M1 6h22M18 1l5 5-5 5" stroke="currentColor" stroke-width="1.8"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

<?= $this->endSection() ?>
