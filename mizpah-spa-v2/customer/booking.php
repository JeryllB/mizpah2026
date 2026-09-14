<?php

session_start();

include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if (isset($_POST['submit_booking'])) {

    $name = $_POST['customer_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $service = $_POST['service'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $price = $_POST['price'] ?? 0;
    $date = $_POST['booking_date'] ?? '';
    $time = $_POST['booking_time'] ?? '';
    $pax = (int)($_POST['pax'] ?? 1);
    $payment = $_POST['payment_method'] ?? 'Cash';
    $notes = $_POST['notes'] ?? '';
    $therapist = $_POST['therapist'] ?? '';
    $addons = $_POST['addons'] ?? '';
    $room_type = $_POST['room_type'] ?? '';

    /*
       COUPLE ROOM AUTOMATIC PAX
    */
    if ($room_type === "Couple Room") {
        $pax = 2;
    }

    $name = mysqli_real_escape_string($conn, $name);
    $phone = mysqli_real_escape_string($conn, $phone);
    $service_id = (int)$service_id;
    $service = mysqli_real_escape_string($conn, $service);
    $duration = mysqli_real_escape_string($conn, $duration);
    $price = (float)$price;
    $date = mysqli_real_escape_string($conn, $date);
    $time = mysqli_real_escape_string($conn, $time);
    $payment = mysqli_real_escape_string($conn, $payment);
    $notes = mysqli_real_escape_string($conn, $notes);
    $therapist = (int)$therapist;
    $addons = mysqli_real_escape_string($conn, $addons);
    $room_type = mysqli_real_escape_string($conn, $room_type);

    $sql = "INSERT INTO bookings
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
        '$user_id',
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
        '$therapist',
        '$room_type',
        'Pending'
    )";

    if (mysqli_query($conn, $sql)) {

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

<title>Customer Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Poppins;
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

h4 {
    color: #D6C29C;
    margin-bottom: 8px;
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
    border-color: #8d7650;
}

.card.active {
    border: 2px solid #D6C29C;
}

.card.dim {
    opacity: .3;
    pointer-events: none;
}

.card.unavailable {
    opacity: .35;
    cursor: not-allowed;
    border-color: #422;
}

.desc {
    font-size: 11px;
    color: #aaa;
    margin-top: 4px;
}

.available-text {
    color: #9fcf9f;
}

.unavailable-text {
    color: #d58d8d;
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

input:read-only {
    opacity: .7;
}

textarea {
    min-height: 80px;
    resize: vertical;
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
    background: #c5af87;
}

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

.time-card {
    background: #111;
    border: 1px solid #222;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    font-size: 12px;
}

.time-card:hover {
    border-color: #8d7650;
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

.message {
    font-size: 11px;
    color: #aaa;
    margin-top: 8px;
}

</style>

</head>

<body>

<div class="header">
    CUSTOMER BOOKING
</div>

<div class="container">

<form method="POST" id="bookingForm">

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

        <div id="serviceDesc" class="small">
            Select service to view description
        </div>

    </div>


    <!-- DURATION -->

    <div class="box">

        <h3>DURATION</h3>

        <div class="grid" id="durationBox"></div>

    </div>


    <!-- ADDONS -->

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
        >

    </div>


    <!-- THERAPIST -->

    <div class="box">

        <h3>THERAPIST</h3>

        <div
            class="grid"
            id="therapistBox"
        ></div>

        <div
            id="therapistMessage"
            class="message"
        >
            Select date and time first.
        </div>

        <input
            type="hidden"
            name="therapist"
            id="therapist"
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
            value="1"
            min="1"
            max="6"
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
        Therapist: -

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

let summary = {
    service: '-',
    duration: '-',
    room: '-',
    time: '-',
    therapist: '-'
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

document.addEventListener('click', e => {

    let c = e.target.closest('.category');

    if (!c) return;

    document.querySelectorAll('.category').forEach(x => {
        x.classList.remove('active');
    });

    c.classList.add('active');

    fetch('../get_services_by_category.php?cat=' + encodeURIComponent(c.dataset.cat))
        .then(r => r.json())
        .then(d => {

            serviceBox.innerHTML = '';

            d.forEach(s => {

                serviceBox.innerHTML += `
                    <div
                        class="card service"
                        data-id="${s.id}"
                        data-name="${s.service_name}"
                        data-desc="${s.description || ''}"
                    >
                        ${s.service_name}
                    </div>
                `;

            });

        })
        .catch(error => {
            console.error('Service error:', error);
        });

});


/* SERVICE */

document.addEventListener('click', e => {

    let s = e.target.closest('.service');

    if (!s) return;

    service_id.value = s.dataset.id;
    service.value = s.dataset.name;

    serviceDesc.innerText =
        s.dataset.desc || "No description available";

    summary.service = s.dataset.name;
    renderSummary();


    /* DURATION */

    fetch('../get_duration.php?id=' + encodeURIComponent(s.dataset.id))
        .then(r => r.json())
        .then(d => {

            durationBox.innerHTML = '';

            d.forEach(x => {

                durationBox.innerHTML += `
                    <div
                        class="card duration"
                        data-d="${x.duration}"
                        data-p="${x.price}"
                    >
                        ${x.duration}<br>
                        ₱${x.price}
                    </div>
                `;

            });

        });


    /* ADDONS */

    fetch('../get_addons.php')
        .then(r => r.json())
        .then(d => {

            addonBox.innerHTML = '';

            d.forEach(a => {

                addonBox.innerHTML += `
                    <div
                        class="card addon"
                        data-name="${a.service_name}"
                        data-price="${a.price}"
                    >
                        ${a.service_name}<br>
                        ₱${a.price}

                        <div class="desc">
                            ${a.description || ''}
                        </div>
                    </div>
                `;

            });

        });

});


/* DURATION */

document.addEventListener('click', e => {

    let d = e.target.closest('.duration');

    if (!d) return;

    document.querySelectorAll('.duration').forEach(x => {
        x.classList.remove('active');
    });

    d.classList.add('active');

    duration.value = d.dataset.d;
    price.value = d.dataset.p;

    summary.duration = d.dataset.d;
    renderSummary();

});


/* ADDONS */

document.addEventListener('click', e => {

    let a = e.target.closest('.addon');

    if (!a) return;

    a.classList.toggle('active');

    let arr = [];

    document.querySelectorAll('.addon.active').forEach(x => {
        arr.push(x.dataset.name);
    });

    addons.value = arr.join(', ');

});


/* ROOM */

document.addEventListener('click', e => {

    let r = e.target.closest('.room');

    if (!r) return;

    document.querySelectorAll('.room').forEach(x => {
        x.classList.remove('active');
    });

    r.classList.add('active');

    room_type.value = r.dataset.room;

    summary.room = r.dataset.room;
    renderSummary();

    let paxInput = document.querySelector('[name="pax"]');

    if (r.dataset.room === "Couple Room") {

        paxInput.value = 2;
        paxInput.readOnly = true;

    } else {

        paxInput.value = 1;
        paxInput.readOnly = false;

    }

});


/* RESET THERAPIST DISPLAY */

function resetTherapistDisplay() {

    therapistBox.innerHTML = '';

    therapistMessage.innerText =
        'Select a time to view available therapists.';

    therapist.value = '';

    summary.therapist = '-';

    renderSummary();

}


/* LOAD THERAPISTS */

function loadTherapists(selectedDate, selectedTime) {

    therapistBox.innerHTML = '';

    therapistMessage.innerText =
        'Loading therapists...';

    therapist.value = '';

    summary.therapist = '-';
    renderSummary();

    let selectedDuration = duration.value || '1 Hour';

    fetch(
        'get_available_therapists.php?date=' +
        encodeURIComponent(selectedDate) +
        '&time=' +
        encodeURIComponent(selectedTime) +
        '&duration=' +
        encodeURIComponent(selectedDuration)
    )
    .then(r => r.json())
    .then(data => {

        therapistBox.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {

            therapistMessage.innerText =
                'No therapists found.';

            return;

        }

        therapistMessage.innerText =
            'Choose an available therapist.';

        data.forEach(t => {

            let therapistName = t.name ?? t;
            let therapistId = t.id ?? '';
            let isAvailable =
                t.available === undefined ? true : Boolean(t.available);

            let card = document.createElement('div');

            card.className = 'card therapist';

            card.dataset.id = therapistId;
            card.dataset.name = therapistName;

            card.innerHTML = `
                <strong>${therapistName}</strong>
                <div class="desc ${
                    isAvailable
                        ? 'available-text'
                        : 'unavailable-text'
                }">
                    ${
                        isAvailable
                            ? 'Available'
                            : 'Booked / Unavailable'
                    }
                </div>
            `;

            if (!isAvailable) {

                card.classList.add('unavailable');
                card.style.pointerEvents = 'none';

            } else {

                card.onclick = () => {

                    document.querySelectorAll('.therapist')
                        .forEach(x => x.classList.remove('active'));

                    card.classList.add('active');

                    /*
                       Save therapist ID to therapist_id column.
                    */

                    therapist.value = therapistId;

                    summary.therapist = therapistName;

                    renderSummary();

                };

            }

            therapistBox.appendChild(card);

        });

    })
    .catch(error => {

        console.error('Therapist error:', error);

        therapistBox.innerHTML = '';

        therapistMessage.innerText =
            'Unable to load therapists.';

    });

}


/* DATE + TIME */

booking_date.onchange = async () => {

    timeBox.innerHTML = '';

    resetTherapistDisplay();

    let selectedDate = booking_date.value;

    if (!selectedDate) return;

    for (let h = 10; h <= 22; h++) {

        let timeValue = h + ':00';

        let res = await fetch(
            '../check_slot.php?date=' +
            encodeURIComponent(selectedDate) +
            '&time=' +
            encodeURIComponent(timeValue)
        );

        let data = await res.json();

        let div = document.createElement('div');

        div.className = 'time-card';

        div.innerHTML =
            (h % 12 || 12) +
            ':00 ' +
            (h >= 12 ? 'PM' : 'AM') +
            `<div class="small">${data.remaining} slot</div>`;

        if (!data.available) {

            div.classList.add('dim');

        } else {

            div.onclick = () => {

                document.querySelectorAll('.time-card').forEach(x => {
                    x.classList.remove('active');
                });

                div.classList.add('active');

                booking_time.value = timeValue;

                summary.time = timeValue;
                summary.therapist = '-';

                renderSummary();

                loadTherapists(
                    selectedDate,
                    timeValue
                );

            };

        }

        timeBox.appendChild(div);

    }

};


/* CLEAR THERAPIST WHEN DURATION CHANGES */

document.addEventListener('click', e => {

    let d = e.target.closest('.duration');

    if (!d) return;

    if (booking_date.value && booking_time.value) {

        loadTherapists(
            booking_date.value,
            booking_time.value
        );

    }

});


/* PREVENT SUBMIT WITHOUT REQUIRED SELECTIONS */

document.getElementById('bookingForm').addEventListener('submit', e => {

    if (!service_id.value) {
        alert('Please select a service first.');
        e.preventDefault();
        return;
    }

    if (!duration.value) {
        alert('Please select a duration first.');
        e.preventDefault();
        return;
    }

    if (!room_type.value) {
        alert('Please select a room first.');
        e.preventDefault();
        return;
    }

    if (!booking_date.value || !booking_time.value) {
        alert('Please select date and time first.');
        e.preventDefault();
        return;
    }

});

</script>

</body>
</html>