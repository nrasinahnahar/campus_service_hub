
<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$page_title = 'Daftar akaun';
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Nama wajib diisi, maksimum 100 aksara.';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        mb_strlen($email) > 150
    ) {
        $errors[] = 'Masukkan e-mel yang sah.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password mesti sekurang-kurangnya 8 aksara.';
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            'SELECT id FROM users WHERE email = ?'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'E-mel ini sudah didaftarkan.';
        }
    }

    if (!$errors) {
        $hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, 'user')"
        );

        $stmt->bind_param(
            'sss',
            $name,
            $email,
            $hash
        );

        $stmt->execute();

        header('Location: login.php?registered=1');
        exit;
    }
}

require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="form-card p-4 p-md-5">

            <h1 class="h2 fw-bold">Cipta akaun</h1>
            <p class="muted">Sertai komuniti CampusHub.</p>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post"
                  data-validate="true"
                  novalidate>

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label" for="name">
                        Nama penuh
                    </label>

                    <input class="form-control"
                           id="name"
                           name="name"
                           maxlength="100"
                           required
                           value="<?= e($name) ?>">

                    <div class="invalid-feedback">
                        Masukkan nama.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">
                        E-mel
                    </label>

                    <input class="form-control"
                           id="email"
                           name="email"
                           type="email"
                           maxlength="150"
                           required
                           value="<?= e($email) ?>">

                    <div class="invalid-feedback">
                        Masukkan e-mel yang sah.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password">
                        Password
                    </label>

                    <input class="form-control"
                           id="password"
                           name="password"
                           type="password"
                           minlength="8"
                           required>

                    <div class="form-text">
                        Minimum 8 aksara.
                    </div>
                </div>

                <button class="btn btn-primary w-100">
                    Daftar
                </button>

            </form>

            <p class="mt-3 mb-0">
                Sudah ada akaun?
                <a href="login.php">Log masuk</a>
            </p>

        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>