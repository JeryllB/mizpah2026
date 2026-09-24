<?php

session_start();

include __DIR__ . '/../includes/db.php';


/* =========================================================
   ADMIN AUTHENTICATION
   ========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit;

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

<title>Walk-in Booking | Mizpah Wellness Spa</title>

<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet"
>

<style>

/* =========================================================
   RESET
   ========================================================= */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    min-height:100vh;

    background:#0b0b0b;

    color:#fff;

    font-family:'Poppins',sans-serif;

}


/* =========================================================
   BACKGROUND
   ========================================================= */

body::before{

    content:"";

    position:fixed;

    top:-250px;
    right:-200px;

    width:500px;
    height:500px;

    background:
        rgba(214,194,156,.045);

    filter:blur(120px);

    pointer-events:none;

}


/* =========================================================
   HEADER
   ========================================================= */

.header{

    position:sticky;

    top:0;

    z-index:1000;

    min-height:68px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:20px;

    padding:11px 5%;

    background:
        rgba(11,11,11,.96);

    border-bottom:
        1px solid
        rgba(214,194,156,.12);

    backdrop-filter:blur(15px);

}

.brand{

    display:flex;

    align-items:center;

    gap:11px;

}

.logo{

    width:42px;
    height:42px;

    object-fit:contain;

}

.brand-text small{

    display:block;

    color:#777;

    font-size:8px;

    text-transform:uppercase;

    letter-spacing:1.5px;

}

.brand-text strong{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:17px;

    font-weight:600;

}

.back-btn{

    padding:8px 13px;

    border:
        1px solid
        rgba(214,194,156,.25);

    border-radius:7px;

    color:#aaa;

    text-decoration:none;

    font-size:9px;

    transition:.2s;

}

.back-btn:hover{

    color:#111;

    background:#D6C29C;

}


/* =========================================================
   HERO
   ========================================================= */

.hero{

    padding:
        45px 5%
        30px;

    text-align:center;

}

.hero-label{

    color:#7d705a;

    font-size:8px;

    text-transform:uppercase;

    letter-spacing:2.5px;

    margin-bottom:7px;

}

.hero h1{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:
        clamp(27px,4vw,38px);

    font-weight:600;

    margin-bottom:7px;

}

.hero p{

    color:#777;

    font-size:10px;

    line-height:1.7;

}


/* =========================================================
   MAIN LAYOUT
   ========================================================= */

.page{

    width:100%;

    max-width:1180px;

    margin:auto;

    padding:
        15px 4%
        70px;

}

.booking-layout{

    display:grid;

    grid-template-columns:
        minmax(0,1fr)
        300px;

    gap:18px;

    align-items:start;

}

.form-column{

    display:flex;

    flex-direction:column;

    gap:14px;

}


/* =========================================================
   SECTION BOX
   ========================================================= */

.box{

    background:
        linear-gradient(
            145deg,
            #141414,
            #101010
        );

    border:
        1px solid
        rgba(255,255,255,.065);

    border-radius:14px;

    padding:20px;

}

.step-header{

    display:flex;

    align-items:flex-start;

    gap:11px;

    margin-bottom:17px;

}

.step-number{

    width:27px;
    height:27px;

    flex-shrink:0;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:50%;

    background:
        rgba(214,194,156,.10);

    border:
        1px solid
        rgba(214,194,156,.22);

    color:#D6C29C;

    font-size:9px;

    font-weight:600;

}

.step-header h2{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:17px;

    font-weight:600;

    margin-bottom:2px;

}

.step-header p{

    color:#666;

    font-size:8px;

    line-height:1.5;

}


/* =========================================================
   SUB LABEL
   ========================================================= */

.field-label{

    display:block;

    margin:
        16px 0
        7px;

    color:#888;

    font-size:8px;

    font-weight:500;

    text-transform:uppercase;

    letter-spacing:1px;

}

.field-label:first-child{

    margin-top:0;

}


/* =========================================================
   OPTION GRID
   ========================================================= */

.grid{

    display:grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(130px,1fr)
        );

    gap:8px;

}


/* =========================================================
   CARDS
   ========================================================= */

.card{

    position:relative;

    min-height:48px;

    padding:11px;

    display:flex;

    flex-direction:column;

    align-items:center;

    justify-content:center;

    text-align:center;

    background:#0f0f0f;

    border:
        1px solid
        #262626;

    border-radius:9px;

    color:#bbb;

    font-size:9px;

    line-height:1.5;

    cursor:pointer;

    transition:.2s;

    user-select:none;

}

.card:hover{

    border-color:
        rgba(214,194,156,.35);

    color:#D6C29C;

    transform:
        translateY(-1px);

}

.card.active{

    border-color:#D6C29C;

    background:
        rgba(214,194,156,.075);

    color:#D6C29C;

    box-shadow:
        inset 0 0 0 1px
        rgba(214,194,156,.10);

}

.card.dim{

    opacity:.28;

    cursor:not-allowed;

    pointer-events:none;

    transform:none;

}

.small{

    margin-top:3px;

    color:#666;

    font-size:7px;

    line-height:1.4;

}


/* =========================================================
   SERVICE DESCRIPTION
   ========================================================= */

#serviceDesc{

    margin-top:9px;

    color:#777;

    font-size:9px;

    line-height:1.6;

}


/* =========================================================
   INPUTS
   ========================================================= */

input,
select,
textarea{

    width:100%;

    padding:11px 12px;

    background:#0d0d0d;

    color:#ddd;

    border:
        1px solid
        #292929;

    border-radius:8px;

    outline:none;

    font-family:'Poppins',sans-serif;

    font-size:9px;

    transition:.2s;

}

input:focus,
select:focus,
textarea:focus{

    border-color:
        rgba(214,194,156,.55);

    box-shadow:
        0 0 0 2px
        rgba(214,194,156,.05);

}

input::placeholder,
textarea::placeholder{

    color:#555;

}

textarea{

    min-height:90px;

    resize:vertical;

}

input[type="date"]{

    color-scheme:dark;

}


/* =========================================================
   CUSTOMER GRID
   ========================================================= */

.customer-grid{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:10px;

}

.full-field{

    grid-column:1/-1;

}


/* =========================================================
   SUMMARY
   ========================================================= */

.summary{

    position:sticky;

    top:86px;

    background:
        linear-gradient(
            145deg,
            #151515,
            #101010
        );

    border:
        1px solid
        rgba(214,194,156,.16);

    border-radius:14px;

    padding:20px;

}

.summary-label{

    color:#6f634f;

    font-size:7px;

    text-transform:uppercase;

    letter-spacing:2px;

    margin-bottom:4px;

}

.summary h3{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:19px;

    margin-bottom:18px;

}

.summary-row{

    display:flex;

    justify-content:space-between;

    align-items:flex-start;

    gap:15px;

    padding:
        9px 0;

    border-bottom:
        1px solid
        rgba(255,255,255,.045);

}

.summary-row span:first-child{

    color:#666;

    font-size:8px;

}

.summary-row span:last-child{

    color:#bbb;

    font-size:8px;

    text-align:right;

    word-break:break-word;

}

.summary-addons{

    margin-top:14px;

    padding:
        10px;

    background:
        rgba(255,255,255,.02);

    border-radius:8px;

}

.summary-addons-title{

    color:#666;

    font-size:7px;

    text-transform:uppercase;

    letter-spacing:1px;

    margin-bottom:5px;

}

#summaryAddons{

    color:#888;

    font-size:8px;

    line-height:1.6;

}


/* =========================================================
   TOTAL
   ========================================================= */

.total-box{

    display:flex;

    align-items:flex-end;

    justify-content:space-between;

    gap:15px;

    margin-top:16px;

    padding-top:14px;

    border-top:
        1px solid
        rgba(214,194,156,.15);

}

.total-label{

    color:#777;

    font-size:8px;

}

.total-price{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:23px;

    font-weight:600;

}


/* =========================================================
   CONFIRM BUTTON
   ========================================================= */

.btn{

    width:100%;

    margin-top:16px;

    padding:12px 15px;

    border:none;

    border-radius:8px;

    background:#D6C29C;

    color:#111;

    font-family:'Poppins',sans-serif;

    font-size:9px;

    font-weight:600;

    cursor:pointer;

    transition:.2s;

}

.btn:hover{

    background:#ead8b1;

    transform:
        translateY(-1px);

}

.admin-note{

    margin-top:10px;

    color:#555;

    font-size:7px;

    line-height:1.5;

    text-align:center;

}


/* =========================================================
   PAX NOTE
   ========================================================= */

.pax-note{

    margin-top:5px;

    color:#666;

    font-size:7px;

}


/* =========================================================
   NO OPTIONS
   ========================================================= */

.empty-option{

    grid-column:1/-1;

    padding:14px;

    border:
        1px dashed
        #292929;

    border-radius:8px;

    color:#555;

    font-size:8px;

    text-align:center;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:900px){

    .booking-layout{

        grid-template-columns:1fr;

    }

    .summary{

        position:static;

    }

}

@media(max-width:600px){

    .header{

        padding:
            10px 4%;

    }

    .brand-text strong{

        font-size:14px;

    }

    .brand-text small{

        display:none;

    }

    .logo{

        width:36px;
        height:36px;

    }

    .hero{

        padding:
            35px 20px
            20px;

    }

    .page{

        padding:
            10px 4%
            50px;

    }

    .box{

        padding:16px;

    }

    .customer-grid{

        grid-template-columns:1fr;

    }

    .full-field{

        grid-column:auto;

    }

    .grid{

        grid-template-columns:
            repeat(
                2,
                minmax(0,1fr)
            );

    }

}

</style>

</head>


<body>


<!-- =========================================================
     HEADER
     ========================================================= -->

<header class="header">

    <div class="brand">

        <img
            src="../assets/images/logo.png"
            class="logo"
            alt="Mizpah Wellness Spa"
        >

        <div class="brand-text">

            <small>
                Admin
            </small>

            <strong>
                Walk-in Booking
            </strong>

        </div>

    </div>


    <a
        href="bookings.php"
        class="back-btn"
    >
        Back to Bookings
    </a>

</header>


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="hero">

    <div class="hero-label">
        Mizpah Wellness Spa
    </div>

    <h1>
        Walk-in Appointment
    </h1>

    <p>
        Create a booking for customers who visit the spa
        without an online reservation.
    </p>

</section>


<!-- =========================================================
     PAGE
     ========================================================= -->

<div class="page">

<form
    method="POST"
    action="walkin-save.php"
    id="bookingForm"
>


<div class="booking-layout">


<!-- =========================================================
     LEFT SIDE
     ========================================================= -->

<div class="form-column">


    <!-- =====================================================
         STEP 1
         ===================================================== -->

    <section class="box">

        <div class="step-header">

            <div class="step-number">
                1
            </div>

            <div>

                <h2>
                    Choose Service
                </h2>

                <p>
                    Select the service category, treatment,
                    duration and optional add-ons.
                </p>

            </div>

        </div>


        <label class="field-label">
            Category
        </label>


        <div class="grid">

            <div
                class="card category"
                data-cat="Massage"
            >
                Massage
            </div>

            <div
                class="card category"
                data-cat="Package"
            >
                Package
            </div>

            <div
                class="card category"
                data-cat="Promo"
            >
                Promo
            </div>

        </div>


        <label class="field-label">
            Service
        </label>


        <div
            class="grid"
            id="serviceBox"
        >

            <div class="empty-option">
                Select a category first.
            </div>

        </div>


        <div id="serviceDesc"></div>


        <label class="field-label">
            Duration
        </label>


        <div
            class="grid"
            id="durationBox"
        >

            <div class="empty-option">
                Select a service first.
            </div>

        </div>


        <label class="field-label">
            Optional Add-ons
        </label>


        <div
            class="grid"
            id="addonBox"
        >

            <div class="empty-option">
                Select a service first.
            </div>

        </div>

    </section>


    <!-- =====================================================
         STEP 2
         ===================================================== -->

    <section class="box">

        <div class="step-header">

            <div class="step-number">
                2
            </div>

            <div>

                <h2>
                    Schedule & Room
                </h2>

                <p>
                    Select the room, appointment date,
                    number of guests and available time.
                </p>

            </div>

        </div>


        <label class="field-label">
            Room Type
        </label>


        <div class="grid">

            <div
                class="card room"
                data-room="Single Room"
            >
                Single Room

                <div class="small">
                    For individual appointments
                </div>

            </div>


            <div
                class="card room"
                data-room="Couple Room"
            >
                Couple Room

                <div class="small">
                    Automatically sets 2 guests
                </div>

            </div>

        </div>


        <label class="field-label">
            Appointment Date
        </label>


        <input
            type="date"
            id="date"
            required
        >


        <label class="field-label">
            Number of Guests
        </label>


        <input
            type="number"
            name="pax"
            id="pax"
            value="1"
            min="1"
            max="4"
            required
        >


        <div
            class="pax-note"
            id="paxNote"
        >
            Maximum of 4 guests.
        </div>


        <label class="field-label">
            Available Time
        </label>


        <div
            class="grid"
            id="timeBox"
        >

            <div class="empty-option">
                Select an appointment date first.
            </div>

        </div>

    </section>


    <!-- =====================================================
         STEP 3
         ===================================================== -->

    <section class="box">

        <div class="step-header">

            <div class="step-number">
                3
            </div>

            <div>

                <h2>
                    Choose Therapist
                </h2>

                <p>
                    Assign an available therapist or leave
                    the appointment as No Preference.
                </p>

            </div>

        </div>


        <div
            class="grid"
            id="therapistBox"
        >

            <div
                class="card therapist active"
                data-id="0"
                data-name="No Preference"
            >

                No Preference

                <div class="small">
                    Assign later
                </div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         STEP 4
         ===================================================== -->

    <section class="box">

        <div class="step-header">

            <div class="step-number">
                4
            </div>

            <div>

                <h2>
                    Customer Details
                </h2>

                <p>
                    Enter the walk-in customer's contact
                    and payment information.
                </p>

            </div>

        </div>


        <div class="customer-grid">


            <div>

                <label class="field-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="customer_name"
                    placeholder="Customer full name"
                    required
                >

            </div>


            <div>

                <label class="field-label">
                    Phone Number
                </label>

                <input
                    type="text"
                    name="phone"
                    placeholder="09XXXXXXXXX"
                    required
                >

            </div>


            <div>

                <label class="field-label">
                    Payment Method
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


            <div class="full-field">

                <label class="field-label">
                    Notes
                </label>

                <textarea
                    name="notes"
                    placeholder="Optional notes or customer requests..."
                ></textarea>

            </div>


        </div>

    </section>


</div>


<!-- =========================================================
     RIGHT SIDE SUMMARY
     ========================================================= -->

<aside class="summary">


    <div class="summary-label">
        Admin Walk-in
    </div>


    <h3>
        Booking Summary
    </h3>


    <div class="summary-row">

        <span>
            Service
        </span>

        <span id="summaryService">
            —
        </span>

    </div>


    <div class="summary-row">

        <span>
            Duration
        </span>

        <span id="summaryDuration">
            —
        </span>

    </div>


    <div class="summary-row">

        <span>
            Room
        </span>

        <span id="summaryRoom">
            —
        </span>

    </div>


    <div class="summary-row">

        <span>
            Date
        </span>

        <span id="summaryDate">
            —
        </span>

    </div>


    <div class="summary-row">

        <span>
            Time
        </span>

        <span id="summaryTime">
            —
        </span>

    </div>


    <div class="summary-row">

        <span>
            Guests
        </span>

        <span id="summaryPax">
            1
        </span>

    </div>


    <div class="summary-row">

        <span>
            Therapist
        </span>

        <span id="summaryTherapist">
            No Preference
        </span>

    </div>


    <div class="summary-addons">

        <div class="summary-addons-title">
            Add-ons
        </div>

        <div id="summaryAddons">
            None selected
        </div>

    </div>


    <div class="total-box">

        <div>

            <div class="total-label">
                Estimated Total
            </div>

        </div>

        <div
            class="total-price"
            id="summaryTotal"
        >
            ₱0.00
        </div>

    </div>


    <button
        type="submit"
        class="btn"
    >
        Confirm Walk-in Booking
    </button>


    <div class="admin-note">
        Review the booking information before confirming.
    </div>


</aside>


</div>


<!-- =========================================================
     HIDDEN FORM VALUES
     ========================================================= -->

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

<input
    type="hidden"
    name="room_type"
    id="room_type"
>

<input
    type="hidden"
    name="booking_date"
    id="booking_date"
>

<input
    type="hidden"
    name="booking_time"
    id="booking_time"
>

<input
    type="hidden"
    name="therapist"
    id="therapist"
    value="0"
>

<input
    type="hidden"
    name="addons"
    id="addons"
>


</form>

</div>


<script>

/* =========================================================
   ELEMENTS
   ========================================================= */

const serviceBox =
    document.getElementById("serviceBox");

const durationBox =
    document.getElementById("durationBox");

const addonBox =
    document.getElementById("addonBox");

const therapistBox =
    document.getElementById("therapistBox");

const timeBox =
    document.getElementById("timeBox");

const serviceDesc =
    document.getElementById("serviceDesc");


const serviceIdInput =
    document.getElementById("service_id");

const serviceInput =
    document.getElementById("service");

const durationInput =
    document.getElementById("duration");

const priceInput =
    document.getElementById("price");

const roomInput =
    document.getElementById("room_type");

const bookingDateInput =
    document.getElementById("booking_date");

const bookingTimeInput =
    document.getElementById("booking_time");

const therapistInput =
    document.getElementById("therapist");

const addonsInput =
    document.getElementById("addons");

const dateInput =
    document.getElementById("date");

const paxInput =
    document.getElementById("pax");

const paxNote =
    document.getElementById("paxNote");


/* =========================================================
   STATE
   ========================================================= */

let basePrice = 0;

let addonTotal = 0;

let selectedDuration = "";

let selectedDate = "";

let selectedTime = "";


/* =========================================================
   HELPERS
   ========================================================= */

function escapeHTML(value){

    if(
        value === null ||
        value === undefined
    ){
        return "";
    }

    return String(value)

        .replace(/&/g,"&amp;")
        .replace(/</g,"&lt;")
        .replace(/>/g,"&gt;")
        .replace(/"/g,"&quot;")
        .replace(/'/g,"&#039;");

}


function money(value){

    return "₱" +
        Number(value || 0)
        .toLocaleString(
            "en-PH",
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        );

}


function formatTime24(time){

    if(!time){
        return "—";
    }

    const parts =
        time.split(":");

    let hour =
        parseInt(parts[0]);

    const minute =
        parts[1] || "00";

    const ampm =
        hour >= 12
        ? "PM"
        : "AM";

    hour =
        hour % 12 || 12;

    return (
        hour +
        ":" +
        minute +
        " " +
        ampm
    );

}


function formatDate(value){

    if(!value){
        return "—";
    }

    const date =
        new Date(
            value + "T00:00:00"
        );

    return date.toLocaleDateString(
        "en-PH",
        {
            month:"short",
            day:"numeric",
            year:"numeric"
        }
    );

}


/* =========================================================
   TOTAL
   ========================================================= */

function updateTotal(){

    const total =
        Number(basePrice) +
        Number(addonTotal);

    priceInput.value =
        total.toFixed(2);

    document
    .getElementById(
        "summaryTotal"
    )
    .textContent =
        money(total);

}


/* =========================================================
   ADD-ON SUMMARY
   ========================================================= */

function updateAddonSummary(){

    const selected =
        document.querySelectorAll(
            ".addon.active"
        );

    let names = [];

    let stored = [];

    addonTotal = 0;


    selected.forEach(
        item => {

            const name =
                item.dataset.name;

            const addonPrice =
                Number(
                    item.dataset.price || 0
                );

            names.push(
                name +
                " (" +
                money(addonPrice) +
                ")"
            );

            stored.push(name);

            addonTotal +=
                addonPrice;

        }
    );


    addonsInput.value =
        stored.join(", ");


    document
    .getElementById(
        "summaryAddons"
    )
    .innerHTML =
        names.length
        ?
        names
        .map(
            name =>
            escapeHTML(name)
        )
        .join("<br>")
        :
        "None selected";


    updateTotal();

}


/* =========================================================
   RESET AFTER CATEGORY
   ========================================================= */

function resetServiceSelection(){

    serviceIdInput.value = "";
    serviceInput.value = "";
    durationInput.value = "";

    basePrice = 0;
    addonTotal = 0;

    selectedDuration = "";

    serviceDesc.innerHTML = "";

    durationBox.innerHTML =
        '<div class="empty-option">Select a service first.</div>';

    addonBox.innerHTML =
        '<div class="empty-option">Select a service first.</div>';

    document
    .getElementById(
        "summaryService"
    )
    .textContent = "—";

    document
    .getElementById(
        "summaryDuration"
    )
    .textContent = "—";

    document
    .getElementById(
        "summaryAddons"
    )
    .textContent =
        "None selected";

    addonsInput.value = "";

    updateTotal();

}


/* =========================================================
   CATEGORY
   ========================================================= */

document
.querySelectorAll(
    ".category"
)
.forEach(
    category => {

        category.addEventListener(
            "click",
            function(){

                document
                .querySelectorAll(
                    ".category"
                )
                .forEach(
                    item =>
                    item.classList.remove(
                        "active"
                    )
                );

                this.classList.add(
                    "active"
                );


                resetServiceSelection();


                serviceBox.innerHTML =
                    '<div class="empty-option">Loading services...</div>';


                fetch(
                    "../get_services_by_category.php?cat=" +
                    encodeURIComponent(
                        this.dataset.cat
                    )
                )

                .then(
                    response =>
                    response.json()
                )

                .then(
                    data => {

                        serviceBox.innerHTML =
                            "";


                        if(
                            !Array.isArray(data) ||
                            data.length === 0
                        ){

                            serviceBox.innerHTML =
                                '<div class="empty-option">No services available in this category.</div>';

                            return;

                        }


                        data.forEach(
                            service => {

                                const card =
                                    document.createElement(
                                        "div"
                                    );

                                card.className =
                                    "card service";

                                card.dataset.id =
                                    service.id;

                                card.dataset.name =
                                    service.service_name;

                                card.dataset.desc =
                                    service.description || "";

                                card.textContent =
                                    service.service_name;

                                serviceBox
                                .appendChild(
                                    card
                                );

                            }
                        );

                    }
                )

                .catch(
                    () => {

                        serviceBox.innerHTML =
                            '<div class="empty-option">Unable to load services.</div>';

                    }
                );

            }
        );

    }
);


/* =========================================================
   SERVICE
   ========================================================= */

document.addEventListener(
    "click",
    function(e){

        const selected =
            e.target.closest(
                ".service"
            );

        if(!selected){
            return;
        }


        document
        .querySelectorAll(
            ".service"
        )
        .forEach(
            item =>
            item.classList.remove(
                "active"
            )
        );


        selected.classList.add(
            "active"
        );


        serviceIdInput.value =
            selected.dataset.id;

        serviceInput.value =
            selected.dataset.name;


        document
        .getElementById(
            "summaryService"
        )
        .textContent =
            selected.dataset.name;


        if(
            selected.dataset.desc
        ){

            serviceDesc.textContent =
                selected.dataset.desc;

        }else{

            serviceDesc.innerHTML = "";

        }


        basePrice = 0;
        addonTotal = 0;

        durationInput.value = "";
        addonsInput.value = "";

        document
        .getElementById(
            "summaryDuration"
        )
        .textContent = "—";

        document
        .getElementById(
            "summaryAddons"
        )
        .textContent =
            "None selected";

        updateTotal();


        /* DURATION */

        durationBox.innerHTML =
            '<div class="empty-option">Loading durations...</div>';


        fetch(
            "../get_duration.php?id=" +
            encodeURIComponent(
                selected.dataset.id
            )
        )

        .then(
            response =>
            response.json()
        )

        .then(
            data => {

                durationBox.innerHTML =
                    "";


                if(
                    !Array.isArray(data) ||
                    data.length === 0
                ){

                    durationBox.innerHTML =
                        '<div class="empty-option">No duration available.</div>';

                    return;

                }


                data.forEach(
                    item => {

                        const card =
                            document.createElement(
                                "div"
                            );

                        card.className =
                            "card duration";

                        card.dataset.duration =
                            item.duration;

                        card.dataset.price =
                            item.price;

                        card.innerHTML =
                            escapeHTML(
                                item.duration
                            ) +
                            '<div class="small">' +
                            money(item.price) +
                            '</div>';

                        durationBox
                        .appendChild(
                            card
                        );

                    }
                );

            }
        );


        /* ADD-ONS */

        addonBox.innerHTML =
            '<div class="empty-option">Loading add-ons...</div>';


        fetch(
            "../get_addons.php"
        )

        .then(
            response =>
            response.json()
        )

        .then(
            data => {

                addonBox.innerHTML =
                    "";


                if(
                    !Array.isArray(data) ||
                    data.length === 0
                ){

                    addonBox.innerHTML =
                        '<div class="empty-option">No add-ons available.</div>';

                    return;

                }


                data.forEach(
                    addon => {

                        const card =
                            document.createElement(
                                "div"
                            );

                        card.className =
                            "card addon";

                        card.dataset.name =
                            addon.service_name;

                        card.dataset.price =
                            addon.price;


                        card.innerHTML =
                            escapeHTML(
                                addon.service_name
                            ) +

                            '<div class="small">' +

                            money(
                                addon.price
                            ) +

                            (
                                addon.description
                                ?
                                "<br>" +
                                escapeHTML(
                                    addon.description
                                )
                                :
                                ""
                            ) +

                            "</div>";


                        addonBox
                        .appendChild(
                            card
                        );

                    }
                );

            }
        );

    }
);


/* =========================================================
   DURATION
   ========================================================= */

document.addEventListener(
    "click",
    function(e){

        const selected =
            e.target.closest(
                ".duration"
            );

        if(!selected){
            return;
        }


        document
        .querySelectorAll(
            ".duration"
        )
        .forEach(
            item =>
            item.classList.remove(
                "active"
            )
        );


        selected.classList.add(
            "active"
        );


        selectedDuration =
            selected.dataset.duration;

        durationInput.value =
            selectedDuration;

        basePrice =
            Number(
                selected.dataset.price || 0
            );


        document
        .getElementById(
            "summaryDuration"
        )
        .textContent =
            selectedDuration;


        updateTotal();


        /*
         If date/time is already selected,
         refresh therapists because duration
         can affect therapist availability.
        */

        if(
            selectedDate &&
            selectedTime
        ){

            loadTherapists();

        }

    }
);


/* =========================================================
   ADD-ONS
   ========================================================= */

document.addEventListener(
    "click",
    function(e){

        const selected =
            e.target.closest(
                ".addon"
            );

        if(!selected){
            return;
        }


        selected.classList.toggle(
            "active"
        );


        updateAddonSummary();

    }
);


/* =========================================================
   ROOM
   ========================================================= */

document.addEventListener(
    "click",
    function(e){

        const selected =
            e.target.closest(
                ".room"
            );

        if(!selected){
            return;
        }


        document
        .querySelectorAll(
            ".room"
        )
        .forEach(
            item =>
            item.classList.remove(
                "active"
            )
        );


        selected.classList.add(
            "active"
        );


        const room =
            selected.dataset.room;


        roomInput.value =
            room;


        document
        .getElementById(
            "summaryRoom"
        )
        .textContent =
            room;


        if(
            room === "Couple Room"
        ){

            paxInput.value = 2;

            paxInput.readOnly = true;

            paxNote.textContent =
                "Couple Room automatically uses 2 guests.";

        }else{

            paxInput.readOnly = false;

            if(
                Number(
                    paxInput.value
                ) < 1
            ){

                paxInput.value = 1;

            }

            paxNote.textContent =
                "Maximum of 4 guests.";

        }


        document
        .getElementById(
            "summaryPax"
        )
        .textContent =
            paxInput.value;

    }
);


/* =========================================================
   PAX
   ========================================================= */

paxInput.addEventListener(
    "input",
    function(){

        let value =
            Number(
                this.value || 1
            );


        if(value < 1){
            value = 1;
        }

        if(value > 4){
            value = 4;
        }


        this.value = value;


        document
        .getElementById(
            "summaryPax"
        )
        .textContent =
            value;

    }
);


/* =========================================================
   DATE MINIMUM
   ========================================================= */

const today =
    new Date();

const year =
    today.getFullYear();

const month =
    String(
        today.getMonth() + 1
    ).padStart(2,"0");

const day =
    String(
        today.getDate()
    ).padStart(2,"0");

dateInput.min =
    year +
    "-" +
    month +
    "-" +
    day;


/* =========================================================
   DATE / TIME
   ========================================================= */

dateInput.addEventListener(
    "change",
    async function(){

        selectedDate =
            this.value;

        selectedTime = "";

        bookingDateInput.value =
            selectedDate;

        bookingTimeInput.value =
            "";


        document
        .getElementById(
            "summaryDate"
        )
        .textContent =
            formatDate(
                selectedDate
            );


        document
        .getElementById(
            "summaryTime"
        )
        .textContent =
            "—";


        resetTherapist();


        timeBox.innerHTML =
            '<div class="empty-option">Checking available times...</div>';


        let timeHTML = "";


        /*
         Keeping your current 10 AM - 10 PM
         walk-in time range.
        */

        for(
            let hour = 10;
            hour <= 22;
            hour++
        ){

            const rawTime =
                String(hour)
                .padStart(2,"0")
                +
                ":00";


            try{

                const response =
                    await fetch(
                        "../check_slot.php?date=" +
                        encodeURIComponent(
                            selectedDate
                        ) +
                        "&time=" +
                        encodeURIComponent(
                            rawTime
                        )
                    );


                const data =
                    await response.json();


                const available =
                    data.available
                    ? true
                    : false;


                const remaining =
                    data.remaining ??
                    0;


                timeHTML += `

                    <div
                        class="card time ${
                            available
                            ?
                            ""
                            :
                            "dim"
                        }"
                        data-time="${rawTime}"
                    >

                        ${formatTime24(rawTime)}

                        <div class="small">

                            ${
                                available
                                ?
                                remaining +
                                " slot" +
                                (
                                    Number(remaining) === 1
                                    ?
                                    ""
                                    :
                                    "s"
                                ) +
                                " remaining"
                                :
                                "Unavailable"
                            }

                        </div>

                    </div>

                `;

            }catch(error){

                timeHTML += `

                    <div
                        class="card time"
                        data-time="${rawTime}"
                    >

                        ${formatTime24(rawTime)}

                    </div>

                `;

            }

        }


        timeBox.innerHTML =
            timeHTML;

    }
);


/* =========================================================
   TIME
   ========================================================= */

document.addEventListener(
    "click",
    function(e){

        const selected =
            e.target.closest(
                ".time"
            );

        if(
            !selected ||
            selected.classList.contains(
                "dim"
            )
        ){
            return;
        }


        document
        .querySelectorAll(
            ".time"
        )
        .forEach(
            item =>
            item.classList.remove(
                "active"
            )
        );


        selected.classList.add(
            "active"
        );


        selectedTime =
            selected.dataset.time;

        bookingTimeInput.value =
            selectedTime;


        document
        .getElementById(
            "summaryTime"
        )
        .textContent =
            formatTime24(
                selectedTime
            );


        loadTherapists();

    }
);


/* =========================================================
   RESET THERAPIST
   ========================================================= */

function resetTherapist(){

    therapistInput.value = "0";


    document
    .getElementById(
        "summaryTherapist"
    )
    .textContent =
        "No Preference";


    therapistBox.innerHTML = `

        <div
            class="card therapist active"
            data-id="0"
            data-name="No Preference"
        >

            No Preference

            <div class="small">
                Assign later
            </div>

        </div>

    `;

}


/* =========================================================
   LOAD THERAPISTS
   ========================================================= */

function loadTherapists(){

    if(
        !selectedDate ||
        !selectedTime
    ){

        return;

    }


    resetTherapist();


    let url =
        "../get_available_therapists.php?date=" +
        encodeURIComponent(
            selectedDate
        ) +
        "&time=" +
        encodeURIComponent(
            selectedTime
        );


    if(selectedDuration){

        url +=
            "&duration=" +
            encodeURIComponent(
                selectedDuration
            );

    }


    fetch(url)

    .then(
        response =>
        response.json()
    )

    .then(
        data => {

            if(
                !Array.isArray(data)
            ){
                return;
            }


            data.forEach(
                therapist => {

                    const card =
                        document.createElement(
                            "div"
                        );

                    card.className =
                        "card therapist";

                    card.dataset.id =
                        therapist.id;

                    card.dataset.name =
                        therapist.name;

                    card.innerHTML =
                        escapeHTML(
                            therapist.name
                        ) +
                        '<div class="small">Available</div>';


                    therapistBox
                    .appendChild(
                        card
                    );

                }
            );

        }
    );

}


/* =========================================================
   THERAPIST
   ========================================================= */

document.addEventListener(
    "click",
    function(e){

        const selected =
            e.target.closest(
                ".therapist"
            );

        if(!selected){
            return;
        }


        document
        .querySelectorAll(
            ".therapist"
        )
        .forEach(
            item =>
            item.classList.remove(
                "active"
            )
        );


        selected.classList.add(
            "active"
        );


        therapistInput.value =
            selected.dataset.id || 0;


        document
        .getElementById(
            "summaryTherapist"
        )
        .textContent =
            selected.dataset.name ||
            "No Preference";

    }
);


/* =========================================================
   FORM VALIDATION
   ========================================================= */

document
.getElementById(
    "bookingForm"
)
.addEventListener(
    "submit",
    function(e){

        if(
            !serviceIdInput.value ||
            !serviceInput.value
        ){

            e.preventDefault();

            alert(
                "Please select a service."
            );

            return;

        }


        if(
            !durationInput.value ||
            Number(priceInput.value) <= 0
        ){

            e.preventDefault();

            alert(
                "Please select a duration."
            );

            return;

        }


        if(
            !roomInput.value
        ){

            e.preventDefault();

            alert(
                "Please select a room."
            );

            return;

        }


        if(
            !bookingDateInput.value
        ){

            e.preventDefault();

            alert(
                "Please select an appointment date."
            );

            return;

        }


        if(
            !bookingTimeInput.value
        ){

            e.preventDefault();

            alert(
                "Please select an available time."
            );

            return;

        }

    }
);

</script>


</body>
</html>