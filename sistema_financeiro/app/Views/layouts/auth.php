<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= e($title) ?> | Finaxis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/auth.css')) ?>">
</head>

<body>
    <main class="auth-layout">
        <section class="auth-presentation">
            <div class="auth-presentation-content">
                <div id="authCarousel" class="carousel slide auth-carousel" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="auth-slide">
                                <h2 class="auth-title">Sistema ideal para seu <span>planejamento financeiro!</span></h2><img src="<?= e(asset('assets/images/auth_banner_01.png')) ?>" alt="Planejamento financeiro">
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="auth-slide">
                                <h2 class="auth-title">Organize suas <span>receitas e despesas</span> em um só lugar!</h2><img src="<?= e(asset('assets/images/auth_banner_02.png')) ?>" alt="Controle financeiro">
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="auth-slide">
                                <h2 class="auth-title">Defina seus <span>planos e metas</span> com nossa ajuda!</h2><img src="<?= e(asset('assets/images/auth_banner_03.png')) ?>" alt="Metas financeiras">
                            </div>
                        </div>
                    </div>
                    <div class="carousel-indicators"><button type="button" data-bs-target="#authCarousel" data-bs-slide-to="0" class="active"></button><button type="button" data-bs-target="#authCarousel" data-bs-slide-to="1"></button><button type="button" data-bs-target="#authCarousel" data-bs-slide-to="2"></button></div>
                </div>
            </div>
        </section>
        <section class="auth-form-section">
            <div class="auth-card <?= e($cardClass ?? '') ?>">
                <div class="auth-logo"><img src="<?= e(asset('assets/images/logo_finaxis_dark.png')) ?>" alt="Finaxis"></div><?= $content ?>
            </div>
        </section>
    </main>
    <script>
        window.FINAXIS_BASE_URL = <?= json_encode(base_url()) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(asset('js/auth.js')) ?>"></script>
</body>

</html>