
<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$page_title = 'Log masuk';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        'SELECT id, name, email, password, role
         FROM users WHERE email = ?'
    );

    $stmt->bind_param('s', $email);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    if (
        $user &&
        password_verify($password, $user['password'])
    ) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        header('Location: dashboard.php');
        exit;

    } else {
        $error = 'E-mel atau password tidak tepat.';
    }
}

require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="form-card p-4 p-md-5">

            <h1 class="h2 fw-bold">Selamat kembali</h1>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    Pendaftaran berjaya. Sila log masuk.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post"
                  data-validate="true"
                  novalidate>

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label" for="email">
                        E-mel
                    </label>

                    <input class="form-control"
                           id="email"
                           type="email"
                           name="email"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password">
                        Password
                    </label>

                    <input class="form-control"
                           id="password"
                           type="password"
                           name="password"
                           required>
                </div>

                <button class="btn btn-primary w-100">
                    Log masuk
                </button>

            </form>

            <p class="mt-3 mb-0">
                Belum ada akaun?
                <a href="register.php">Daftar</a>
            </p>

        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>