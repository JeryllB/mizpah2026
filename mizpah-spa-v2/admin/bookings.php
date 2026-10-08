<?php

session_start();
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}


/* =========================================================
   DURATION TO MINUTES
   ========================================================= */

function getMinutes($duration)
{
    $duration = strtolower(trim($duration));

    if (in_array($duration, [
        '1.5hr', '1.5 hr', '1.5hrs', '1.5 hrs'
    ])) {
        return 90;
    }

    if (in_array($duration, [
        '2hr', '2 hr', '2hrs', '2 hrs'
    ])) {
        return 120;
    }

    return 60;
}


/* =========================================================
   THERAPIST AVAILABILITY
   ========================================================= */

function isTherapistAvailable(
    $conn,
    $therapist_id,
    $date,
    $time,
    $duration,
    $current_booking_id = 0
) {
    $therapist_id = (int)$therapist_id;
    $current_booking_id = (int)$current_booking_id;

    if ($therapist_id <= 0) {
        return true;
    }

    $newStart = strtotime("$date $time");
    $newEnd = $newStart + (getMinutes($duration) * 60);

    $stmt = $conn->prepare("
        SELECT booking_date, booking_time, duration
        FROM bookings
        WHERE therapist_id = ?
        AND id != ?
        AND status NOT IN ('Completed', 'Cancelled')
    ");

    if (!$stmt) {
        return true;
    }

    $stmt->bind_param(
        "ii",
        $therapist_id,
        $current_booking_id
    );

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $existingStart = strtotime(
            $row['booking_date'] . ' ' . $row['booking_time']
        );

        $existingEnd =
            $existingStart +
            (getMinutes($row['duration']) * 60);

        if (
            $newStart < $existingEnd &&
            $newEnd > $existingStart
        ) {
            $stmt->close();
            return false;
        }
    }

    $stmt->close();
    return true;
}


/* =========================================================
   AJAX - CHANGE THERAPIST
   ========================================================= */

if (isset($_POST['ajax_change_therapist'])) {

    $booking_id =
        (int)($_POST['booking_id'] ?? 0);

    $therapist_id =
        (int)($_POST['therapist_id'] ?? 0);

    if ($booking_id <= 0) {
        echo 'INVALID';
        exit;
    }


    /* NO PREFERENCE */

    if ($therapist_id === 0) {

        $stmt = $conn->prepare("
            UPDATE bookings
            SET therapist_id = 0
            WHERE id = ?
        ");

        if (!$stmt) {
            echo 'ERROR';
            exit;
        }

        $stmt->bind_param(
            "i",
            $booking_id
        );

        if (!$stmt->execute()) {

            $stmt->close();

            echo 'ERROR';
            exit;
        }

        $stmt->close();


        /* Clean old assignment rows */

        $delete = $conn->prepare("
            DELETE FROM booking_therapists
            WHERE booking_id = ?
        ");

        if ($delete) {

            $delete->bind_param(
                "i",
                $booking_id
            );

            $delete->execute();
            $delete->close();
        }

        echo 'OK';
        exit;
    }


    /* CHECK THERAPIST */

    $check = $conn->prepare("
        SELECT id
        FROM therapists
        WHERE id = ?
        AND status = 'Active'
        LIMIT 1
    ");

    if (!$check) {
        echo 'ERROR';
        exit;
    }

    $check->bind_param(
        "i",
        $therapist_id
    );

    $check->execute();

    $checkResult =
        $check->get_result();

    if ($checkResult->num_rows === 0) {

        $check->close();

        echo 'INVALID';
        exit;
    }

    $check->close();


    /* GET BOOKING INFO */

    $bookingStmt = $conn->prepare("
        SELECT
            booking_date,
            booking_time,
            duration
        FROM bookings
        WHERE id = ?
        LIMIT 1
    ");

    if (!$bookingStmt) {
        echo 'ERROR';
        exit;
    }

    $bookingStmt->bind_param(
        "i",
        $booking_id
    );

    $bookingStmt->execute();

    $bookingResult =
        $bookingStmt->get_result();

    if ($bookingResult->num_rows === 0) {

        $bookingStmt->close();

        echo 'INVALID';
        exit;
    }

    $booking =
        $bookingResult->fetch_assoc();

    $bookingStmt->close();


    /* CHECK AVAILABILITY */

    if (
        !isTherapistAvailable(
            $conn,
            $therapist_id,
            $booking['booking_date'],
            $booking['booking_time'],
            $booking['duration'],
            $booking_id
        )
    ) {
        echo 'BUSY';
        exit;
    }


    /* UPDATE THERAPIST */

    $update = $conn->prepare("
        UPDATE bookings
        SET therapist_id = ?
        WHERE id = ?
    ");

    if (!$update) {
        echo 'ERROR';
        exit;
    }

    $update->bind_param(
        "ii",
        $therapist_id,
        $booking_id
    );

    if (!$update->execute()) {

        $update->close();

        echo 'ERROR';
        exit;
    }

    $update->close();


    /* Clean old assignment rows */

    $delete = $conn->prepare("
        DELETE FROM booking_therapists
        WHERE booking_id = ?
    ");

    if ($delete) {

        $delete->bind_param(
            "i",
            $booking_id
        );

        $delete->execute();
        $delete->close();
    }

    echo 'OK';
    exit;
}


/* =========================================================
   UPDATE STATUS
   ========================================================= */

if (isset($_POST['update_status'])) {

    $booking_id =
        (int)($_POST['booking_id'] ?? 0);

    $status =
        trim($_POST['status'] ?? '');

    $allowedStatuses = [
        'Confirmed',
        'Completed',
        'Cancelled'
    ];

    if (
        $booking_id > 0 &&
        in_array(
            $status,
            $allowedStatuses,
            true
        )
    ) {

        $stmt = $conn->prepare("
            UPDATE bookings
            SET status = ?
            WHERE id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "si",
                $status,
                $booking_id
            );

            $stmt->execute();
            $stmt->close();
        }
    }


    $returnFilter =
        strtolower(
            trim(
                $_POST['return_filter']
                ?? 'new'
            )
        );

    $allowedFilters = [
        'new',
        'confirmed',
        'completed',
        'cancelled',
        'all'
    ];

    if (
        !in_array(
            $returnFilter,
            $allowedFilters,
            true
        )
    ) {
        $returnFilter = 'new';
    }

    header(
        "Location: bookings.php?filter=" .
        urlencode($returnFilter)
    );

    exit;
}


/* =========================================================
   FILTER
   DEFAULT = NEW BOOKINGS
   ========================================================= */

$filter =
    strtolower(
        trim(
            $_GET['filter']
            ?? 'new'
        )
    );

$allowedFilters = [
    'new',
    'confirmed',
    'completed',
    'cancelled',
    'all'
];

if (
    !in_array(
        $filter,
        $allowedFilters,
        true
    )
) {
    $filter = 'new';
}


/* =========================================================
   ACTIVE THERAPISTS
   ========================================================= */

$therapists = [];

$therapistQuery = $conn->query("
    SELECT id, name
    FROM therapists
    WHERE status = 'Active'
    ORDER BY name ASC
");

if ($therapistQuery) {

    while (
        $row = $therapistQuery->fetch_assoc()
    ) {
        $therapists[] = $row;
    }
}


/* =========================================================
   COUNTS
   ========================================================= */

$newBookings = 0;
$confirmedBookings = 0;
$completedBookings = 0;
$cancelledBookings = 0;
$totalBookings = 0;


/* NEW = CREATED WITHIN LAST 24 HOURS */

$newQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
");

if ($newQuery) {

    $newRow =
        $newQuery->fetch_assoc();

    $newBookings =
        (int)($newRow['total'] ?? 0);
}


/* STATUS COUNTS */

$countQuery = $conn->query("
    SELECT

        COUNT(*) AS total,

        SUM(
            CASE
                WHEN status = 'Confirmed'
                THEN 1
                ELSE 0
            END
        ) AS confirmed,

        SUM(
            CASE
                WHEN status = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed,

        SUM(
            CASE
                WHEN status = 'Cancelled'
                THEN 1
                ELSE 0
            END
        ) AS cancelled

    FROM bookings
");

if ($countQuery) {

    $counts =
        $countQuery->fetch_assoc();

    $totalBookings =
        (int)($counts['total'] ?? 0);

    $confirmedBookings =
        (int)($counts['confirmed'] ?? 0);

    $completedBookings =
        (int)($counts['completed'] ?? 0);

    $cancelledBookings =
        (int)($counts['cancelled'] ?? 0);
}


/* =========================================================
   FILTER QUERY
   ========================================================= */

$where = "";
$orderBy = "
    b.booking_date DESC,
    b.booking_time DESC,
    b.id DESC
";


/* NEW BOOKINGS */

if ($filter === 'new') {

    $where = "
        WHERE b.created_at >=
        DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ";

    $orderBy = "
        b.created_at DESC,
        b.id DESC
    ";
}


/* CONFIRMED */

elseif ($filter === 'confirmed') {

    $where = "
        WHERE b.status = 'Confirmed'
    ";

    $orderBy = "
        b.booking_date ASC,
        b.booking_time ASC,
        b.id DESC
    ";
}


/* COMPLETED */

elseif ($filter === 'completed') {

    $where = "
        WHERE b.status = 'Completed'
    ";

    $orderBy = "
        b.booking_date DESC,
        b.booking_time DESC,
        b.id DESC
    ";
}


/* CANCELLED */

elseif ($filter === 'cancelled') {

    $where = "
        WHERE b.status = 'Cancelled'
    ";

    $orderBy = "
        b.booking_date DESC,
        b.booking_time DESC,
        b.id DESC
    ";
}


/* ALL */

elseif ($filter === 'all') {

    $where = "";

    $orderBy = "
        b.created_at DESC,
        b.id DESC
    ";
}


/* =========================================================
   GET BOOKINGS
   ========================================================= */

$bookings = $conn->query("
    SELECT
        b.*,
        t.name AS therapist_name

    FROM bookings b

    LEFT JOIN therapists t
        ON t.id = b.therapist_id

    $where

    ORDER BY $orderBy
");


/* =========================================================
   FILTER TITLE
   ========================================================= */

$filterTitle = 'New Bookings';
$filterDescription =
    'Bookings received within the last 24 hours.';

if ($filter === 'confirmed') {

    $filterTitle =
        'Confirmed Bookings';

    $filterDescription =
        'Upcoming confirmed appointments.';
}

elseif ($filter === 'completed') {

    $filterTitle =
        'Completed Bookings';

    $filterDescription =
        'Appointments that have already been completed.';
}

elseif ($filter === 'cancelled') {

    $filterTitle =
        'Cancelled Bookings';

    $filterDescription =
        'Appointments that were cancelled.';
}

elseif ($filter === 'all') {

    $filterTitle =
        'All Bookings';

    $filterDescription =
        'Complete booking history.';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Bookings | Mizpah Wellness Spa
</title>

<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet"
>


<style>

/* =========================================================
   BASE
   ========================================================= */

*{
    box-sizing:border-box;
}

body{
    margin:0;
    background:#0b0b0b;
    color:#eee;
    font-family:'Poppins',sans-serif;
}

.main-content{
    margin-left:250px;
    min-height:100vh;
    padding:28px 30px 55px;
}


/* =========================================================
   HEADER
   ========================================================= */

.page-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
}

.page-eyebrow{
    color:#84745f;
    font-size:11px;
    font-weight:500;
    letter-spacing:1.5px;
    text-transform:uppercase;
}

.page-header h1{
    margin:2px 0 0;
    color:#d5c29d;
    font-family:'Playfair Display',serif;
    font-size:34px;
    font-weight:600;
}

.page-header p{
    margin:5px 0 0;
    color:#888;
    font-size:12px;
}

.walkin-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:11px 18px;
    background:#d5c29d;
    color:#111;
    border-radius:7px;
    text-decoration:none;
    font-size:12px;
    font-weight:600;
}

.walkin-button:hover{
    background:#ead9b6;
}


/* =========================================================
   SUCCESS
   ========================================================= */

.success-message{
    margin-bottom:17px;
    padding:12px 15px;
    background:rgba(100,130,90,.10);
    border:1px solid rgba(120,150,105,.22);
    border-radius:7px;
    color:#b7c8ad;
    font-size:12px;
}


/* =========================================================
   FILTER CARDS
   ========================================================= */

.stats{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:11px;
    margin-bottom:23px;
}

.stat-card{
    display:block;
    position:relative;
    padding:15px 17px;
    background:#131313;
    border:1px solid rgba(255,255,255,.07);
    border-radius:10px;
    color:inherit;
    text-decoration:none;
    transition:.2s;
    cursor:pointer;
}

.stat-card:hover{
    background:#191919;
    border-color:rgba(213,194,157,.30);
    transform:translateY(-2px);
}

.stat-card.active{
    background:
        linear-gradient(
            145deg,
            rgba(83,63,33,.48),
            #15130f
        );

    border-color:
        rgba(213,194,157,.50);
}

.stat-card.active::after{
    content:"";
    position:absolute;
    left:16px;
    right:16px;
    bottom:-1px;
    height:2px;
    background:#d5c29d;
}

.stat-label{
    color:#999;
    font-size:10px;
    font-weight:500;
    letter-spacing:.6px;
    text-transform:uppercase;
}

.stat-number{
    margin-top:3px;
    color:#d5c29d;
    font-family:'Playfair Display',serif;
    font-size:27px;
    line-height:1.1;
}

.stat-card.active .stat-label{
    color:#c8b78f;
}


/* NEW CARD */

.new-card .stat-number{
    display:flex;
    align-items:center;
    gap:7px;
}

.new-dot{
    width:7px;
    height:7px;
    background:#d5c29d;
    border-radius:50%;
}


/* =========================================================
   SECTION
   ========================================================= */

.section-header{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:15px;
    margin-bottom:11px;
}

.section-header h2{
    margin:0;
    color:#d5c29d;
    font-family:'Playfair Display',serif;
    font-size:22px;
    font-weight:600;
}

.section-header p{
    margin:3px 0 0;
    color:#777;
    font-size:11px;
}

.filter-indicator{
    color:#9a8b70;
    font-size:11px;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-container{
    width:100%;
    background:#111;
    border:1px solid rgba(255,255,255,.07);
    border-radius:10px;
    overflow:hidden;
}

.table-scroll{
    width:100%;
    overflow-x:auto;
}

.booking-table{
    width:100%;
    min-width:1200px;
    border-collapse:collapse;
}

.booking-table thead{
    background:#181818;
}

.booking-table th{
    padding:13px 14px;
    border-bottom:1px solid rgba(213,194,157,.13);
    color:#a18f70;
    font-size:11px;
    font-weight:600;
    letter-spacing:.5px;
    text-align:left;
    text-transform:uppercase;
    white-space:nowrap;
}

.booking-table td{
    padding:13px 14px;
    border-bottom:1px solid rgba(255,255,255,.055);
    color:#aaa;
    font-size:12px;
    line-height:1.5;
    vertical-align:middle;
}

.booking-table tbody tr:nth-child(even){
    background:#121212;
}

.booking-table tbody tr:hover{
    background:#171717;
}

.booking-table tbody tr:last-child td{
    border-bottom:none;
}


/* =========================================================
   CUSTOMER
   ========================================================= */

.booking-top{
    display:flex;
    align-items:center;
    gap:6px;
    margin-bottom:2px;
}

.booking-number{
    color:#807258;
    font-size:10px;
    font-weight:600;
}

.new-badge{
    display:inline-block;
    padding:2px 6px;
    background:rgba(213,194,157,.12);
    border:1px solid rgba(213,194,157,.25);
    border-radius:10px;
    color:#d5c29d;
    font-size:8px;
    font-weight:600;
    letter-spacing:.5px;
}

.customer-name{
    color:#eee;
    font-size:13px;
    font-weight:500;
}

.customer-phone{
    margin-top:1px;
    color:#888;
    font-size:11px;
}

.source-badge{
    display:inline-block;
    margin-top:4px;
    padding:3px 7px;
    background:#191919;
    border:1px solid #292929;
    border-radius:12px;
    color:#888;
    font-size:9px;
    text-transform:capitalize;
}

.created-time{
    margin-top:5px;
    color:#6f6657;
    font-size:9px;
}


/* =========================================================
   SERVICE
   ========================================================= */

.service-name{
    color:#e0e0e0;
    font-size:13px;
    font-weight:500;
}

.service-info{
    margin-top:2px;
    color:#888;
    font-size:11px;
}

.service-price{
    color:#d5c29d;
    font-size:12px;
    font-weight:500;
}

.addons{
    max-width:190px;
    margin-top:4px;
    color:#9b8b70;
    font-size:10px;
    line-height:1.4;
}


/* =========================================================
   SCHEDULE
   ========================================================= */

.schedule-date{
    color:#ddd;
    font-size:12px;
    font-weight:500;
}

.schedule-time{
    margin-top:1px;
    color:#888;
    font-size:11px;
}


/* =========================================================
   ROOM
   ========================================================= */

.room-name{
    color:#ddd;
    font-size:12px;
}

.room-pax{
    margin-top:2px;
    color:#888;
    font-size:11px;
}


/* =========================================================
   CONTROLS
   ========================================================= */

.control-label{
    display:block;
    margin-bottom:5px;
    color:#9b8a6c;
    font-size:9px;
    font-weight:600;
    letter-spacing:.5px;
    text-transform:uppercase;
}

.admin-select{
    width:100%;
    min-width:145px;
    padding:8px 9px;
    background:#0c0c0c;
    color:#ddd;
    border:1px solid #343434;
    border-radius:6px;
    outline:none;
    font-family:'Poppins',sans-serif;
    font-size:11px;
}

.admin-select:hover{
    border-color:#574a36;
}

.admin-select:focus{
    border-color:rgba(213,194,157,.60);
}

.admin-select:disabled{
    opacity:.55;
}


/* =========================================================
   PAYMENT / NOTES
   ========================================================= */

.payment{
    color:#ddd;
    font-size:12px;
}

.notes{
    max-width:165px;
    color:#9b8b70;
    font-size:11px;
    line-height:1.45;
}


/* =========================================================
   EMPTY
   ========================================================= */

.empty{
    padding:45px 20px !important;
    color:#777 !important;
    font-size:12px !important;
    text-align:center;
}


/* =========================================================
   SCROLLBAR
   ========================================================= */

.table-scroll::-webkit-scrollbar{
    height:8px;
}

.table-scroll::-webkit-scrollbar-track{
    background:#0c0c0c;
}

.table-scroll::-webkit-scrollbar-thumb{
    background:#413827;
    border-radius:20px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:1200px){

    .stats{
        grid-template-columns:
            repeat(3,1fr);
    }
}

@media(max-width:1100px){

    .main-content{
        margin-left:0;
        padding:24px 18px 50px;
    }
}

@media(max-width:700px){

    .page-header{
        align-items:flex-start;
        flex-direction:column;
    }

    .walkin-button{
        width:100%;
    }

    .stats{
        grid-template-columns:
            repeat(2,1fr);
    }

    .section-header{
        align-items:flex-start;
        flex-direction:column;
    }
}

</style>

</head>


<body>


<?php
include __DIR__ . '/includes/sidebar.php';
?>


<main class="main-content">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="page-header">

        <div>

            <div class="page-eyebrow">
                Appointment Management
            </div>

            <h1>
                Bookings
            </h1>

            <p>
                View new reservations, manage therapists
                and update appointment status.
            </p>

        </div>


        <a
            href="walkin-booking.php"
            class="walkin-button"
        >
            + New Walk-in Booking
        </a>

    </div>


    <!-- =====================================================
         SUCCESS
         ===================================================== -->

    <?php if (
        isset($_GET['success']) &&
        $_GET['success'] == '1'
    ): ?>

        <div class="success-message">

            Booking successfully created and
            <strong>Confirmed</strong>.

        </div>

    <?php endif; ?>


    <!-- =====================================================
         FILTER CARDS
         ===================================================== -->

    <div class="stats">


        <!-- NEW -->

        <a
            href="bookings.php?filter=new"
            class="
                stat-card
                new-card
                <?= $filter === 'new'
                    ? 'active'
                    : ''
                ?>
            "
        >

            <div class="stat-label">
                New Bookings
            </div>

            <div class="stat-number">

                <span class="new-dot"></span>

                <?= $newBookings ?>

            </div>

        </a>


        <!-- CONFIRMED -->

        <a
            href="bookings.php?filter=confirmed"
            class="
                stat-card
                <?= $filter === 'confirmed'
                    ? 'active'
                    : ''
                ?>
            "
        >

            <div class="stat-label">
                Confirmed
            </div>

            <div class="stat-number">
                <?= $confirmedBookings ?>
            </div>

        </a>


        <!-- COMPLETED -->

        <a
            href="bookings.php?filter=completed"
            class="
                stat-card
                <?= $filter === 'completed'
                    ? 'active'
                    : ''
                ?>
            "
        >

            <div class="stat-label">
                Completed
            </div>

            <div class="stat-number">
                <?= $completedBookings ?>
            </div>

        </a>


        <!-- CANCELLED -->

        <a
            href="bookings.php?filter=cancelled"
            class="
                stat-card
                <?= $filter === 'cancelled'
                    ? 'active'
                    : ''
                ?>
            "
        >

            <div class="stat-label">
                Cancelled
            </div>

            <div class="stat-number">
                <?= $cancelledBookings ?>
            </div>

        </a>


        <!-- ALL -->

        <a
            href="bookings.php?filter=all"
            class="
                stat-card
                <?= $filter === 'all'
                    ? 'active'
                    : ''
                ?>
            "
        >

            <div class="stat-label">
                All Bookings
            </div>

            <div class="stat-number">
                <?= $totalBookings ?>
            </div>

        </a>


    </div>


    <!-- =====================================================
         SECTION
         ===================================================== -->

    <div class="section-header">

        <div>

            <h2>
                <?= htmlspecialchars(
                    $filterTitle
                ) ?>
            </h2>

            <p>
                <?= htmlspecialchars(
                    $filterDescription
                ) ?>
            </p>

        </div>


        <div class="filter-indicator">

            <?php if ($filter === 'new'): ?>

                Latest received first

            <?php elseif ($filter === 'confirmed'): ?>

                Upcoming schedule first

            <?php elseif ($filter === 'all'): ?>

                Latest booking first

            <?php else: ?>

                <?= ucfirst(
                    htmlspecialchars($filter)
                ) ?>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         TABLE
         ===================================================== -->

    <div class="table-container">

        <div class="table-scroll">

            <table class="booking-table">


                <thead>

                    <tr>

                        <th>Customer</th>

                        <th>Service</th>

                        <th>Schedule</th>

                        <th>Room / Pax</th>

                        <th>Therapist</th>

                        <th>Payment</th>

                        <th>Notes</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (
                    $bookings &&
                    $bookings->num_rows > 0
                ): ?>


                    <?php while (
                        $booking =
                            $bookings->fetch_assoc()
                    ): ?>


                        <?php

                        $bookingId =
                            (int)$booking['id'];

                        $therapistId =
                            (int)(
                                $booking['therapist_id']
                                ?? 0
                            );

                        $currentStatus =
                            trim(
                                $booking['status']
                                ?? 'Confirmed'
                            );

                        $pax =
                            max(
                                1,
                                (int)(
                                    $booking['pax']
                                    ?? 1
                                )
                            );


                        /* Is this booking new? */

                        $isNew = false;

                        if (
                            !empty(
                                $booking['created_at']
                            )
                        ) {

                            $createdTimestamp =
                                strtotime(
                                    $booking['created_at']
                                );

                            $isNew =
                                $createdTimestamp >=
                                strtotime('-24 hours');
                        }

                        ?>


                        <tr>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="booking-top">

                                    <span class="booking-number">

                                        Booking #<?= $bookingId ?>

                                    </span>


                                    <?php if ($isNew): ?>

                                        <span class="new-badge">
                                            NEW
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $booking['customer_name']
                                        ?? ''
                                    ) ?>

                                </div>


                                <div class="customer-phone">

                                    <?= htmlspecialchars(
                                        $booking['phone']
                                        ?? ''
                                    ) ?>

                                </div>


                                <div class="source-badge">

                                    <?= htmlspecialchars(
                                        $booking['created_by']
                                        ?? 'guest'
                                    ) ?>

                                </div>


                                <?php if (
                                    !empty(
                                        $booking['created_at']
                                    )
                                ): ?>

                                    <div class="created-time">

                                        Booked:
                                        <?= date(
                                            'M d, g:i A',
                                            strtotime(
                                                $booking['created_at']
                                            )
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- SERVICE -->

                            <td>

                                <div class="service-name">

                                    <?= htmlspecialchars(
                                        $booking['service']
                                        ?? ''
                                    ) ?>

                                </div>


                                <div class="service-info">

                                    <?= htmlspecialchars(
                                        $booking['duration']
                                        ?? ''
                                    ) ?>

                                    &nbsp;·&nbsp;

                                    <span class="service-price">

                                        ₱<?= number_format(
                                            (float)(
                                                $booking['price']
                                                ?? 0
                                            ),
                                            2
                                        ) ?>

                                    </span>

                                </div>


                                <?php if (
                                    !empty(
                                        $booking['addons']
                                    )
                                ): ?>

                                    <div class="addons">

                                        Add-ons:
                                        <?= htmlspecialchars(
                                            $booking['addons']
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- SCHEDULE -->

                            <td>

                                <div class="schedule-date">

                                    <?php

                                    if (
                                        !empty(
                                            $booking['booking_date']
                                        )
                                    ) {

                                        echo date(
                                            'M d, Y',
                                            strtotime(
                                                $booking['booking_date']
                                            )
                                        );

                                    } else {

                                        echo '—';
                                    }

                                    ?>

                                </div>


                                <div class="schedule-time">

                                    <?php

                                    if (
                                        !empty(
                                            $booking['booking_time']
                                        )
                                    ) {

                                        echo date(
                                            'g:i A',
                                            strtotime(
                                                $booking['booking_time']
                                            )
                                        );

                                    } else {

                                        echo '—';
                                    }

                                    ?>

                                </div>

                            </td>


                            <!-- ROOM / PAX -->

                            <td>

                                <div class="room-name">

                                    <?= htmlspecialchars(
                                        !empty(
                                            $booking['room_type']
                                        )
                                            ? $booking['room_type']
                                            : 'N/A'
                                    ) ?>

                                </div>


                                <div class="room-pax">

                                    <?= $pax ?>

                                    <?= $pax === 1
                                        ? 'guest'
                                        : 'guests'
                                    ?>

                                </div>

                            </td>


                            <!-- THERAPIST -->

                            <td>

                                <span class="control-label">
                                    Assigned Therapist
                                </span>


                                <select
                                    class="admin-select"

                                    data-old-value="<?= $therapistId ?>"

                                    onchange="
                                        changeTherapist(
                                            this,
                                            <?= $bookingId ?>
                                        )
                                    "
                                >

                                    <option
                                        value="0"

                                        <?= $therapistId === 0
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        No Preference
                                    </option>


                                    <?php foreach (
                                        $therapists
                                        as $therapist
                                    ): ?>

                                        <?php

                                        $optionId =
                                            (int)$therapist['id'];

                                        ?>

                                        <option
                                            value="<?= $optionId ?>"

                                            <?= $therapistId === $optionId
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >

                                            <?= htmlspecialchars(
                                                $therapist['name']
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </td>


                            <!-- PAYMENT -->

                            <td>

                                <div class="payment">

                                    <?= htmlspecialchars(
                                        $booking['payment_method']
                                        ?? 'Cash'
                                    ) ?>

                                </div>

                            </td>


                            <!-- NOTES -->

                            <td>

                                <div class="notes">

                                    <?php if (
                                        !empty(
                                            $booking['notes']
                                        )
                                    ): ?>

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $booking['notes']
                                            )
                                        ) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span class="control-label">
                                    Booking Status
                                </span>


                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="update_status"
                                        value="1"
                                    >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= $bookingId ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="return_filter"
                                        value="<?= htmlspecialchars($filter) ?>"
                                    >


                                    <select
                                        name="status"
                                        class="admin-select"
                                        onchange="this.form.submit()"
                                    >

                                        <option
                                            value="Confirmed"

                                            <?= $currentStatus === 'Confirmed'
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            Confirmed
                                        </option>


                                        <option
                                            value="Completed"

                                            <?= $currentStatus === 'Completed'
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            Completed
                                        </option>


                                        <option
                                            value="Cancelled"

                                            <?= $currentStatus === 'Cancelled'
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            Cancelled
                                        </option>

                                    </select>

                                </form>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="8"
                            class="empty"
                        >

                            <?php if ($filter === 'new'): ?>

                                No new bookings within
                                the last 24 hours.

                            <?php else: ?>

                                No
                                <?= $filter === 'all'
                                    ? ''
                                    : htmlspecialchars($filter)
                                ?>
                                bookings found.

                            <?php endif; ?>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>

        </div>

    </div>


</main>


<script>

/* =========================================================
   CHANGE THERAPIST
   ========================================================= */

function changeTherapist(
    select,
    bookingId
) {

    const oldValue =
        select.dataset.oldValue;

    const therapistId =
        select.value;

    select.disabled = true;


    const data =
        new URLSearchParams();

    data.append(
        'ajax_change_therapist',
        '1'
    );

    data.append(
        'booking_id',
        bookingId
    );

    data.append(
        'therapist_id',
        therapistId
    );


    fetch(
        'bookings.php',
        {
            method:'POST',

            headers:{
                'Content-Type':
                    'application/x-www-form-urlencoded'
            },

            body:data.toString()
        }
    )

    .then(
        response =>
            response.text()
    )

    .then(
        result => {

            result =
                result.trim();


            if (result === 'OK') {

                window.location.reload();
                return;
            }


            if (result === 'BUSY') {

                alert(
                    'This therapist is already assigned to another active booking during this schedule.'
                );

                select.value =
                    oldValue;

                select.disabled =
                    false;

                return;
            }


            if (result === 'INVALID') {

                alert(
                    'Invalid booking or therapist.'
                );

                select.value =
                    oldValue;

                select.disabled =
                    false;

                return;
            }


            alert(
                'Unable to update therapist.'
            );

            select.value =
                oldValue;

            select.disabled =
                false;

        }
    )

    .catch(
        () => {

            alert(
                'Unable to update therapist.'
            );

            select.value =
                oldValue;

            select.disabled =
                false;

        }
    );

}

</script>


</body>
</html>