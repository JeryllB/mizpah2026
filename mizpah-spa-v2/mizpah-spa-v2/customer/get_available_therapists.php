<?php

include '../includes/db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$duration = $_GET['duration'] ?? '1 Hour';

if (empty($date) || empty($time)) {
    echo json_encode([]);
    exit;
}


/*
   CONVERT DURATION TO MINUTES
*/

function getMinutes($duration) {

    $duration = strtolower(trim($duration));

    if (
        strpos($duration, '2 hour') !== false ||
        strpos($duration, '2 hr') !== false ||
        $duration === '2'
    ) {
        return 120;
    }

    if (
        strpos($duration, '1.5') !== false ||
        strpos($duration, '90') !== false ||
        strpos($duration, '1 hour 30') !== false
    ) {
        return 90;
    }

    return 60;
}

$durationMinutes = getMinutes($duration);


/*
   SELECTED BOOKING TIME
*/

$selectedStart = strtotime($date . ' ' . $time);
$selectedEnd = $selectedStart + ($durationMinutes * 60);


/*
   GET ALL ACTIVE THERAPISTS
*/

$therapists = [];

$result = mysqli_query(
    $conn,
    "SELECT id, name
     FROM therapists
     ORDER BY name ASC"
);

if (!$result) {
    echo json_encode([]);
    exit;
}


/*
   CHECK EACH THERAPIST'S BOOKINGS
*/

while ($therapist = mysqli_fetch_assoc($result)) {

    $therapistId = (int)$therapist['id'];
    $therapistName = $therapist['name'];

    $available = true;

    /*
       Check both:
       1. bookings.therapist_id
       2. booking_therapists.therapist_id

       This allows checking therapist assignments made
       from admin/customer booking records.
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT DISTINCT
            b.booking_time,
            b.duration,
            b.status
         FROM bookings b
         LEFT JOIN booking_therapists bt
            ON bt.booking_id = b.id
         WHERE b.booking_date = ?
         AND (
            b.therapist_id = ?
            OR bt.therapist_id = ?
         )"
    );

    if (!$stmt) {
        continue;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $date,
        $therapistId,
        $therapistId
    );

    mysqli_stmt_execute($stmt);

    $bookingResult = mysqli_stmt_get_result($stmt);

    while ($booking = mysqli_fetch_assoc($bookingResult)) {

        $status = strtolower(trim($booking['status'] ?? ''));

        /*
           Completed and Cancelled bookings do not block
           the therapist's schedule.
        */

        if (
            $status === 'completed' ||
            $status === 'cancelled'
        ) {
            continue;
        }

        $bookingStart = strtotime(
            $date . ' ' . $booking['booking_time']
        );

        $bookingMinutes = getMinutes(
            $booking['duration'] ?? '1 Hour'
        );

        $bookingEnd = $bookingStart + ($bookingMinutes * 60);

        /*
           OVERLAP CHECK
        */

        if (
            $selectedStart < $bookingEnd &&
            $selectedEnd > $bookingStart
        ) {

            $available = false;
            break;

        }

    }

    mysqli_stmt_close($stmt);

    $therapists[] = [
        'id' => $therapistId,
        'name' => $therapistName,
        'available' => $available
    ];

}

echo json_encode($therapists);

?>