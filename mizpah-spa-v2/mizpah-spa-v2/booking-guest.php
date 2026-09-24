<?php

session_start();

include __DIR__ . '/includes/db.php';


/* =========================================================
   GUEST BOOKING
   - No login required
   - No registered customer user_id required
   ========================================================= */


/* =========================================================
   SUBMIT BOOKING
   ========================================================= */

if (isset($_POST['submit_booking'])) {

    $name = mysqli_real_escape_string(
        $conn,
        trim($_POST['customer_name'] ?? '')
    );

    $phone = mysqli_real_escape_string(
        $conn,
        trim($_POST['phone'] ?? '')
    );

    $service_id = (int)($_POST['service_id'] ?? 0);

    $service = mysqli_real_escape_string(
        $conn,
        $_POST['service'] ?? ''
    );

    $duration = mysqli_real_escape_string(
        $conn,
        $_POST['duration'] ?? ''
    );

    $price = (float)($_POST['price'] ?? 0);

    $date = mysqli_real_escape_string(
        $conn,
        $_POST['booking_date'] ?? ''
    );

    $time = mysqli_real_escape_string(
        $conn,
        $_POST['booking_time'] ?? ''
    );

    $pax = (int)($_POST['pax'] ?? 1);

    $payment = mysqli_real_escape_string(
        $conn,
        $_POST['payment_method'] ?? 'Cash'
    );

    $notes = mysqli_real_escape_string(
        $conn,
        $_POST['notes'] ?? ''
    );

    $therapist_id = (int)($_POST['therapist'] ?? 0);

    $addons = mysqli_real_escape_string(
        $conn,
        $_POST['addons'] ?? ''
    );

    $room_type = mysqli_real_escape_string(
        $conn,
        $_POST['room_type'] ?? ''
    );


    /* =====================================================
       COUPLE ROOM = 2 PAX
       ===================================================== */

    if ($room_type === 'Couple Room') {
        $pax = 2;
    }


    /* =====================================================
       BASIC VALIDATION
       ===================================================== */

    if (
        empty($name) ||
        empty($phone) ||
        $service_id <= 0 ||
        empty($service) ||
        empty($duration) ||
        $price <= 0 ||
        empty($date) ||
        empty($time) ||
        empty($room_type)
    ) {

        $booking_error =
            'Please complete all required booking information.';

    } else {


        /* =================================================
           INSERT GUEST BOOKING

           user_id is omitted completely so this guest
           booking does not require a customer account.

           created_by = guest
           ================================================= */

        $insert = mysqli_query($conn, "

            INSERT INTO bookings
            (
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

            VALUES
            (
                '$service_id',
                '$service',
                '$duration',
                '$price',
                '$name',
                '$phone',
                '$date',
                '$time',
                '$pax',
                '$payment',
                '$notes',
                '$addons',
                '$therapist_id',
                '$room_type',
                'Pending',
                'guest'
            )

        ");


        if ($insert) {

            $booking_id = mysqli_insert_id($conn);


            /* =============================================
               SAVE SPECIFIC THERAPIST
               ============================================= */

            if ($therapist_id > 0) {

                mysqli_query($conn, "

                    INSERT INTO booking_therapists
                    (
                        booking_id,
                        therapist_id,
                        assigned_by
                    )

                    VALUES
                    (
                        '$booking_id',
                        '$therapist_id',
                        'guest'
                    )

                ");

            }


            header(
                "Location: thankyou.php?id=" .
                $booking_id
            );

            exit;

        } else {

            $booking_error =
                'Booking Error: ' .
                mysqli_error($conn);

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Book an Appointment | Mizpah Wellness Spa</title>

<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins,sans-serif;
}

html{
    scroll-behavior:smooth;
}

body{

    min-height:100vh;

    background:
        linear-gradient(
            rgba(0,0,0,.84),
            rgba(0,0,0,.94)
        ),
        url('assets/images/hero.jpg')
        center/cover fixed no-repeat;

    color:#fff;

}


/* =========================================================
   HEADER
   ========================================================= */

.site-header{

    min-height:70px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:12px 7%;

    background:rgba(10,10,10,.96);

    border-bottom:
        1px solid
        rgba(214,194,156,.16);

    position:sticky;
    top:0;

    z-index:1000;

}

.brand{

    display:flex;
    align-items:center;

    gap:11px;

    text-decoration:none;

}

.brand img{

    width:42px;
    height:42px;

    object-fit:contain;

}

.brand span{

    font-family:
        'Playfair Display',
        serif;

    color:#D6C29C;

    font-size:18px;

    font-weight:600;

}

.header-links{

    display:flex;
    align-items:center;

    gap:8px;

}

.header-links a{

    color:#ddd;

    text-decoration:none;

    font-size:12px;

    padding:8px 11px;

    border-radius:7px;

    transition:.2s;

}

.header-links a:hover{

    color:#D6C29C;

}

.login-btn{

    border:
        1px solid
        rgba(214,194,156,.4);

    color:#D6C29C !important;

}


/* =========================================================
   PAGE
   ========================================================= */

.page{

    width:100%;

    max-width:1250px;

    margin:auto;

    padding:
        38px 5%
        70px;

}


/* =========================================================
   PAGE HEADING
   ========================================================= */

.page-heading{

    margin-bottom:28px;

}

.eyebrow{

    color:#8c7d63;

    text-transform:uppercase;

    letter-spacing:2px;

    font-size:9px;

    margin-bottom:5px;

}

.page-heading h1{

    font-family:
        'Playfair Display',
        serif;

    color:#D6C29C;

    font-size:34px;

    margin-bottom:7px;

}

.page-heading p{

    color:#888;

    font-size:11px;

    line-height:1.8;

    max-width:680px;

}


/* =========================================================
   ERROR
   ========================================================= */

.error-box{

    background:
        rgba(150,60,60,.12);

    border:
        1px solid
        rgba(200,100,100,.25);

    color:#dca2a2;

    padding:12px 15px;

    border-radius:10px;

    margin-bottom:18px;

    font-size:11px;

}


/* =========================================================
   MAIN LAYOUT
   ========================================================= */

.booking-layout{

    display:grid;

    grid-template-columns:
        minmax(0,1fr)
        330px;

    gap:22px;

    align-items:start;

}


/* =========================================================
   SECTIONS
   ========================================================= */

.section{

    background:
        rgba(18,18,18,.93);

    border:
        1px solid
        rgba(255,255,255,.07);

    border-radius:16px;

    padding:22px;

    margin-bottom:15px;

    box-shadow:
        0 12px 30px
        rgba(0,0,0,.18);

}

.section-head{

    display:flex;

    align-items:flex-start;

    gap:12px;

    margin-bottom:19px;

}

.step{

    width:29px;
    height:29px;

    min-width:29px;

    border-radius:50%;

    display:flex;

    justify-content:center;
    align-items:center;

    background:
        rgba(214,194,156,.10);

    border:
        1px solid
        rgba(214,194,156,.25);

    color:#D6C29C;

    font-size:11px;

    font-weight:600;

}

.section-title{

    color:#D6C29C;

    font-size:14px;

    font-weight:600;

    margin-bottom:3px;

}

.section-sub{

    color:#666;

    font-size:10px;

    line-height:1.6;

}

.field-title{

    color:#aaa;

    font-size:10px;

    font-weight:500;

    text-transform:uppercase;

    letter-spacing:.6px;

    margin:
        18px 0
        9px;

}

.field-title:first-child{
    margin-top:0;
}


/* =========================================================
   SELECTION CARDS
   ========================================================= */

.selection-grid{

    display:grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(130px,1fr)
        );

    gap:9px;

}

.select-card{

    min-height:49px;

    position:relative;

    display:flex;

    flex-direction:column;

    justify-content:center;
    align-items:center;

    text-align:center;

    padding:12px 10px;

    background:#101010;

    border:
        1px solid
        #292929;

    border-radius:10px;

    color:#ccc;

    cursor:pointer;

    font-size:11px;

    transition:.2s;

}

.select-card:hover{

    transform:
        translateY(-1px);

    border-color:
        rgba(214,194,156,.55);

    color:#fff;

}

.select-card.active{

    border-color:#D6C29C;

    background:
        rgba(214,194,156,.08);

    color:#D6C29C;

    box-shadow:
        0 0 0 1px
        rgba(214,194,156,.04);

}

.select-card.unavailable{

    opacity:.30;

    cursor:not-allowed;

    pointer-events:none;

}

.card-price{

    color:#D6C29C;

    font-size:10px;

    margin-top:3px;

}

.card-desc{

    color:#666;

    font-size:9px;

    line-height:1.4;

    margin-top:4px;

}

.service-description{

    margin-top:11px;

    padding:11px 13px;

    background:#0e0e0e;

    border-left:
        2px solid
        #D6C29C;

    border-radius:
        0 8px 8px 0;

    color:#888;

    font-size:10px;

    line-height:1.7;

}


/* =========================================================
   FORM
   ========================================================= */

.form-grid{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:14px;

}

.form-group{

    min-width:0;

}

.form-group.full{

    grid-column:
        1 / -1;

}

label{

    display:block;

    color:#999;

    font-size:10px;

    margin-bottom:6px;

}

.required{

    color:#D6C29C;

}

input,
select,
textarea{

    width:100%;

    padding:11px 12px;

    background:#0f0f0f;

    color:#fff;

    border:
        1px solid
        #303030;

    border-radius:9px;

    outline:none;

    font-size:11px;

    transition:.2s;

}

input:focus,
select:focus,
textarea:focus{

    border-color:#D6C29C;

    box-shadow:
        0 0 7px
        rgba(214,194,156,.12);

}

input::placeholder,
textarea::placeholder{

    color:#555;

}

select option{

    background:#111;

}

textarea{

    min-height:90px;

    resize:vertical;

    line-height:1.6;

}

input:read-only{

    opacity:.65;

}


/* =========================================================
   SCHEDULE
   ========================================================= */

.schedule-grid{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:16px;

    margin-top:18px;

}

.time-grid{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:8px;

}

.time-card{

    background:#101010;

    border:
        1px solid
        #292929;

    border-radius:9px;

    padding:10px 5px;

    text-align:center;

    cursor:pointer;

    color:#ccc;

    font-size:10px;

    transition:.2s;

}

.time-card:hover{

    border-color:#D6C29C;

}

.time-card.active{

    border-color:#D6C29C;

    background:
        rgba(214,194,156,.08);

    color:#D6C29C;

}

.time-card.dim{

    opacity:.25;

    pointer-events:none;

}

.slot-count{

    display:block;

    color:#666;

    font-size:8px;

    margin-top:3px;

}


/* =========================================================
   THERAPIST
   ========================================================= */

.therapist-choice{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:9px;

    margin-bottom:13px;

}

.therapist-list{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:9px;

    margin-top:10px;

}

.therapist-card{

    min-height:65px;

}

.available-text{

    color:#8fa98f;

}

.unavailable-text{

    color:#b78383;

}

.message{

    color:#777;

    font-size:10px;

    margin-top:10px;

    line-height:1.6;

}


/* =========================================================
   SUMMARY
   ========================================================= */

.summary-panel{

    position:sticky;

    top:88px;

    background:
        rgba(18,18,18,.97);

    border:
        1px solid
        rgba(214,194,156,.17);

    border-radius:16px;

    padding:22px;

    box-shadow:
        0 16px 40px
        rgba(0,0,0,.30);

}

.summary-panel h3{

    font-family:
        'Playfair Display',
        serif;

    color:#D6C29C;

    font-size:20px;

    margin-bottom:5px;

}

.summary-sub{

    color:#666;

    font-size:9px;

    margin-bottom:18px;

}

.summary-row{

    display:flex;

    justify-content:space-between;

    gap:12px;

    padding:10px 0;

    border-bottom:
        1px solid
        #272727;

}

.summary-label{

    color:#777;

    font-size:9px;

}

.summary-value{

    color:#ddd;

    font-size:10px;

    text-align:right;

    max-width:180px;

}

.summary-addons{

    line-height:1.5;

}

.price-area{

    padding:
        15px 0
        4px;

}

.price-line{

    display:flex;

    justify-content:space-between;

    gap:10px;

    margin-bottom:7px;

    color:#888;

    font-size:9px;

}

.total-line{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-top:12px;

    padding-top:13px;

    border-top:
        1px solid
        rgba(214,194,156,.18);

}

.total-label{

    color:#aaa;

    font-size:11px;

}

.total-price{

    color:#D6C29C;

    font-size:21px;

    font-weight:600;

}

.book-btn{

    width:100%;

    margin-top:17px;

    padding:13px;

    border:none;

    border-radius:9px;

    background:#D6C29C;

    color:#111;

    font-size:11px;

    font-weight:700;

    cursor:pointer;

    transition:.2s;

}

.book-btn:hover{

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(214,194,156,.18);

}

.booking-note{

    color:#555;

    font-size:8px;

    line-height:1.5;

    text-align:center;

    margin-top:10px;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:1000px){

    .booking-layout{

        grid-template-columns:1fr;

    }

    .summary-panel{

        position:static;

    }

}

@media(max-width:750px){

    .site-header{

        padding:
            12px 4%;

    }

    .brand span{

        display:none;

    }

    .header-links{

        gap:2px;

    }

    .header-links a{

        padding:7px;

        font-size:10px;

    }

    .page{

        padding:
            28px 4%
            50px;

    }

    .schedule-grid{

        grid-template-columns:1fr;

    }

    .time-grid{

        grid-template-columns:
            repeat(3,1fr);

    }

    .therapist-list{

        grid-template-columns:
            repeat(2,1fr);

    }

}

@media(max-width:550px){

    .page-heading h1{

        font-size:27px;

    }

    .section{

        padding:17px;

    }

    .form-grid{

        grid-template-columns:1fr;

    }

    .form-group.full{

        grid-column:auto;

    }

    .selection-grid,
    .therapist-choice{

        grid-template-columns:
            repeat(2,1fr);

    }

    .time-grid{

        grid-template-columns:
            repeat(2,1fr);

    }

}

</style>

</head>


<body>


<!-- =========================================================
     HEADER
     ========================================================= -->

<header class="site-header">

    <a
        href="index.php"
        class="brand"
    >

        <img
            src="assets/images/logo.png"
            alt="Mizpah Logo"
        >

        <span>
            Mizpah Wellness Spa
        </span>

    </a>


    <div class="header-links">

        <a href="index.php">
            Home
        </a>

        <a href="services.php">
            Services
        </a>

        <a href="therapist.php">
            Therapists
        </a>

        <a
            href="login.php"
            class="login-btn"
        >
            Login
        </a>

    </div>

</header>


<!-- =========================================================
     PAGE
     ========================================================= -->

<div class="page">


    <div class="page-heading">

        <div class="eyebrow">
            Guest Booking
        </div>

        <h1>
            Book an Appointment
        </h1>

        <p>
            No account is required. Choose your preferred
            service, schedule and therapist, then provide
            your contact details to complete your booking.
        </p>

    </div>


    <?php if (!empty($booking_error)): ?>

        <div class="error-box">
            <?= htmlspecialchars($booking_error) ?>
        </div>

    <?php endif; ?>


    <form
        method="POST"
        id="bookingForm"
    >


        <div class="booking-layout">


            <!-- =================================================
                 LEFT COLUMN
                 ================================================= -->

            <div>


                <!-- =============================================
                     STEP 1
                     ============================================= -->

                <div class="section">

                    <div class="section-head">

                        <div class="step">
                            1
                        </div>

                        <div>

                            <div class="section-title">
                                Choose Your Service
                            </div>

                            <div class="section-sub">
                                Select a category, service,
                                duration and optional add-ons.
                            </div>

                        </div>

                    </div>


                    <!-- CATEGORY -->

                    <div class="field-title">
                        Category
                    </div>

                    <div class="selection-grid">

                        <div
                            class="select-card category"
                            data-cat="Massage"
                        >
                            Massage
                        </div>

                        <div
                            class="select-card category"
                            data-cat="Package"
                        >
                            Package
                        </div>

                        <div
                            class="select-card category"
                            data-cat="Promo"
                        >
                            Promo
                        </div>

                    </div>


                    <!-- SERVICE -->

                    <div class="field-title">
                        Service
                    </div>

                    <div
                        class="selection-grid"
                        id="serviceBox"
                    >

                        <div
                            class="message"
                            style="grid-column:1/-1;"
                        >
                            Select a category first.
                        </div>

                    </div>


                    <div
                        id="serviceDesc"
                        class="service-description"
                    >
                        Select a service to view
                        its description.
                    </div>


                    <!-- DURATION -->

                    <div class="field-title">
                        Duration
                    </div>

                    <div
                        class="selection-grid"
                        id="durationBox"
                    >

                        <div
                            class="message"
                            style="grid-column:1/-1;"
                        >
                            Select a service first.
                        </div>

                    </div>


                    <!-- ADDONS -->

                    <div class="field-title">

                        Add-ons

                        <span
                            style="
                                color:#555;
                                text-transform:none;
                            "
                        >
                            (Optional)
                        </span>

                    </div>

                    <div
                        class="selection-grid"
                        id="addonBox"
                    >

                        <div
                            class="message"
                            style="grid-column:1/-1;"
                        >
                            Add-ons will appear after
                            selecting a service.
                        </div>

                    </div>


                    <input
                        type="hidden"
                        name="addons"
                        id="addons"
                    >

                </div>


                <!-- =============================================
                     STEP 2
                     ============================================= -->

                <div class="section">

                    <div class="section-head">

                        <div class="step">
                            2
                        </div>

                        <div>

                            <div class="section-title">
                                Schedule & Room
                            </div>

                            <div class="section-sub">
                                Choose your preferred room,
                                appointment date and available
                                time.
                            </div>

                        </div>

                    </div>


                    <!-- ROOM -->

                    <div class="field-title">
                        Room Type
                    </div>

                    <div class="selection-grid">

                        <div
                            class="select-card room"
                            data-room="Single Room"
                        >

                            Single Room

                            <div class="card-desc">
                                Individual booking
                            </div>

                        </div>


                        <div
                            class="select-card room"
                            data-room="Couple Room"
                        >

                            Couple Room

                            <div class="card-desc">
                                Automatically 2 pax
                            </div>

                        </div>

                    </div>


                    <input
                        type="hidden"
                        name="room_type"
                        id="room_type"
                    >


                    <div class="schedule-grid">


                        <div>

                            <label for="booking_date">

                                Appointment Date

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="date"
                                id="booking_date"
                                name="booking_date"
                                required
                            >

                        </div>


                        <div>

                            <label for="pax">

                                Number of Guests

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="number"
                                id="pax"
                                name="pax"
                                value="1"
                                min="1"
                                max="6"
                                required
                            >

                        </div>


                    </div>


                    <!-- TIME -->

                    <div class="field-title">
                        Available Time
                    </div>

                    <div
                        class="time-grid"
                        id="timeBox"
                    >

                        <div
                            class="message"
                            style="grid-column:1/-1;"
                        >
                            Select an appointment date first.
                        </div>

                    </div>


                    <input
                        type="hidden"
                        name="booking_time"
                        id="booking_time"
                    >

                </div>


                <!-- =============================================
                     STEP 3
                     ============================================= -->

                <div class="section">

                    <div class="section-head">

                        <div class="step">
                            3
                        </div>

                        <div>

                            <div class="section-title">
                                Choose Your Therapist
                            </div>

                            <div class="section-sub">
                                You may choose a specific
                                available therapist or leave
                                it as No Preference.
                            </div>

                        </div>

                    </div>


                    <div class="therapist-choice">

                        <div
                            class="select-card therapist-option active"
                            data-therapist-option="0"
                        >

                            <strong>
                                No Preference
                            </strong>

                            <span class="card-desc">
                                Any available therapist
                            </span>

                        </div>


                        <div
                            class="select-card therapist-option"
                            data-therapist-option="choose"
                        >

                            <strong>
                                Choose Therapist
                            </strong>

                            <span class="card-desc">
                                Select a specific therapist
                            </span>

                        </div>

                    </div>


                    <div
                        id="therapistList"
                        class="therapist-list"
                    ></div>


                    <div
                        id="therapistMessage"
                        class="message"
                    >
                        Select a date and time first
                        to view therapist availability.
                    </div>


                    <input
                        type="hidden"
                        name="therapist"
                        id="therapist"
                        value="0"
                    >

                </div>


                <!-- =============================================
                     STEP 4
                     ============================================= -->

                <div class="section">

                    <div class="section-head">

                        <div class="step">
                            4
                        </div>

                        <div>

                            <div class="section-title">
                                Guest Details
                            </div>

                            <div class="section-sub">
                                Enter your contact information
                                so the spa can identify your
                                appointment.
                            </div>

                        </div>

                    </div>


                    <div class="form-grid">


                        <div class="form-group">

                            <label>

                                Full Name

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                name="customer_name"
                                placeholder="Enter your full name"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>

                                Phone Number

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                name="phone"
                                placeholder="09XXXXXXXXX"
                                required
                            >

                        </div>


                        <div class="form-group full">

                            <label>

                                Payment Method

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <select
                                name="payment_method"
                                required
                            >

                                <option value="Cash">
                                    Cash
                                </option>

                                <option value="GCash">
                                    GCash
                                </option>

                            </select>

                        </div>


                        <div class="form-group full">

                            <label>

                                Notes / Special Request

                                <span style="color:#555;">
                                    (Optional)
                                </span>

                            </label>

                            <textarea
                                name="notes"
                                placeholder="Any additional request for your appointment..."
                            ></textarea>

                        </div>


                    </div>

                </div>


            </div>


            <!-- =================================================
                 SUMMARY
                 ================================================= -->

            <div>

                <div class="summary-panel">

                    <h3>
                        Booking Summary
                    </h3>

                    <div class="summary-sub">
                        Review your appointment before booking.
                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Service
                        </span>

                        <span
                            class="summary-value"
                            id="sumService"
                        >
                            —
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Duration
                        </span>

                        <span
                            class="summary-value"
                            id="sumDuration"
                        >
                            —
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Room
                        </span>

                        <span
                            class="summary-value"
                            id="sumRoom"
                        >
                            —
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Guests
                        </span>

                        <span
                            class="summary-value"
                            id="sumPax"
                        >
                            1
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Date
                        </span>

                        <span
                            class="summary-value"
                            id="sumDate"
                        >
                            —
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Time
                        </span>

                        <span
                            class="summary-value"
                            id="sumTime"
                        >
                            —
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Therapist
                        </span>

                        <span
                            class="summary-value"
                            id="sumTherapist"
                        >
                            No Preference
                        </span>

                    </div>


                    <div class="summary-row">

                        <span class="summary-label">
                            Add-ons
                        </span>

                        <span
                            class="summary-value summary-addons"
                            id="sumAddons"
                        >
                            None
                        </span>

                    </div>


                    <div class="price-area">


                        <div class="price-line">

                            <span>
                                Service
                            </span>

                            <span id="sumBasePrice">
                                ₱0.00
                            </span>

                        </div>


                        <div class="price-line">

                            <span>
                                Add-ons
                            </span>

                            <span id="sumAddonPrice">
                                ₱0.00
                            </span>

                        </div>


                        <div class="total-line">

                            <span class="total-label">
                                Total
                            </span>

                            <span
                                class="total-price"
                                id="sumTotal"
                            >
                                ₱0.00
                            </span>

                        </div>


                    </div>


                    <button
                        type="submit"
                        name="submit_booking"
                        class="book-btn"
                    >
                        Confirm Booking
                    </button>


                    <div class="booking-note">
                        No account is required. Your booking
                        will initially be submitted as Pending.
                    </div>

                </div>

            </div>


        </div>


        <!-- =================================================
             HIDDEN VALUES
             ================================================= -->

        <input
            type="hidden"
            name="service_id"
            id="service_id"
        >

        <input
            type="hidden"
            name="service"
            id="service"
        >

        <input
            type="hidden"
            name="duration"
            id="duration"
        >

        <input
            type="hidden"
            name="price"
            id="price"
            value="0"
        >


    </form>

</div>


<script>

/* =========================================================
   DOM
   ========================================================= */

const serviceBox =
    document.getElementById('serviceBox');

const serviceDesc =
    document.getElementById('serviceDesc');

const durationBox =
    document.getElementById('durationBox');

const addonBox =
    document.getElementById('addonBox');

const timeBox =
    document.getElementById('timeBox');

const therapistList =
    document.getElementById('therapistList');

const therapistMessage =
    document.getElementById('therapistMessage');

const serviceIdInput =
    document.getElementById('service_id');

const serviceInput =
    document.getElementById('service');

const durationInput =
    document.getElementById('duration');

const priceInput =
    document.getElementById('price');

const addonsInput =
    document.getElementById('addons');

const roomInput =
    document.getElementById('room_type');

const bookingDate =
    document.getElementById('booking_date');

const bookingTime =
    document.getElementById('booking_time');

const therapistInput =
    document.getElementById('therapist');

const paxInput =
    document.getElementById('pax');


/* =========================================================
   PRICE
   ========================================================= */

let basePrice = 0;

let addonTotal = 0;


/* =========================================================
   SUMMARY DATA
   ========================================================= */

let summary = {

    service: '—',

    duration: '—',

    room: '—',

    pax: '1',

    date: '—',

    time: '—',

    therapist: 'No Preference',

    addons: 'None'

};


function money(value){

    return '₱' +
        Number(value || 0)
        .toLocaleString(
            'en-PH',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        );

}


function renderSummary(){

    document.getElementById(
        'sumService'
    ).textContent =
        summary.service;

    document.getElementById(
        'sumDuration'
    ).textContent =
        summary.duration;

    document.getElementById(
        'sumRoom'
    ).textContent =
        summary.room;

    document.getElementById(
        'sumPax'
    ).textContent =
        summary.pax;

    document.getElementById(
        'sumDate'
    ).textContent =
        summary.date;

    document.getElementById(
        'sumTime'
    ).textContent =
        summary.time;

    document.getElementById(
        'sumTherapist'
    ).textContent =
        summary.therapist;

    document.getElementById(
        'sumAddons'
    ).textContent =
        summary.addons;


    document.getElementById(
        'sumBasePrice'
    ).textContent =
        money(basePrice);


    document.getElementById(
        'sumAddonPrice'
    ).textContent =
        money(addonTotal);


    const total =
        Number(basePrice) +
        Number(addonTotal);


    document.getElementById(
        'sumTotal'
    ).textContent =
        money(total);


    /*
       IMPORTANT:
       Save complete total:
       service/duration + add-ons
    */

    priceInput.value =
        total.toFixed(2);

}


/* =========================================================
   RESET SERVICE
   ========================================================= */

function resetServiceSelection(){

    serviceIdInput.value = '';

    serviceInput.value = '';

    durationInput.value = '';

    addonsInput.value = '';

    basePrice = 0;

    addonTotal = 0;


    summary.service = '—';

    summary.duration = '—';

    summary.addons = 'None';


    serviceDesc.textContent =
        'Select a service to view its description.';


    durationBox.innerHTML = `
        <div
            class="message"
            style="grid-column:1/-1;"
        >
            Select a service first.
        </div>
    `;


    addonBox.innerHTML = `
        <div
            class="message"
            style="grid-column:1/-1;"
        >
            Add-ons will appear after
            selecting a service.
        </div>
    `;


    renderSummary();

}


/* =========================================================
   CATEGORY
   ========================================================= */

document.addEventListener(
    'click',
    function(e){

        const category =
            e.target.closest('.category');

        if(!category){
            return;
        }


        document
        .querySelectorAll('.category')
        .forEach(function(card){

            card.classList.remove(
                'active'
            );

        });


        category.classList.add(
            'active'
        );


        resetServiceSelection();


        serviceBox.innerHTML = `
            <div
                class="message"
                style="grid-column:1/-1;"
            >
                Loading services...
            </div>
        `;


        fetch(
            'get_services_by_category.php?cat=' +
            encodeURIComponent(
                category.dataset.cat
            )
        )

        .then(function(response){
            return response.json();
        })

        .then(function(data){

            serviceBox.innerHTML = '';


            if(
                !Array.isArray(data) ||
                data.length === 0
            ){

                serviceBox.innerHTML = `
                    <div
                        class="message"
                        style="grid-column:1/-1;"
                    >
                        No services available.
                    </div>
                `;

                return;

            }


            data.forEach(function(service){

                const card =
                    document.createElement(
                        'div'
                    );


                card.className =
                    'select-card service';


                card.dataset.id =
                    service.id;


                card.dataset.name =
                    service.service_name;


                card.dataset.desc =
                    service.description || '';


                card.textContent =
                    service.service_name;


                serviceBox.appendChild(
                    card
                );

            });

        })

        .catch(function(error){

            console.error(
                'Service error:',
                error
            );


            serviceBox.innerHTML = `
                <div
                    class="message"
                    style="grid-column:1/-1;"
                >
                    Unable to load services.
                </div>
            `;

        });

    }
);


/* =========================================================
   SERVICE
   ========================================================= */

document.addEventListener(
    'click',
    function(e){

        const selectedService =
            e.target.closest('.service');

        if(!selectedService){
            return;
        }


        document
        .querySelectorAll('.service')
        .forEach(function(card){

            card.classList.remove(
                'active'
            );

        });


        selectedService.classList.add(
            'active'
        );


        serviceIdInput.value =
            selectedService.dataset.id;


        serviceInput.value =
            selectedService.dataset.name;


        serviceDesc.textContent =
            selectedService.dataset.desc ||
            'No description available.';


        summary.service =
            selectedService.dataset.name;


        summary.duration = '—';

        summary.addons = 'None';


        durationInput.value = '';

        addonsInput.value = '';


        basePrice = 0;

        addonTotal = 0;


        renderSummary();


        /* =============================================
           LOAD DURATION
           ============================================= */

        durationBox.innerHTML = `
            <div
                class="message"
                style="grid-column:1/-1;"
            >
                Loading durations...
            </div>
        `;


        fetch(
            'get_duration.php?id=' +
            encodeURIComponent(
                selectedService.dataset.id
            )
        )

        .then(function(response){
            return response.json();
        })

        .then(function(data){

            durationBox.innerHTML = '';


            if(
                !Array.isArray(data) ||
                data.length === 0
            ){

                durationBox.innerHTML = `
                    <div
                        class="message"
                        style="grid-column:1/-1;"
                    >
                        No duration available.
                    </div>
                `;

                return;

            }


            data.forEach(function(item){

                const card =
                    document.createElement(
                        'div'
                    );


                card.className =
                    'select-card duration';


                card.dataset.duration =
                    item.duration;


                card.dataset.price =
                    item.price;


                card.innerHTML = `
                    <span>
                        ${item.duration}
                    </span>

                    <span class="card-price">
                        ${money(item.price)}
                    </span>
                `;


                durationBox.appendChild(
                    card
                );

            });

        })

        .catch(function(error){

            console.error(
                'Duration error:',
                error
            );

        });


        /* =============================================
           LOAD ADDONS
           ============================================= */

        addonBox.innerHTML = `
            <div
                class="message"
                style="grid-column:1/-1;"
            >
                Loading add-ons...
            </div>
        `;


        fetch('get_addons.php')

        .then(function(response){
            return response.json();
        })

        .then(function(data){

            addonBox.innerHTML = '';


            if(
                !Array.isArray(data) ||
                data.length === 0
            ){

                addonBox.innerHTML = `
                    <div
                        class="message"
                        style="grid-column:1/-1;"
                    >
                        No add-ons available.
                    </div>
                `;

                return;

            }


            data.forEach(function(addon){

                const card =
                    document.createElement(
                        'div'
                    );


                card.className =
                    'select-card addon';


                card.dataset.name =
                    addon.service_name;


                card.dataset.price =
                    addon.price;


                card.innerHTML = `
                    <span>
                        ${addon.service_name}
                    </span>

                    <span class="card-price">
                        ${money(addon.price)}
                    </span>

                    ${
                        addon.description
                        ?
                        `
                            <span class="card-desc">
                                ${addon.description}
                            </span>
                        `
                        :
                        ''
                    }
                `;


                addonBox.appendChild(
                    card
                );

            });

        })

        .catch(function(error){

            console.error(
                'Add-ons error:',
                error
            );

        });

    }
);


/* =========================================================
   DURATION
   ========================================================= */

document.addEventListener(
    'click',
    function(e){

        const selectedDuration =
            e.target.closest('.duration');

        if(!selectedDuration){
            return;
        }


        document
        .querySelectorAll('.duration')
        .forEach(function(card){

            card.classList.remove(
                'active'
            );

        });


        selectedDuration.classList.add(
            'active'
        );


        durationInput.value =
            selectedDuration.dataset.duration;


        basePrice =
            parseFloat(
                selectedDuration.dataset.price
            ) || 0;


        summary.duration =
            selectedDuration.dataset.duration;


        renderSummary();


        /*
           Duration can affect therapist availability.
        */

        if(
            bookingDate.value &&
            bookingTime.value
        ){

            const chooseOption =
                document.querySelector(
                    '.therapist-option[data-therapist-option="choose"]'
                );


            if(
                chooseOption &&
                chooseOption.classList.contains(
                    'active'
                )
            ){

                loadTherapists(
                    bookingDate.value,
                    bookingTime.value
                );

            }

        }

    }
);


/* =========================================================
   ADD-ONS
   ========================================================= */

document.addEventListener(
    'click',
    function(e){

        const selectedAddon =
            e.target.closest('.addon');

        if(!selectedAddon){
            return;
        }


        selectedAddon.classList.toggle(
            'active'
        );


        const names = [];

        addonTotal = 0;


        document
        .querySelectorAll(
            '.addon.active'
        )
        .forEach(function(card){

            names.push(
                card.dataset.name
            );


            addonTotal +=
                parseFloat(
                    card.dataset.price
                ) || 0;

        });


        addonsInput.value =
            names.join(', ');


        summary.addons =
            names.length
            ? names.join(', ')
            : 'None';


        renderSummary();

    }
);


/* =========================================================
   ROOM
   ========================================================= */

document.addEventListener(
    'click',
    function(e){

        const selectedRoom =
            e.target.closest('.room');

        if(!selectedRoom){
            return;
        }


        document
        .querySelectorAll('.room')
        .forEach(function(card){

            card.classList.remove(
                'active'
            );

        });


        selectedRoom.classList.add(
            'active'
        );


        roomInput.value =
            selectedRoom.dataset.room;


        summary.room =
            selectedRoom.dataset.room;


        if(
            selectedRoom.dataset.room ===
            'Couple Room'
        ){

            paxInput.value = 2;

            paxInput.readOnly = true;

            summary.pax = '2';

        }else{

            paxInput.value = 1;

            paxInput.readOnly = false;

            summary.pax = '1';

        }


        renderSummary();

    }
);


/* =========================================================
   PAX
   ========================================================= */

paxInput.addEventListener(
    'input',
    function(){

        let value =
            parseInt(
                paxInput.value
            ) || 1;


        if(value < 1){
            value = 1;
        }


        if(value > 6){
            value = 6;
        }


        paxInput.value = value;

        summary.pax =
            String(value);


        renderSummary();

    }
);


/* =========================================================
   MIN DATE
   ========================================================= */

const today =
    new Date();


const year =
    today.getFullYear();


const month =
    String(
        today.getMonth() + 1
    ).padStart(
        2,
        '0'
    );


const day =
    String(
        today.getDate()
    ).padStart(
        2,
        '0'
    );


bookingDate.min =
    `${year}-${month}-${day}`;


/* =========================================================
   THERAPIST RESET
   ========================================================= */

function resetTherapist(){

    document
    .querySelectorAll(
        '.therapist-option'
    )
    .forEach(function(card){

        card.classList.remove(
            'active'
        );

    });


    const noPreference =
        document.querySelector(
            '.therapist-option[data-therapist-option="0"]'
        );


    if(noPreference){

        noPreference.classList.add(
            'active'
        );

    }


    therapistInput.value = '0';

    therapistList.innerHTML = '';


    summary.therapist =
        'No Preference';


    therapistMessage.textContent =
        'Select a date and time first to view therapist availability.';


    renderSummary();

}


/* =========================================================
   THERAPIST OPTION
   ========================================================= */

document.addEventListener(
    'click',
    function(e){

        const option =
            e.target.closest(
                '.therapist-option'
            );

        if(!option){
            return;
        }


        document
        .querySelectorAll(
            '.therapist-option'
        )
        .forEach(function(card){

            card.classList.remove(
                'active'
            );

        });


        option.classList.add(
            'active'
        );


        const value =
            option.dataset.therapistOption;


        if(value === '0'){

            therapistInput.value = '0';

            therapistList.innerHTML = '';


            summary.therapist =
                'No Preference';


            therapistMessage.textContent =
                'No Preference selected. Any available therapist may be assigned.';


            renderSummary();

            return;

        }


        if(value === 'choose'){

            therapistInput.value = '0';

            summary.therapist =
                'No Preference';


            renderSummary();


            if(
                !bookingDate.value ||
                !bookingTime.value
            ){

                therapistList.innerHTML = '';

                therapistMessage.textContent =
                    'Please select a date and time first.';

                return;

            }


            loadTherapists(
                bookingDate.value,
                bookingTime.value
            );

        }

    }
);


/* =========================================================
   LOAD THERAPISTS
   ========================================================= */

function loadTherapists(
    selectedDate,
    selectedTime
){

    therapistList.innerHTML = '';

    therapistMessage.textContent =
        'Loading therapists...';


    let selectedDuration =
        durationInput.value ||
        '1 Hour';


    fetch(
        'get_available_therapists.php?date=' +
        encodeURIComponent(
            selectedDate
        ) +
        '&time=' +
        encodeURIComponent(
            selectedTime
        ) +
        '&duration=' +
        encodeURIComponent(
            selectedDuration
        )
    )

    .then(function(response){
        return response.json();
    })

    .then(function(data){

        therapistList.innerHTML = '';


        if(
            !Array.isArray(data) ||
            data.length === 0
        ){

            therapistMessage.textContent =
                'No therapists found.';

            return;

        }


        therapistMessage.textContent =
            'Select an available therapist.';


        data.forEach(function(therapist){

            const therapistId =
                therapist.id ?? '';


            const therapistName =
                therapist.name ||
                'Unnamed Therapist';


            const isAvailable =
                therapist.available === undefined
                ? true
                : Boolean(
                    therapist.available
                );


            const card =
                document.createElement(
                    'div'
                );


            card.className =
                'select-card therapist-card';


            card.dataset.therapistId =
                therapistId;


            card.dataset.therapistName =
                therapistName;


            if(!isAvailable){

                card.classList.add(
                    'unavailable'
                );


                card.innerHTML = `

                    <strong>
                        ${therapistName}
                    </strong>

                    <span
                        class="
                            card-desc
                            unavailable-text
                        "
                    >
                        Booked / Unavailable
                    </span>

                `;

            }else{

                card.innerHTML = `

                    <strong>
                        ${therapistName}
                    </strong>

                    <span
                        class="
                            card-desc
                            available-text
                        "
                    >
                        Available
                    </span>

                `;


                card.addEventListener(
                    'click',
                    function(){

                        document
                        .querySelectorAll(
                            '.therapist-card'
                        )
                        .forEach(function(item){

                            item.classList.remove(
                                'active'
                            );

                        });


                        card.classList.add(
                            'active'
                        );


                        therapistInput.value =
                            therapistId;


                        summary.therapist =
                            therapistName;


                        renderSummary();

                    }
                );

            }


            therapistList.appendChild(
                card
            );

        });

    })

    .catch(function(error){

        console.error(
            'Therapist error:',
            error
        );


        therapistList.innerHTML = '';


        therapistMessage.textContent =
            'Unable to load therapists. Please try again.';

    });

}


/* =========================================================
   DATE CHANGE / LOAD TIME
   ========================================================= */

bookingDate.addEventListener(
    'change',
    async function(){

        timeBox.innerHTML = '';

        bookingTime.value = '';


        resetTherapist();


        const selectedDate =
            bookingDate.value;


        if(!selectedDate){

            summary.date = '—';

            summary.time = '—';

            renderSummary();

            return;

        }


        const dateObject =
            new Date(
                selectedDate +
                'T00:00:00'
            );


        summary.date =
            dateObject.toLocaleDateString(
                'en-US',
                {
                    month:'short',
                    day:'numeric',
                    year:'numeric'
                }
            );


        summary.time = '—';


        renderSummary();


        timeBox.innerHTML = `
            <div
                class="message"
                style="grid-column:1/-1;"
            >
                Checking available time slots...
            </div>
        `;


        const cards = [];


        /*
           Preserved existing guest booking
           time range: 10 AM - 10 PM
        */

        for(
            let hour = 10;
            hour <= 22;
            hour++
        ){

            const timeValue =
                String(hour)
                .padStart(
                    2,
                    '0'
                ) +
                ':00';


            try{

                const response =
                    await fetch(
                        'check_slot.php?date=' +
                        encodeURIComponent(
                            selectedDate
                        ) +
                        '&time=' +
                        encodeURIComponent(
                            timeValue
                        )
                    );


                const data =
                    await response.json();


                const card =
                    document.createElement(
                        'div'
                    );


                card.className =
                    'time-card';


                const displayHour =
                    hour % 12 || 12;


                const meridiem =
                    hour >= 12
                    ? 'PM'
                    : 'AM';


                card.innerHTML = `

                    ${displayHour}:00 ${meridiem}

                    <span class="slot-count">

                        ${
                            data.remaining ?? 0
                        }

                        slot${
                            Number(
                                data.remaining
                            ) === 1
                            ? ''
                            : 's'
                        }

                    </span>

                `;


                if(!data.available){

                    card.classList.add(
                        'dim'
                    );

                }else{

                    card.addEventListener(
                        'click',
                        function(){

                            document
                            .querySelectorAll(
                                '.time-card'
                            )
                            .forEach(function(item){

                                item.classList.remove(
                                    'active'
                                );

                            });


                            card.classList.add(
                                'active'
                            );


                            bookingTime.value =
                                timeValue;


                            summary.time =
                                `${displayHour}:00 ${meridiem}`;


                            resetTherapist();


                            /*
                               resetTherapist also renders
                               summary, so restore chosen time.
                            */

                            summary.time =
                                `${displayHour}:00 ${meridiem}`;


                            therapistMessage.textContent =
                                'Choose No Preference or click Choose Therapist to view availability.';


                            renderSummary();

                        }
                    );

                }


                cards.push(card);


            }catch(error){

                console.error(
                    'Time slot error:',
                    error
                );

            }

        }


        timeBox.innerHTML = '';


        if(cards.length === 0){

            timeBox.innerHTML = `
                <div
                    class="message"
                    style="grid-column:1/-1;"
                >
                    Unable to load available time slots.
                </div>
            `;

            return;

        }


        cards.forEach(function(card){

            timeBox.appendChild(
                card
            );

        });

    }
);


/* =========================================================
   FORM VALIDATION
   ========================================================= */

document
.getElementById(
    'bookingForm'
)
.addEventListener(
    'submit',
    function(e){

        if(
            !serviceIdInput.value
        ){

            alert(
                'Please select a service.'
            );

            e.preventDefault();

            return;

        }


        if(
            !durationInput.value
        ){

            alert(
                'Please select a duration.'
            );

            e.preventDefault();

            return;

        }


        if(
            !roomInput.value
        ){

            alert(
                'Please select a room.'
            );

            e.preventDefault();

            return;

        }


        if(
            !bookingDate.value
        ){

            alert(
                'Please select an appointment date.'
            );

            e.preventDefault();

            return;

        }


        if(
            !bookingTime.value
        ){

            alert(
                'Please select an available time.'
            );

            e.preventDefault();

            return;

        }


        if(
            !priceInput.value ||
            Number(
                priceInput.value
            ) <= 0
        ){

            alert(
                'Please select a service duration so the booking price can be calculated.'
            );

            e.preventDefault();

            return;

        }

    }
);


/* =========================================================
   INITIAL SUMMARY
   ========================================================= */

renderSummary();

</script>

</body>

</html>