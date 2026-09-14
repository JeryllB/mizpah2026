<?php

session_start();

include __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if (isset($_POST['submit_booking'])) {

    $name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');

    $service_id = (int)($_POST['service_id'] ?? 0);
    $service = mysqli_real_escape_string($conn, $_POST['service'] ?? '');
    $duration = mysqli_real_escape_string($conn, $_POST['duration'] ?? '');
    $price = (float)($_POST['price'] ?? 0);

    $date = mysqli_real_escape_string($conn, $_POST['booking_date'] ?? '');
    $time = mysqli_real_escape_string($conn, $_POST['booking_time'] ?? '');
    $pax = (int)($_POST['pax'] ?? 1);

    $payment = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'Cash');
    $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

    $therapist_id = (int)($_POST['therapist'] ?? 0);

    $addons = mysqli_real_escape_string($conn, $_POST['addons'] ?? '');
    $room_type = mysqli_real_escape_string($conn, $_POST['room_type'] ?? '');

    if ($room_type === "Couple Room") {
        $pax = 2;
    }

    $insert = mysqli_query($conn, "
        INSERT INTO bookings
        (
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
            status
        )
        VALUES
        (
            $user_id,
            $service_id,
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
            $therapist_id,
            '$room_type',
            'Pending'
        )
    ");

    if ($insert) {
        header("Location: thankyou.php?id=" . mysqli_insert_id($conn));
        exit;
    } else {
        echo "Booking Error: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Poppins, sans-serif;
}

body {
    background: #0b0b0b;
    color: #fff;
}

.header {
    padding: 18px;
    text-align: center;
    color: #D6C29C;
    border-bottom: 1px solid #222;
}

.container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

.box {
    background: #141414;
    border: 1px solid #222;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 12px;
}

h3 {
    font-size: 12px;
    color: #D6C29C;
    margin-bottom: 10px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
}

.card {
    background: #111;
    border: 1px solid #222;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    font-size: 12px;
    transition: .2s;
}

.card:hover {
    border-color: #D6C29C;
}

.card.active {
    border: 2px solid #D6C29C;
}

.card.dim {
    opacity: .35;
    cursor: not-allowed;
    pointer-events: none;
}

.desc {
    font-size: 11px;
    color: #aaa;
    margin-top: 4px;
}

input,
select,
textarea {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    background: #0f0f0f;
    color: #fff;
    border: 1px solid #333;
    border-radius: 10px;
}

textarea {
    resize: vertical;
    min-height: 80px;
}

.btn {
    width: 100%;
    padding: 14px;
    background: #D6C29C;
    color: #111;
    border: none;
    border-radius: 12px;
    font-weight: bold;
    cursor: pointer;
}

.btn:hover {
    background: #c5b187;
}

/* SUMMARY */

.summary {
    background: #111;
    border: 1px solid #333;
    border-radius: 12px;
    padding: 12px;
    font-size: 12px;
    margin-bottom: 10px;
    position: sticky;
    top: 10px;
}

.summary h4 {
    color: #D6C29C;
    margin-bottom: 6px;
}

.time-card {
    background: #111;
    border: 1px solid #222;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
}

.time-card:hover {
    border-color: #D6C29C;
}

.time-card.active {
    border: 2px solid #D6C29C;
}

.time-card.dim {
    opacity: .3;
    pointer-events: none;
}

.small {
    font-size: 11px;
    color: #aaa;
}

/* THERAPIST */

.therapist-choice {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 14px;
}

.therapist-option {
    min-height: 72px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.therapist-option strong {
    color: #fff;
    font-size: 12px;
}

.therapist-option span {
    color: #999;
    font-size: 10px;
}

.therapist-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.therapist {
    min-height: 68px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}

.therapist strong {
    color: #fff;
    font-size: 12px;
}

.therapist.unavailable {
    opacity: .4;
    cursor: not-allowed;
    pointer-events: none;
    border-color: #3a3a3a;
}

.unavailable-label {
    display: block;
    color: #b99f78;
    font-size: 9px;
}

.no-therapist {
    color: #aaa;
    font-size: 11px;
    padding: 10px 0;
    text-align: center;
}

@media (max-width: 600px) {

    .container {
        padding: 12px;
    }

    .therapist-choice,
    .therapist-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}

</style>
</head>

<body>

<div class="header">
    CUSTOMER BOOKING
</div>

<div class="container">

<form method="POST">

<!-- CATEGORY -->

<div class="box">

    <h3>CATEGORY</h3>

    <div class="grid">

        <div class="card category" data-cat="Massage">
            Massage
        </div>

        <div class="card category" data-cat="Package">
            Package
        </div>

        <div class="card category" data-cat="Promo">
            Promo
        </div>

    </div>

</div>


<!-- SERVICE -->

<div class="box">

    <h3>SERVICE</h3>

    <div class="grid" id="serviceBox"></div>

    <div id="serviceDesc" class="small"></div>

</div>


<!-- DURATION -->

<div class="box">

    <h3>DURATION</h3>

    <div class="grid" id="durationBox"></div>

</div>


<!-- ADD-ONS -->

<div class="box">

    <h3>ADD-ONS</h3>

    <div class="grid" id="addonBox"></div>

    <input type="hidden" name="addons" id="addons">

</div>


<!-- ROOM -->

<div class="box">

    <h3>ROOM</h3>

    <div class="grid">

        <div class="card room" data-room="Single Room">
            Single
        </div>

        <div class="card room" data-room="Couple Room">
            Couple
        </div>

    </div>

    <input type="hidden" name="room_type" id="room_type">

</div>


<!-- DATE -->

<div class="box">

    <h3>DATE</h3>

    <input
        type="date"
        id="booking_date"
        name="booking_date"
        required
    >

</div>


<!-- TIME -->

<div class="box">

    <h3>TIME</h3>

    <div class="grid" id="timeBox"></div>

    <input
        type="hidden"
        name="booking_time"
        id="booking_time"
        required
    >

</div>


<!-- THERAPIST -->

<div class="box">

    <h3>THERAPIST</h3>

    <div class="grid therapist-choice" id="therapistBox">

        <div
            class="card therapist-option active"
            data-therapist-option="0"
        >
            <strong>No Preference</strong>
            <span>Any available therapist</span>
        </div>

        <div
            class="card therapist-option"
            data-therapist-option="choose"
        >
            <strong>Choose Therapist</strong>
            <span>Select a specific therapist</span>
        </div>

    </div>

    <div id="therapistList" class="therapist-list"></div>

    <div id="therapistMessage" class="no-therapist">
        Select a date and time first to view therapist availability.
    </div>

    <input
        type="hidden"
        name="therapist"
        id="therapist"
        value="0"
    >

</div>


<!-- CUSTOMER -->

<div class="box">

    <h3>CUSTOMER</h3>

    <input
        name="customer_name"
        placeholder="Full Name"
        required
    >

    <input
        name="phone"
        placeholder="Phone Number"
        required
    >

    <input
        type="number"
        name="pax"
        id="pax"
        value="1"
        min="1"
        required
    >

    <select name="payment_method">

        <option value="Cash">
            Cash
        </option>

        <option value="GCash">
            GCash
        </option>

    </select>

    <textarea
        name="notes"
        placeholder="Notes"
    ></textarea>

</div>


<!-- SUMMARY -->

<div class="summary" id="summaryBox">

    <h4>SUMMARY</h4>

    Service: -<br>
    Duration: -<br>
    Room: -<br>
    Time: -<br>
    Therapist: No Preference

</div>


<button
    class="btn"
    type="submit"
    name="submit_booking"
>
    BOOK NOW
</button>


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
>

</form>

</div>


<script>

/* DOM ELEMENTS */

const summaryBox = document.getElementById('summaryBox');
const serviceDesc = document.getElementById('serviceDesc');

const serviceBox = document.getElementById('serviceBox');
const durationBox = document.getElementById('durationBox');
const addonBox = document.getElementById('addonBox');

const roomTypeInput = document.getElementById('room_type');
const bookingDate = document.getElementById('booking_date');
const bookingTime = document.getElementById('booking_time');

const timeBox = document.getElementById('timeBox');
const therapistBox = document.getElementById('therapistBox');
const therapistList = document.getElementById('therapistList');
const therapistInput = document.getElementById('therapist');
const therapistMessage = document.getElementById('therapistMessage');

const paxInput = document.getElementById('pax');

const serviceIdInput = document.getElementById('service_id');
const serviceInput = document.getElementById('service');
const durationInput = document.getElementById('duration');
const priceInput = document.getElementById('price');
const addonsInput = document.getElementById('addons');


/* SUMMARY */

let summary = {
    service: '-',
    duration: '-',
    room: '-',
    time: '-',
    therapist: 'No Preference'
};

function renderSummary() {

    summaryBox.innerHTML = `
        <h4>SUMMARY</h4>
        Service: ${summary.service}<br>
        Duration: ${summary.duration}<br>
        Room: ${summary.room}<br>
        Time: ${summary.time}<br>
        Therapist: ${summary.therapist}
    `;

}


/* CATEGORY */

document.addEventListener('click', function(e) {

    const category = e.target.closest('.category');

    if (!category) return;

    document.querySelectorAll('.category').forEach(function(item) {
        item.classList.remove('active');
    });

    category.classList.add('active');

    fetch(
        'get_services_by_category.php?cat=' +
        encodeURIComponent(category.dataset.cat)
    )
    .then(response => response.json())
    .then(data => {

        serviceBox.innerHTML = '';

        data.forEach(function(service) {

            serviceBox.innerHTML += `
                <div
                    class="card service"
                    data-id="${service.id}"
                    data-name="${service.service_name}"
                    data-desc="${service.description || ''}"
                >
                    ${service.service_name}
                </div>
            `;

        });

    })
    .catch(error => {
        console.error('Service error:', error);
    });

});


/* SERVICE */

document.addEventListener('click', function(e) {

    const selectedService = e.target.closest('.service');

    if (!selectedService) return;

    document.querySelectorAll('.service').forEach(function(item) {
        item.classList.remove('active');
    });

    selectedService.classList.add('active');

    serviceIdInput.value = selectedService.dataset.id;
    serviceInput.value = selectedService.dataset.name;

    serviceDesc.innerText = selectedService.dataset.desc || '';

    summary.service = selectedService.dataset.name;

    renderSummary();

    fetch(
        'get_duration.php?id=' +
        encodeURIComponent(selectedService.dataset.id)
    )
    .then(response => response.json())
    .then(data => {

        durationBox.innerHTML = '';

        data.forEach(function(item) {

            durationBox.innerHTML += `
                <div
                    class="card duration"
                    data-d="${item.duration}"
                    data-p="${item.price}"
                >
                    ${item.duration}<br>
                    ₱${item.price}
                </div>
            `;

        });

    })
    .catch(error => {
        console.error('Duration error:', error);
    });

    fetch('get_addons.php')
    .then(response => response.json())
    .then(data => {

        addonBox.innerHTML = '';

        data.forEach(function(addon) {

            addonBox.innerHTML += `
                <div
                    class="card addon"
                    data-name="${addon.service_name}"
                    data-price="${addon.price}"
                >
                    ${addon.service_name}<br>
                    ₱${addon.price}

                    <div class="desc">
                        ${addon.description || ''}
                    </div>
                </div>
            `;

        });

    })
    .catch(error => {
        console.error('Add-ons error:', error);
    });

});


/* DURATION */

document.addEventListener('click', function(e) {

    const selectedDuration = e.target.closest('.duration');

    if (!selectedDuration) return;

    document.querySelectorAll('.duration').forEach(function(item) {
        item.classList.remove('active');
    });

    selectedDuration.classList.add('active');

    durationInput.value = selectedDuration.dataset.d;
    priceInput.value = selectedDuration.dataset.p;

    summary.duration = selectedDuration.dataset.d;

    renderSummary();

});


/* ADD-ONS */

document.addEventListener('click', function(e) {

    const selectedAddon = e.target.closest('.addon');

    if (!selectedAddon) return;

    selectedAddon.classList.toggle('active');

    const selectedAddons = [];

    document.querySelectorAll('.addon.active').forEach(function(item) {
        selectedAddons.push(item.dataset.name);
    });

    addonsInput.value = selectedAddons.join(', ');

});


/* ROOM */

document.addEventListener('click', function(e) {

    const selectedRoom = e.target.closest('.room');

    if (!selectedRoom) return;

    document.querySelectorAll('.room').forEach(function(item) {
        item.classList.remove('active');
    });

    selectedRoom.classList.add('active');

    roomTypeInput.value = selectedRoom.dataset.room;

    summary.room = selectedRoom.dataset.room;

    if (selectedRoom.dataset.room === 'Couple Room') {
        paxInput.value = 2;
        paxInput.min = 2;
    } else {
        paxInput.value = 1;
        paxInput.min = 1;
    }

    renderSummary();

});


/* THERAPIST OPTION */

document.addEventListener('click', function(e) {

    const option = e.target.closest('.therapist-option');

    if (!option) return;

    document.querySelectorAll('.therapist-option').forEach(function(item) {
        item.classList.remove('active');
    });

    option.classList.add('active');

    const selectedOption = option.dataset.therapistOption;

    if (selectedOption === '0') {

        therapistInput.value = '0';
        therapistList.innerHTML = '';

        summary.therapist = 'No Preference';

        therapistMessage.innerText =
            'No Preference selected.';

        renderSummary();

        return;
    }

    if (selectedOption === 'choose') {

        if (!bookingDate.value || !bookingTime.value) {

            therapistMessage.innerText =
                'Please select a date and time first.';

            return;
        }

        loadTherapists(
            bookingDate.value,
            bookingTime.value
        );

    }

});


/* LOAD THERAPISTS */

function loadTherapists(date, time) {

    therapistList.innerHTML = '';
    therapistMessage.innerText = 'Loading therapists...';

    fetch(
        'get_available_therapists.php?date=' +
        encodeURIComponent(date) +
        '&time=' +
        encodeURIComponent(time)
    )
    .then(response => response.json())
    .then(data => {

        therapistMessage.innerText = '';

        if (!Array.isArray(data) || data.length === 0) {

            therapistMessage.innerText = 'No therapists found.';
            return;

        }

        data.forEach(function(therapist) {

            const therapistId = therapist.id;
            const therapistName = therapist.name || 'Unnamed Therapist';
            const isAvailable = therapist.available !== false;

            const card = document.createElement('div');

            card.className = 'card therapist';

            card.dataset.therapistId = therapistId;
            card.dataset.therapistName = therapistName;

            if (!isAvailable) {

                card.classList.add('unavailable');

                card.innerHTML = `
                    <strong>${therapistName}</strong>
                    <span class="unavailable-label">
                        Booked / Unavailable
                    </span>
                `;

            } else {

                card.innerHTML = `
                    <strong>${therapistName}</strong>
                    <span class="small">Available</span>
                `;

            }

            therapistList.appendChild(card);

        });

    })
    .catch(error => {

        console.error('Therapist error:', error);

        therapistMessage.innerText =
            'Unable to load therapists. Please try again.';

    });

}


/* DATE CHANGE */

bookingDate.addEventListener('change', async function() {

    timeBox.innerHTML = '';

    therapistBox.innerHTML = `
        <div
            class="card therapist-option active"
            data-therapist-option="0"
        >
            <strong>No Preference</strong>
            <span>Any available therapist</span>
        </div>

        <div
            class="card therapist-option"
            data-therapist-option="choose"
        >
            <strong>Choose Therapist</strong>
            <span>Select a specific therapist</span>
        </div>
    `;

    therapistList.innerHTML = '';

    therapistInput.value = '0';
    summary.therapist = 'No Preference';

    bookingTime.value = '';

    therapistMessage.innerText =
        'Select a time to view therapist availability.';

    renderSummary();

    for (let h = 10; h <= 22; h++) {

        try {

            const response = await fetch(
                'check_slot.php?date=' +
                encodeURIComponent(bookingDate.value) +
                '&time=' +
                encodeURIComponent(h + ':00')
            );

            const data = await response.json();

            const timeCard = document.createElement('div');

            timeCard.className = 'time-card';

            timeCard.innerHTML =
                (h % 12 || 12) +
                ':00 ' +
                (h >= 12 ? 'PM' : 'AM') +
                `<div class="small">
                    ${data.remaining ?? 0} slot
                </div>`;

            if (!data.available) {
                timeCard.classList.add('dim');
            }

            timeCard.addEventListener('click', function() {

                bookingTime.value = h + ':00';

                summary.time =
                    (h % 12 || 12) +
                    ':00 ' +
                    (h >= 12 ? 'PM' : 'AM');

                document.querySelectorAll('.time-card').forEach(function(item) {
                    item.classList.remove('active');
                });

                timeCard.classList.add('active');

                therapistInput.value = '0';
                summary.therapist = 'No Preference';

                therapistBox.innerHTML = `
                    <div
                        class="card therapist-option active"
                        data-therapist-option="0"
                    >
                        <strong>No Preference</strong>
                        <span>Any available therapist</span>
                    </div>

                    <div
                        class="card therapist-option"
                        data-therapist-option="choose"
                    >
                        <strong>Choose Therapist</strong>
                        <span>Select a specific therapist</span>
                    </div>
                `;

                therapistList.innerHTML = '';

                therapistMessage.innerText =
                    'Click Choose Therapist to view available and booked therapists.';

                renderSummary();

            });

            timeBox.appendChild(timeCard);

        } catch (error) {

            console.error('Time slot error:', error);

        }

    }

});


/* THERAPIST SELECTION */

document.addEventListener('click', function(e) {

    const selectedTherapist = e.target.closest('.therapist');

    if (!selectedTherapist) return;

    if (
        selectedTherapist.classList.contains('unavailable') ||
        selectedTherapist.classList.contains('dim')
    ) {
        return;
    }

    document.querySelectorAll('.therapist').forEach(function(item) {
        item.classList.remove('active');
    });

    selectedTherapist.classList.add('active');

    const therapistId = selectedTherapist.dataset.therapistId;
    const therapistName = selectedTherapist.dataset.therapistName;

    therapistInput.value = therapistId;

    summary.therapist = therapistName;

    renderSummary();

});

</script>

</body>
</html>