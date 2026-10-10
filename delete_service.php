
<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

require_csrf();

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    header('Location: dashboard.php?msg=' .
           urlencode('ID servis tidak sah.'));
    exit;
}

$stmt = $conn->prepare(
    'SELECT user_id, image
     FROM services
     WHERE id = ?'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    header('Location: dashboard.php?msg=' .
           urlencode('Servis tidak ditemui.'));
    exit;
}

if (
    !is_admin() &&
    (int) $service['user_id'] !==
    (int) $_SESSION['user_id']
) {
    http_response_code(403);
    exit('Anda tidak dibenarkan memadam servis ini.');
}

$stmt = $conn->prepare(
    'DELETE FROM services WHERE id = ?'
);

$stmt->bind_param('i', $id);
$stmt->execute();

if (!empty($service['image'])) {
    $imagePath = __DIR__ . '/uploads/' .
                 basename($service['image']);

    if (is_file($imagePath)) {
        @unlink($imagePath);
    }
}

header(
    'Location: dashboard.php?msg=' .
    urlencode('Servis berjaya dipadam.')
);

exit;