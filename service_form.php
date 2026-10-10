
<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$editing = $id > 0;

$errors = [];
$title = '';
$description = '';
$price = '';
$existingImage = null;

// Get service details when editing
if ($editing) {
    $stmt = $conn->prepare(
        'SELECT * FROM services WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $current = $stmt->get_result()->fetch_assoc();

    if (!$current) {
        http_response_code(404);
        exit('Servis tidak ditemui.');
    }

    // Only admin or service owner can edit
    if (
        !is_admin() &&
        (int)$current['user_id'] !== (int)$_SESSION['user_id']
    ) {
        http_response_code(403);
        exit('Anda tidak dibenarkan mengubah servis ini.');
    }

    $title = $current['title'];
    $description = $current['description'];
    $price = $current['price'];
    $existingImage = $current['image'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');

    // Validate title
    if ($title === '' || mb_strlen($title) > 150) {
        $errors[] = 'Tajuk wajib diisi, maksimum 150 aksara.';
    }

    // Validate description
    if (
        $description === '' ||
        mb_strlen($description) > 5000
    ) {
        $errors[] = 'Penerangan wajib diisi, maksimum 5000 aksara.';
    }

    // Validate price
    if (
        !is_numeric($price) ||
        (float)$price < 0 ||
        (float)$price > 99999999
    ) {
        $errors[] = 'Masukkan harga yang sah.';
    }

    $imageName = $existingImage;
    $newImagePath = null;

    $hasUpload = (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    );

    // Image required when adding a new service
    if (!$editing && !$hasUpload) {
        $errors[] = 'Gambar servis diperlukan.';
    }

    if (
        $hasUpload &&
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {
        $errors[] = 'Muat naik gambar gagal.';
    }

    // Validate and upload image
    if (
        $hasUpload &&
        $_FILES['image']['error'] === UPLOAD_ERR_OK
    ) {

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Saiz maksimum gambar ialah 2 MB.';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['image']['tmp_name']);

        $types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        if (!isset($types[$mime])) {
            $errors[] = 'Gambar mesti JPG atau PNG.';
        }

        if (!$errors) {
            $uploadDir = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imageName = bin2hex(random_bytes(16))
                       . '.' . $types[$mime];

            $newImagePath = $uploadDir . $imageName;

            if (!move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $newImagePath
            )) {
                $errors[] = 'Tidak dapat menyimpan gambar.';
                $newImagePath = null;
            }
        }
    }

    // Save service to database
    if (!$errors) {

        if ($editing) {

            // Recheck ownership before updating
            $check = $conn->prepare(
                'SELECT user_id, image
                 FROM services
                 WHERE id = ?'
            );

            $check->bind_param('i', $id);
            $check->execute();

            $latest = $check->get_result()->fetch_assoc();

            if (!$latest) {
                http_response_code(404);
                exit('Servis tidak ditemui.');
            }

            if (
                !is_admin() &&
                (int)$latest['user_id'] !== (int)$_SESSION['user_id']
            ) {
                http_response_code(403);
                exit('Anda tidak dibenarkan mengubah servis ini.');
            }

            $oldImage = $latest['image'];

            $stmt = $conn->prepare(
                'UPDATE services
                 SET title = ?, description = ?,
                     price = ?, image = ?
                 WHERE id = ?'
            );

            $stmt->bind_param(
                'ssdsi',
                $title,
                $description,
                $price,
                $imageName,
                $id
            );

            $stmt->execute();

            // Delete previous image if replaced
            if (
                $newImagePath &&
                $oldImage &&
                is_file(
                    __DIR__ . '/uploads/' . basename($oldImage)
                )
            ) {
                unlink(
                    __DIR__ . '/uploads/' . basename($oldImage)
                );
            }

        } else {

            // Add new service
            $uid = (int)$_SESSION['user_id'];

            $stmt = $conn->prepare(
                'INSERT INTO services
                 (user_id, title, description, price, image)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'issds',
                $uid,
                $title,
                $description,
                $price,
                $imageName
            );

            $stmt->execute();
        }

        // Redirect after successful save
        header(
            'Location: dashboard.php?msg=' .
            urlencode(
                $editing
                ? 'Servis berjaya dikemas kini.'
                : 'Servis berjaya ditambah.'
            )
        );
        exit;
    }

    // Remove uploaded file if validation fails
    if ($newImagePath && is_file($newImagePath)) {
        unlink($newImagePath);
    }
}

$page_title = $editing ? 'Edit servis' : 'Tambah servis';

require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="form-card p-4 p-md-5">

            <h1 class="h2 fw-bold">
                <?= $editing
                    ? 'Edit servis'
                    : 'Tambah servis baharu' ?>
            </h1>

            <p class="muted">
                Isi maklumat servis dengan lengkap.
            </p>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post"
                  enctype="multipart/form-data"
                  data-validate="true"
                  novalidate>

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <!-- Service title -->
                <div class="mb-3">
                    <label class="form-label" for="title">
                        Tajuk servis
                    </label>

                    <input class="form-control"
                           id="title"
                           name="title"
                           maxlength="150"
                           required
                           value="<?= e($title) ?>">
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label" for="description">
                        Penerangan
                    </label>

                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"
                              maxlength="5000"
                              required><?= e($description) ?></textarea>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label class="form-label" for="price">
                        Harga (RM)
                    </label>

                    <input class="form-control"
                           id="price"
                           name="price"
                           type="number"
                           min="0"
                           max="99999999"
                           step="0.01"
                           required
                           value="<?= e($price) ?>">
                </div>

                <!-- Image upload -->
                <div class="mb-4">
                    <label class="form-label" for="image">
                        Gambar servis
                    </label>

                    <input class="form-control"
                           id="image"
                           name="image"
                           type="file"
                           accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                           data-image-check
                           <?= $editing ? '' : 'required' ?>>

                    <div class="form-text">
                        JPG atau PNG sahaja, maksimum 2 MB.
                        <?= $editing
                            ? 'Pilih fail hanya jika mahu tukar gambar.'
                            : '' ?>
                    </div>

                    <div class="file-feedback small mt-1"></div>

                    <?php if (
                        $existingImage &&
                        is_file(
                            __DIR__ . '/uploads/' .
                            basename($existingImage)
                        )
                    ): ?>

                        <img
                            class="mt-3 rounded"
                            style="max-width:180px"
                            src="uploads/<?= e(basename($existingImage)) ?>"
                            alt="Gambar semasa">

                    <?php endif; ?>
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-primary">
                    <?= $editing
                        ? 'Simpan perubahan'
                        : 'Simpan servis' ?>
                </button>

                <a class="btn btn-outline-secondary"
                   href="dashboard.php">
                    Batal
                </a>

            </form>

        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
