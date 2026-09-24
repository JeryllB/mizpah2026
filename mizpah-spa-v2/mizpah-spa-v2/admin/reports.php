<?php
session_start();
include '../includes/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

/* ================= FILTERS ================= */
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE status IN ('Completed','Confirmed') AND price > 0";

if(!empty($from) && !empty($to)){
    $where .= " AND booking_date BETWEEN '$from' AND '$to'";
}

if(!empty($status)){
    $where .= " AND status = '$status'";
}

/* TOTAL SALES */
$res = mysqli_query($conn,"
SELECT IFNULL(SUM(price * pax),0) as total
FROM bookings
$where
");

$totalSales = mysqli_fetch_assoc($res)['total'] ?? 0;

/* PAYMENT */
$payment = mysqli_query($conn,"
SELECT payment_method,
IFNULL(SUM(price * pax),0) as total
FROM bookings
$where
GROUP BY payment_method
");

/* SERVICES */
$services = mysqli_query($conn,"
SELECT service, COUNT(*) as total
FROM bookings
$where
AND service != ''
GROUP BY service
ORDER BY total DESC
LIMIT 5
");

/* GRAPH */
$labels = [];
$data = [];

$daily = mysqli_query($conn,"
SELECT booking_date,
IFNULL(SUM(price * pax),0) as total
FROM bookings
$where
GROUP BY booking_date
ORDER BY booking_date ASC
");

if($daily && mysqli_num_rows($daily) > 0){
    while($row = mysqli_fetch_assoc($daily)){
        $labels[] = date("M d", strtotime($row['booking_date']));
        $data[] = (float)$row['total'];
    }
}else{
    $labels = ["No Data"];
    $data = [0];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Report</title>

<link rel="stylesheet" href="../assets/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
margin:0;
font-family:Poppins, sans-serif;
background:#0b0b0b;
color:#fff;
font-weight:300;
}

.main{
margin-left:250px;
padding:25px;
}

.card{
background:#161616;
border:1px solid #222;
border-radius:14px;
padding:20px;
margin-bottom:15px;
}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:15px;
}

h2{
color:#D6C29C;
font-size:26px;
font-weight:500;
margin-bottom:18px;
}

.title{
color:#D6C29C;
font-size:17px;
font-weight:500;
margin-bottom:12px;
}

.value{
font-size:24px;
font-weight:400;
}

p{
color:#ddd;
font-size:12px;
font-weight:300 !important;
margin:7px 0;
}

.chart-box{
height:320px;
}

/* ================= FILTER FIXED ALIGNMENT ================= */
.filter-box{
display:flex;
gap:10px;
flex-wrap:wrap;
align-items:center;
margin-bottom:15px;
}

/* equal height + clean look */
.filter-box input,
.filter-box select{
padding:10px 12px;
border-radius:8px;
border:1px solid #333;
background:#111;
color:#fff;
font-size:13px;
min-height:38px;
outline:none;
}

/* buttons aligned same height */
.filter-box button{
padding:10px 14px;
border-radius:8px;
border:none;
background:#D6C29C;
color:#111;
font-weight:600;
cursor:pointer;
font-size:13px;
min-height:38px;
display:flex;
align-items:center;
justify-content:center;
}

/* print button */
.print-btn{
background:#444 !important;
color:#fff !important;
}

/* mobile fix */
@media(max-width:600px){
.filter-box{
flex-direction:column;
align-items:stretch;
}

.filter-box button{
width:100%;
}
}
</style>
</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main">

<h2>Sales Report</h2>

<!-- FILTER -->
<form method="GET" class="filter-box">

    <input type="date" name="from" value="<?= $from ?>">
    <input type="date" name="to" value="<?= $to ?>">

    <select name="status">
        <option value="">All Status</option>
        <option value="Pending" <?= $status=='Pending'?'selected':'' ?>>Pending</option>
        <option value="Confirmed" <?= $status=='Confirmed'?'selected':'' ?>>Confirmed</option>
        <option value="Completed" <?= $status=='Completed'?'selected':'' ?>>Completed</option>
    </select>

    <button type="submit">Filter</button>

    <button type="button" class="print-btn" onclick="window.print()">Print</button>

</form>

<!-- TOTAL -->
<div class="card">
<div class="title">Total Revenue</div>
<div class="value">₱<?= number_format($totalSales,2) ?></div>
</div>

<div class="grid">

<!-- PAYMENT -->
<div class="card">
<div class="title">Payment Breakdown</div>

<?php if($payment && mysqli_num_rows($payment)>0): ?>
<?php while($p=mysqli_fetch_assoc($payment)): ?>
<p><?= $p['payment_method'] ?> — ₱<?= number_format($p['total'],2) ?></p>
<?php endwhile; ?>
<?php else: ?>
<p>No payment records</p>
<?php endif; ?>

</div>

<!-- SERVICES -->
<div class="card">
<div class="title">Top Services</div>

<?php if($services && mysqli_num_rows($services)>0): ?>
<?php while($s=mysqli_fetch_assoc($services)): ?>
<p><?= $s['service'] ?> — <?= $s['total'] ?> bookings</p>
<?php endwhile; ?>
<?php else: ?>
<p>No service records</p>
<?php endif; ?>

</div>

</div>

<!-- GRAPH -->
<div class="card">
<div class="title">Daily Sales</div>
<div class="chart-box">
<canvas id="chart"></canvas>
</div>
</div>

</div>

<script>
const labels = <?= json_encode($labels) ?>;
const data = <?= json_encode($data) ?>;

new Chart(document.getElementById("chart"),{
type:'line',
data:{
labels:labels,
datasets:[{
label:'Sales ₱',
data:data,
borderColor:'#D6C29C',
backgroundColor:'rgba(214,194,156,0.12)',
fill:true,
tension:0.4,
borderWidth:2,
pointRadius:3
}]
},
options:{
responsive:true,
maintainAspectRatio:false
}
});
</script>

</body>
</html>