<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'Nexus') ?> &middot; Nexus</title>

    <link rel="icon" href="<?= base_url('assets/img/nexus-mark.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/nexus.css') ?>">

    <?= $this->renderSection('head') ?>
</head>
<body>

<header class="nexus-header">
    <div class="nexus-header__inner">
        <a class="nexus-brand" href="<?= base_url() ?>">
            <svg class="nexus-brand__mark" viewBox="0 0 120 120" fill="none" aria-hidden="true">
                <g stroke="#1565f0" stroke-width="9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M60 12 L101.6 36 L101.6 84 L60 108 L18.4 84 L18.4 36 Z"/>
                    <path d="M60 60 L60 12"/>
                    <path d="M60 60 L18.4 84"/>
                    <path d="M60 60 L101.6 36"/>
                </g>
                <g fill="#1565f0">
                    <circle cx="60" cy="60" r="11"/>
                    <circle cx="101.6" cy="36" r="11"/>
                    <circle cx="18.4" cy="84" r="11"/>
                </g>
            </svg>
            <span>
                <span class="nexus-brand__name">Nexus</span>
                <span class="nexus-brand__tagline">Central de Inteligência para Análise de Atendimentos ERP</span>
            </span>
        </a>

        <?php if (! empty($usuario)): ?>
            <span class="nexus-header__user"><?= esc($usuario) ?></span>
        <?php endif; ?>
    </div>
</header>

<main class="nexus-main">
    <div class="nexus-container">
        <?= $this->renderSection('conteudo') ?>
    </div>
</main>

<footer class="nexus-footer">
    Nexus &middot; Trabalho de Conclusão de Curso &middot; <?= date('Y') ?>
</footer>

<?= $this->renderSection('scripts') ?>

</body>
</html>
