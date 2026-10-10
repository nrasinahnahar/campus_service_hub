
<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

require_login();

$page_title = 'Dashboard';

if (is_admin()) {

    $stmt = $conn->prepare(
        'SELECT
            s.id,
            s.title,
            s.price,
            s.user_id,
            u.name AS owner_name
         FROM services s
         JOIN users u ON s.user_id = u.id
         ORDER BY s.created_at DESC'
    );

} else {

    $stmt = $conn->prepare(
        'SELECT
            s.id,
            s.title,
            s.price,
            s.user_id,
            u.name AS owner_name
         FROM services s
         JOIN users u ON s.user_id = u.id
         WHERE s.user_id = ?
         ORDER BY s.created_at DESC'
    );

    $stmt->bind_param('i', $_SESSION['user_id']);
}

$stmt->execute();
$services = $stmt->get_result();

require 'includes/header.php';
?>

<div class="d-flex flex-wrap
            justify-content-between
            align-items-center gap-3 mb-4">

    <div>
        <h1 class="h2 fw-bold">Dashboard</h1>

        <p class="muted mb-0">
            Hai <?= e($_SESSION['name']) ?>.
            Peranan: <?= e($_SESSION['role']) ?>.
        </p>
    </div>

    <a class="btn btn-primary" href="service_form.php">
        + Tambah servis
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>

    <div class="alert alert-success">
        <?= e($_GET['msg']) ?>
    </div>

<?php endif; ?>

<div class="section-card p-3 p-md-4">
    <div class="table-responsive">

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Servis</th>

                    <?php if (is_admin()): ?>
                        <th>Pemilik</th>
                    <?php endif; ?>

                    <th>Harga</th>
                    <th>Tindakan</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!$services->num_rows): ?>

                <tr>
                    <td colspan="<?= is_admin() ? 4 : 3 ?>"
                        class="text-center py-4">
                        Belum ada servis. Klik Tambah servis.
                    </td>
                </tr>

            <?php endif; ?>

            <?php while ($s = $services->fetch_assoc()): ?>

                <tr>
                    <td class="fw-semibold">
                        <?= e($s['title']) ?>
                    </td>

                    <?php if (is_admin()): ?>
                        <td><?= e($s['owner_name']) ?></td>
                    <?php endif; ?>

                    <td>
                        RM <?= number_format(
                            (float) $s['price'],
                            2
                        ) ?>
                    </td>

                    <td>
                        <div class="d-flex gap-2">

                            <a class="btn btn-sm btn-outline-primary"
                               href="service_form.php?id=<?= (int) $s['id'] ?>">
                                Edit
                            </a>

                            <form method="post"
                                  action="delete_service.php"
                                  onsubmit="return confirm('Padam servis ini?')">

                                <input type="hidden"
                                       name="csrf_token"
                                       value="<?= e($_SESSION['csrf_token']) ?>">

                                <input type="hidden"
                                       name="id"
                                       value="<?= (int) $s['id'] ?>">

                                <button class="btn btn-sm btn-outline-danger">
                                    Padam
                                </button>

                            </form>

                        </div>
                    </td>
                </tr>

            <?php endwhile; ?>

            </tbody>
        </table>

    </div>
</div>

<?php require 'includes/footer.php'; ?>