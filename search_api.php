
<?php
require_once 'config/db.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) > 100) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Kata kunci terlalu panjang.'
    ]);
    exit;
}

$like = '%' . $q . '%';

$stmt = $conn->prepare(
    "SELECT
        s.id,
        s.title,
        s.description,
        s.price,
        s.image,
        u.name AS owner_name
     FROM services s
     JOIN users u ON s.user_id = u.id
     WHERE s.title LIKE ?
        OR s.description LIKE ?
        OR u.name LIKE ?
     ORDER BY s.created_at DESC
     LIMIT 50"
);

$stmt->bind_param(
    'sss',
    $like,
    $like,
    $like
);

$stmt->execute();

$data = $stmt->get_result()->fetch_all(
    MYSQLI_ASSOC
);

echo json_encode(
    $data,
    JSON_UNESCAPED_UNICODE |
    JSON_INVALID_UTF8_SUBSTITUTE
);