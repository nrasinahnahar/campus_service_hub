
<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$page_title = 'Home | Campus Service Hub';

$sql = "SELECT
            s.id,
            s.title,
            s.description,
            s.price,
            s.image,
            s.created_at,
            u.name AS owner_name
        FROM services s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.created_at DESC
        LIMIT 6";

$result = $conn->query($sql);

require 'includes/header.php';
?>

<section class="hero mb-5">
    <span class="badge text-bg-light mb-3">
        STUDENT SKILLS & SERVICES
    </span>

    <h1 class="display-5 fw-bold">
        Kemahiran pelajar,
        <br>jadi peluang bersama.
    </h1>

    <p class="lead mt-3">
        Temui servis yang ditawarkan oleh komuniti kampus,
        daripada tutoring hingga reka bentuk digital.
    </p>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <a href="search.php" class="btn btn-light btn-lg">
            Teroka servis
        </a>

        <?php if (is_logged_in()): ?>
            <a href="service_form.php"
               class="btn btn-outline-light btn-lg">
                Tambah servis
            </a>
        <?php else: ?>
            <a href="register.php"
               class="btn btn-outline-light btn-lg">
                Mula sekarang
            </a>
        <?php endif; ?>
    </div>
</section>

<div class="d-flex justify-content-between
            align-items-end mb-3">

    <div>
        <h2 class="h3 fw-bold mb-1">
            Servis terkini
        </h2>

        <p class="muted mb-0">
            Servis yang didaftarkan oleh pelajar.
        </p>
    </div>

    <a href="search.php">Lihat semua →</a>
</div>

<div class="row g-4">

<?php if ($result->num_rows === 0): ?>

    <div class="col-12">
        <div class="section-card p-4">
            Belum ada servis. Daftar dan tambah servis pertama!
        </div>
    </div>

<?php else: ?>

    <?php while ($s = $result->fetch_assoc()): ?>

        <div class="col-md-6 col-lg-4">
            <article class="section-card overflow-hidden h-100">

                <?php
                $imagePath = __DIR__ . '/uploads/' .
                             basename($s['image'] ?? '');

                if (
                    !empty($s['image']) &&
                    is_file($imagePath)
                ):
                ?>

                    <img
                        class="service-img"
                        src="uploads/<?= e(basename($s['image'])) ?>"
                        alt="<?= e($s['title']) ?>">

                <?php else: ?>

                    <div class="service-placeholder">
                        CAMPUS SERVICE
                    </div>

                <?php endif; ?>

                <div class="p-3">
                    <h3 class="h5 fw-bold">
                        <?= e($s['title']) ?>
                    </h3>

                    <p class="muted small">
                        Oleh <?= e($s['owner_name']) ?>
                    </p>

                    <p>
                        <?= e(mb_strimwidth(
                            $s['description'],
                            0,
                            115,
                            '…',
                            'UTF-8'
                        )) ?>
                    </p>

                    <div class="price">
                        RM <?= number_format(
                            (float) $s['price'],
                            2
                        ) ?>
                    </div>
                </div>

            </article>
        </div>

    <?php endwhile; ?>

<?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>