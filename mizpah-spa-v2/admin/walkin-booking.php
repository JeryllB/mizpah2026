<?php
session_start();
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Walk-in Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}
body{background:#0b0b0b;color:#fff;}

.header{
padding:15px;
text-align:center;
color:#D6C29C;
border-bottom:1px solid #222;
}

.container{
max-width:1000px;
margin:auto;
padding:20px;
display:grid;
gap:12px;
}

.box{
background:#141414;
border:1px solid #222;
border-radius:14px;
padding:15px;
}

h3{font-size:12px;color:#D6C29C;margin-bottom:10px;}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
gap:10px;
}

.card{
background:#111;
border:1px solid #222;
border-radius:12px;
padding:12px;
text-align:center;
cursor:pointer;
font-size:12px;
transition:.2s;
}

.card.active{
border:2px solid #D6C29C;
transform:scale(1.03);
}

.card.dim{
opacity:.3;
pointer-events:none;
}

.small{font-size:11px;color:#aaa;}

input,select,textarea{
width:100%;
padding:10px;
margin-top:6px;
background:#0f0f0f;
color:#fff;
border:1px solid #333;
border-radius:10px;
}

.btn{
width:100%;
padding:14px;
background:#D6C29C;
border:none;
border-radius:12px;
font-weight:bold;
cursor:pointer;
}

.summary{
position:sticky;
top:10px;
background:#111;
border:1px solid #333;
padding:12px;
border-radius:12px;
font-size:12px;
}

.summary h4{color:#D6C29C;margin-bottom:6px;}

#serviceDesc{
font-size:12px;
color:#bbb;
margin-top:8px;
line-height:1.4;
}
</style>
</head>

<body>

<div class="header">WALK-IN BOOKING (ADMIN)</div>

<div class="container">

<form method="POST" action="walkin-save.php">

<!-- SUMMARY -->
<div class="summary" id="summaryBox">
<h4>BOOKING SUMMARY</h4>
Service: -<br>
Duration: -<br>
Room: -<br>
Time: -<br>
Therapist: -
</div>

<!-- CATEGORY -->
<div class="box">
<h3>CATEGORY</h3>
<div class="grid">
<div class="card category" data-cat="Massage">Massage</div>
<div class="card category" data-cat="Package">Package</div>
<div class="card category" data-cat="Promo">Promo</div>
</div>
</div>

<!-- SERVICE -->
<div class="box">
<h3>SERVICE</h3>
<div class="grid" id="serviceBox"></div>
<div id="serviceDesc"></div>
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
</div>

<!-- ROOM -->
<div class="box">
<h3>ROOM</h3>
<div class="grid">
<div class="card room" data-room="Single Room">Single</div>
<div class="card room" data-room="Couple Room">Couple</div>
</div>
</div>

<!-- BEDS -->
<div class="box">
<h3>BEDS</h3>
<div class="grid">
<div class="card bed" data-bed="1">1</div>
<div class="card bed" data-bed="2">2</div>
<div class="card bed" data-bed="3">3</div>
<div class="card bed" data-bed="4">4</div>
</div>
</div>

<!-- DATE / TIME -->
<div class="box">
<h3>DATE</h3>
<input type="date" id="date">

<h3>TIME</h3>
<div class="grid" id="timeBox"></div>
</div>

<!-- THERAPIST -->
<div class="box">
<h3>THERAPIST</h3>
<div class="grid" id="therapistBox"></div>
</div>

<!-- CUSTOMER -->
<div class="box">
<h3>CUSTOMER INFO</h3>

<input name="customer_name" placeholder="Full Name" required>
<input name="phone" placeholder="Phone Number" required>
<input type="number" name="pax" value="1" min="1" max="4">

<select name="payment_method">
<option>Cash</option>
<option>GCash</option>
</select>

<textarea name="notes" placeholder="Notes"></textarea>
</div>

<button class="btn">CONFIRM BOOKING</button>

<!-- hidden -->
<input type="hidden" name="service" id="service">
<input type="hidden" name="duration" id="duration">
<input type="hidden" name="price" id="price">
<input type="hidden" name="room_type" id="room_type">
<input type="hidden" name="beds" id="beds">
<input type="hidden" name="booking_date" id="booking_date">
<input type="hidden" name="booking_time" id="booking_time">
<input type="hidden" name="therapist" id="therapist">
<input type="hidden" name="addons" id="addons">

</form>

</div>

<script>

const summaryBox = document.getElementById("summaryBox");
const serviceDesc = document.getElementById("serviceDesc");

let state = {
service:"-",
duration:"-",
room:"-",
time:"-",
therapist:"-"
};

function updateSummary(){
summaryBox.innerHTML=`
<h4>BOOKING SUMMARY</h4>
Service: ${state.service}<br>
Duration: ${state.duration}<br>
Room: ${state.room}<br>
Time: ${state.time}<br>
Therapist: ${state.therapist}
`;
}

/* CATEGORY */
document.querySelectorAll('.category').forEach(c=>{
c.onclick=()=>{
fetch('../get_services_by_category.php?cat='+c.dataset.cat)
.then(r=>r.json())
.then(d=>{
serviceBox.innerHTML="";
d.forEach(s=>{
serviceBox.innerHTML+=`
<div class="card service"
data-id="${s.id}"
data-name="${s.service_name}"
data-desc="${s.description}">
${s.service_name}
</div>`;
});
});
};
});

/* SERVICE */
document.addEventListener('click',e=>{
let s=e.target.closest('.service');
if(!s) return;

document.querySelectorAll('.service').forEach(x=>x.classList.remove('active'));
s.classList.add('active');

service.value = s.dataset.name;
state.service = s.dataset.name;

/* DESCRIPTION FIXED */
serviceDesc.innerHTML = s.dataset.desc ? 
`<div class="small">${s.dataset.desc}</div>` : "";

updateSummary();

/* duration */
fetch('../get_duration.php?id='+s.dataset.id)
.then(r=>r.json())
.then(d=>{
durationBox.innerHTML="";
d.forEach(x=>{
durationBox.innerHTML+=`
<div class="card duration"
data-d="${x.duration}"
data-p="${x.price}">
${x.duration}<br>₱${x.price}
</div>`;
});
});

/* addons */
fetch('../get_addons.php')
.then(r=>r.json())
.then(d=>{
addonBox.innerHTML="";
d.forEach(a=>{
addonBox.innerHTML+=`
<div class="card addon"
data-name="${a.service_name}"
data-price="${a.price}">
${a.service_name}<br>₱${a.price}
<div class="small">${a.description||""}</div>
</div>`;
});
});
});

/* DURATION */
document.addEventListener('click',e=>{
let d=e.target.closest('.duration');
if(!d) return;

document.querySelectorAll('.duration').forEach(x=>x.classList.remove('active'));
d.classList.add('active');

duration.value=d.dataset.d;
price.value=d.dataset.p;

state.duration=d.dataset.d;
updateSummary();
});

/* ADDONS */
document.addEventListener('click',e=>{
let a=e.target.closest('.addon');
if(!a) return;

a.classList.toggle('active');

let arr=[];
document.querySelectorAll('.addon.active').forEach(x=>{
arr.push(x.dataset.name+" ₱"+x.dataset.price);
});

addons.value = arr.join(", ");
});

/* ROOM + BED FIX (COUPLE LOCK) */
document.addEventListener('click',e=>{
let r=e.target.closest('.room');
if(!r) return;

document.querySelectorAll('.room').forEach(x=>x.classList.remove('active'));
r.classList.add('active');

room_type.value = r.dataset.room;
state.room = r.dataset.room;

if(r.dataset.room === "Couple Room"){

beds.value = 2;
document.querySelector('[name="pax"]').value = 2;

/* LOCK BEDS */
document.querySelectorAll('.bed').forEach(b=>{
b.classList.add('dim');
b.classList.remove('active');
});

document.querySelector('.bed[data-bed="2"]').classList.add('active');

}else{

document.querySelectorAll('.bed').forEach(b=>{
b.classList.remove('dim');
});
}
updateSummary();
});

/* BEDS */
document.addEventListener('click',e=>{
let b=e.target.closest('.bed');
if(!b) return;

if(b.classList.contains('dim')) return;

document.querySelectorAll('.bed').forEach(x=>x.classList.remove('active'));
b.classList.add('active');

beds.value=b.dataset.bed;
});

/* TIME AM/PM + SLOT */
date.onchange=async ()=>{
booking_date.value=date.value;

timeBox.innerHTML="";
therapistBox.innerHTML="";

for(let h=10;h<=22;h++){

let res=await fetch('../check_slot.php?date='+date.value+'&time='+h+':00');
let data=await res.json();

let ampm = h>=12 ? "PM":"AM";
let hour = h%12 || 12;

let div=document.createElement("div");
div.className="card time";
div.innerHTML=`
${hour}:00 ${ampm}
<div class="small">${data.remaining} slot</div>
`;

if(!data.available) div.classList.add("dim");

div.onclick=()=>{
document.querySelectorAll('.time').forEach(x=>x.classList.remove('active'));
div.classList.add('active');

booking_time.value=h+":00";
state.time=h+":00";
updateSummary();

/* therapist */
fetch('../get_available_therapists.php?date='+date.value+'&time='+h+':00')
.then(r=>r.json())
.then(d=>{
therapistBox.innerHTML="";
d.forEach(t=>{
therapistBox.innerHTML+=`<div class="card therapist">${t}</div>`;
});
});
};

timeBox.appendChild(div);
}
};

/* THERAPIST */
document.addEventListener('click',e=>{
let t=e.target.closest('.therapist');
if(!t) return;

document.querySelectorAll('.therapist').forEach(x=>x.classList.remove('active'));
t.classList.add('active');

therapist.value=t.innerText;
state.therapist=t.innerText;
updateSummary();
});

</script>

</body>
</html>