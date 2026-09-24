<?php

session_start();
include __DIR__ . '/../includes/db.php';

/* =========================================================
   ADMIN AUTH
   ========================================================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = (int)$_SESSION['user_id'];


/* =========================================================
   GET FORM DATA
   ========================================================= */
$customer_name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$service_id = (int)($_POST['service_id'] ?? 0);
$service = trim($_POST['service'] ?? '');
$duration = trim($_POST['duration'] ?? '');
$price = (float)($_POST['price'] ?? 0);

$date = trim($_POST['booking_date'] ?? '');
$time = trim($_POST['booking_time'] ?? '');

$pax = (int)($_POST['pax'] ?? 1);

$payment = trim($_POST['payment_method'] ?? 'Cash');
$notes = trim($_POST['notes'] ?? '');

$therapist_id = (int)($_POST['therapist'] ?? 0);

$addons = trim($_POST['addons'] ?? '');
$room_type = trim($_POST['room_type'] ?? '');


/* =========================================================
   ROOM SAFETY
   ========================================================= */
if ($room_type === "Couple Room") {
    $pax = 2;
}

if ($pax < 1) {
    $pax = 1;
}

if ($pax > 4) {
    $pax = 4;
}


/* =========================================================
   BASIC VALIDATION
   ========================================================= */
if (
    $customer_name === '' ||
    $phone === '' ||
    $service_id <= 0 ||
    $service === '' ||
    $duration === '' ||
    $price <= 0 ||
    $date === '' ||
    $time === '' ||
    $room_type === ''
) {
    die("ERROR: Please complete all required booking information.");
}


/* =========================================================
   INSERT BOOKING
   ========================================================= */

$status = "Confirmed";
$created_by = "admin";

$stmt = $conn->prepare("
    INSERT INTO bookings (
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
        status,
        created_by
    )
    VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
");

if (!$stmt) {
    die("PREPARE ERROR: " . $conn->error);
}

$stmt->bind_param(
    "issdssssisssisss",
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
    $therapist_id,
    $room_type,
    $status,
    $created_by
);


/* =========================================================
   SAVE BOOKING
   ========================================================= */

if (!$stmt->execute()) {

    die(
        "BOOKING ERROR: " .
        $stmt->error
    );
}

$booking_id = $stmt->insert_id;

$stmt->close();


/* =========================================================
   TEMPORARY THERAPIST TEST
   =========================================================

   IMPORTANT:

   For this test, we are NOT inserting anything into
   booking_therapists.

   The selected therapist is ONLY being saved in:

       bookings.therapist_id

   This will help us determine whether another part of
   the system automatically creates a booking_therapists
   record.

   DO NOT add INSERT INTO booking_therapists here yet.

   ========================================================= */


/* =========================================================
   SUCCESS
   ========================================================= */

header(
    "Location: bookings.php?success=1&id=" .
    $booking_id
);

exit;

?>