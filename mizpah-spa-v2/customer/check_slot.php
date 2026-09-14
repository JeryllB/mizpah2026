<?php
include '../includes/db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';

$date = mysqli_real_escape_string($conn, $date);
$time = mysqli_real_escape_string($conn, $time);

/* TOTAL SLOT LIMIT (beds capacity) */
$limit = 4;

$sql = "
SELECT COUNT(*) AS cnt 
FROM bookings 
WHERE booking_date = '$date' 
AND booking_time = '$time'
AND status != 'Cancelled'
";

$q = mysqli_query($conn, $sql);

if (!$q) {
    echo json_encode([
        'available' => false,
        'remaining' => 0
    ]);
    exit;
}

$row = mysqli_fetch_assoc($q);
$count = (int)($row['cnt'] ?? 0);

echo json_encode([
    'available' => ($count < $limit),
    'remaining' => max(0, $limit - $count)
]);
?>