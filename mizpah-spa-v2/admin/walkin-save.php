<?php
session_start();
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = (int)$_SESSION['user_id'];

/* ================= GET DATA ================= */
$customer_name = $_POST['customer_name'] ?? '';
$phone         = $_POST['phone'] ?? '';

$service_id    = $_POST['service_id'] ?? 0;
$service       = $_POST['service'] ?? '';
$duration      = $_POST['duration'] ?? '';
$price         = $_POST['price'] ?? 0;

$date          = $_POST['booking_date'] ?? '';
$time          = $_POST['booking_time'] ?? '';
$pax           = (int)($_POST['pax'] ?? 1);

$payment       = $_POST['payment_method'] ?? 'Cash';
$notes         = $_POST['notes'] ?? '';

$therapist     = $_POST['therapist'] ?? '';
$addons        = $_POST['addons'] ?? '';

$room_type     = $_POST['room_type'] ?? '';
$beds          = (int)($_POST['beds'] ?? 1);

/* ================= SAFETY FIX ================= */
if ($room_type === "Couple Room") {
    $beds = 2;
    $pax = 2;
}

/* ================= INSERT BOOKING ================= */
$stmt = $conn->prepare("
INSERT INTO bookings (
    user_id,
    service_id,
    service,
    duration,
    price,
    customer_name,
    phone,
    booking_date,
    booking_time,
    pax,
    payment_method,
    notes,
    addons,
    therapist_id,
    room_type,
    beds,
    status,
    created_by
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$status = "Confirmed";

$stmt->bind_param(
    "iissdssssisssssiss",
    $admin_id,
    $service_id,
    $service,
    $duration,
    $price,
    $customer_name,
    $phone,
    $date,
    $time,
    $pax,
    $payment,
    $notes,
    $addons,
    $therapist,
    $room_type,
    $beds,
    $status,
    $admin_id
);

if ($stmt->execute()) {

    $booking_id = $stmt->insert_id;

    header("Location: bookings.php?success=1&id=".$booking_id);
    exit;

} else {
    echo "ERROR: " . $stmt->error;
}
?>