
<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title><?= e($page_title ?? 'Campus Service Hub') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark nav-main">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            Campus<span class="brand-accent">Hub</span>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="search.php">Cari Servis</a>

                <?php if (is_logged_in()): ?>

                    <a class="nav-link" href="dashboard.php">
                        Dashboard
                    </a>

                    <span class="navbar-text small">
                        <?= e($_SESSION['name']) ?>
                        (<?= e($_SESSION['role']) ?>)
                    </span>

                    <form method="post" action="logout.php">
                        <input type="hidden"
                               name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']) ?>">

                        <button class="btn btn-sm btn-outline-light"
                                type="submit">
                            Log keluar
                        </button>
                    </form>

                <?php else: ?>

                    <a class="nav-link" href="login.php">
                        Log masuk
                    </a>

                    <a class="btn btn-sm btn-light"
                       href="register.php">
                        Daftar akaun
                    </a>

                <?php endif; ?>

            </div>
        </div>
    </div>
</nav>

<main class="container py-4">