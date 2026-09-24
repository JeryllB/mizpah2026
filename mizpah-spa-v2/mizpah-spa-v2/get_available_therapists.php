<?php

include __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$duration = $_GET['duration'] ?? '1 Hour';

if (empty($date) || empty($time)) {
    echo json_encode([]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Convert duration to minutes
|--------------------------------------------------------------------------
*/
function durationToMinutes($duration)
{
    $duration = strtolower(trim($duration));

    if (
        strpos($duration, '2 hour') !== false ||
        strpos($duration, '2hr') !== false ||
        strpos($duration, '2 hrs') !== false ||
        strpos($duration, '2hrs') !== false
    ) {
        return 120;
    }

    if (
        strpos($duration, '1.5') !== false ||
        strpos($duration, '1 1/2') !== false ||
        strpos($duration, '90') !== false
    ) {
        return 90;
    }

    return 60;
}

/*
|--------------------------------------------------------------------------
| Selected booking time range
|--------------------------------------------------------------------------
*/
$selectedStart = strtotime($date . ' ' . $time);
$selectedMinutes = durationToMinutes($duration);
$selectedEnd = $selectedStart + ($selectedMinutes * 60);

$therapists = [];

/*
|--------------------------------------------------------------------------
| Get all therapists
|--------------------------------------------------------------------------
*/
$therapistQuery = mysqli_query(
    $conn,
    "
    SELECT id, name
    FROM therapists
    ORDER BY name ASC
    "
);

if (!$therapistQuery) {
    echo json_encode([
        'error' => mysqli_error($conn)
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Check every therapist
|--------------------------------------------------------------------------
*/
while ($therapist = mysqli_fetch_assoc($therapistQuery)) {

    $therapistId = (int) $therapist['id'];
    $therapistName = $therapist['name'];

    $isAvailable = true;

    /*
    |--------------------------------------------------------------------------
    | Check therapist bookings from:
    | 1. bookings.therapist_id
    | 2. booking_therapists.therapist_id
    |--------------------------------------------------------------------------
    */
    $bookingQuery = mysqli_prepare(
        $conn,
        "
        SELECT DISTINCT
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
        )
        "
    );

    if ($bookingQuery) {

        mysqli_stmt_bind_param(
            $bookingQuery,
            "sii",
            $date,
            $therapistId,
            $therapistId
        );

        mysqli_stmt_execute($bookingQuery);

        $bookingResult = mysqli_stmt_get_result($bookingQuery);

        if ($bookingResult) {

            while ($booking = mysqli_fetch_assoc($bookingResult)) {

                $status = strtolower(trim($booking['status']));

                /*
                |--------------------------------------------------------------------------
                | Do not block therapist for completed/cancelled bookings
                |--------------------------------------------------------------------------
                */
                if (
                    $status === 'completed' ||
                    $status === 'cancelled'
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Existing booking time range
                |--------------------------------------------------------------------------
                */
                $bookingStart = strtotime(
                    $date . ' ' . $booking['booking_time']
                );

                $bookingMinutes = durationToMinutes(
                    $booking['duration']
                );

                $bookingEnd = $bookingStart + ($bookingMinutes * 60);

                /*
                |--------------------------------------------------------------------------
                | Check if selected schedule overlaps existing booking
                |--------------------------------------------------------------------------
                */
                if (
                    $selectedStart < $bookingEnd &&
                    $selectedEnd > $bookingStart
                ) {
                    $isAvailable = false;
                    break;
                }
            }
        }

        mysqli_stmt_close($bookingQuery);
    }

    $therapists[] = [
        'id' => $therapistId,
        'name' => $therapistName,
        'available' => $isAvailable
    ];
}

/*
|--------------------------------------------------------------------------
| Return all therapists
|--------------------------------------------------------------------------
*/
echo json_encode($therapists);
exit;

?>