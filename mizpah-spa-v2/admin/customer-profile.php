<?php
session_start();
include '../includes/db.php';

/* ================= SECURITY CHECK ================= */
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: ../login.php");
    exit;
}

$role = strtolower($_SESSION['role']);

if($role !== 'admin'){
    header("Location: ../login.php");
    exit;
}

/* ================= CUSTOMER ID ================= */
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: users.php");
    exit;
}

$customer_id = intval($_GET['id']);

/* ================= GET CUSTOMER + WELLNESS INFO ================= */
$query = mysqli_query($conn, "
    SELECT
        u.id,
        u.name,
        u.email,
        u.role,
        u.created_at,

        w.allergies,
        w.skin_sensitivity,
        w.medical_conditions,
        w.body_pain_injuries,
        w.pregnancy_status,
        w.preferred_pressure,
        w.special_requests,
        w.emergency_contact_name,
        w.emergency_contact_number,

        (
            SELECT b.phone
            FROM bookings b
            WHERE b.user_id = u.id
            AND b.phone IS NOT NULL
            AND b.phone != ''
            ORDER BY b.id DESC
            LIMIT 1
        ) AS phone

    FROM users u

    LEFT JOIN wellness_profiles w
        ON u.id = w.user_id

    WHERE u.id = '$customer_id'
    AND u.role = 'customer'

    LIMIT 1
");

$customer = mysqli_fetch_assoc($query);

if(!$customer){
    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Customer Profile</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<style>

body{
    background:#0b0b0b;
    color:#fff;
    font-family:Poppins,sans-serif;
}

.main{
    margin-left:250px;
    padding:25px;
}

h2{
    color:#D6C29C;
    margin-bottom:20px;
}

.section{
    background:#151515;
    border:1px solid #292929;
    border-radius:12px;
    padding:20px;
    margin-bottom:20px;
}

.section h3{
    color:#D6C29C;
    margin-bottom:15px;
    border-bottom:1px solid #333;
    padding-bottom:10px;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}

.info-box{
    background:#101010;
    border:1px solid #292929;
    border-radius:8px;
    padding:14px;
}

.info-box label{
    display:block;
    color:#888;
    font-size:12px;
    margin-bottom:5px;
}

.info-box p{
    margin:0;
    color:#fff;
    font-size:14px;
    word-break:break-word;
}

.back-btn{
    display:inline-block;
    padding:10px 18px;
    background:#D6C29C;
    color:#111;
    text-decoration:none;
    border-radius:8px;
    font-weight:600;
}

.back-btn:hover{
    opacity:.85;
}

@media(max-width:800px){

    .main{
        margin-left:0;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">

<h2>Customer Profile</h2>

<!-- ================= CUSTOMER INFORMATION ================= -->

<div class="section">

<h3>Customer Information</h3>

<div class="info-grid">

<div class="info-box">
<label>Name</label>
<p><?= htmlspecialchars($customer['name']) ?></p>
</div>

<div class="info-box">
<label>Email</label>
<p><?= htmlspecialchars($customer['email']) ?></p>
</div>

<div class="info-box">
<label>Phone</label>
<p>
<?= !empty($customer['phone']) 
    ? htmlspecialchars($customer['phone']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Account Created</label>
<p><?= htmlspecialchars($customer['created_at']) ?></p>
</div>

</div>

</div>


<!-- ================= WELLNESS INFORMATION ================= -->

<div class="section">

<h3>Wellness Information</h3>

<div class="info-grid">

<div class="info-box">
<label>Allergies</label>
<p>
<?= !empty($customer['allergies']) 
    ? htmlspecialchars($customer['allergies']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Skin Sensitivity</label>
<p>
<?= !empty($customer['skin_sensitivity']) 
    ? htmlspecialchars($customer['skin_sensitivity']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Medical Conditions</label>
<p>
<?= !empty($customer['medical_conditions']) 
    ? htmlspecialchars($customer['medical_conditions']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Body Pain / Injuries</label>
<p>
<?= !empty($customer['body_pain_injuries']) 
    ? htmlspecialchars($customer['body_pain_injuries']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Pregnancy Status</label>
<p>
<?= !empty($customer['pregnancy_status']) 
    ? htmlspecialchars($customer['pregnancy_status']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Preferred Massage Pressure</label>
<p>
<?= !empty($customer['preferred_pressure']) 
    ? htmlspecialchars($customer['preferred_pressure']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box" style="grid-column:1/-1;">
<label>Special Requests</label>
<p>
<?= !empty($customer['special_requests']) 
    ? nl2br(htmlspecialchars($customer['special_requests'])) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Emergency Contact Name</label>
<p>
<?= !empty($customer['emergency_contact_name']) 
    ? htmlspecialchars($customer['emergency_contact_name']) 
    : 'Not provided' ?>
</p>
</div>

<div class="info-box">
<label>Emergency Contact Number</label>
<p>
<?= !empty($customer['emergency_contact_number']) 
    ? htmlspecialchars($customer['emergency_contact_number']) 
    : 'Not provided' ?>
</p>
</div>

</div>

</div>


<a href="users.php" class="back-btn">← Back to Users</a>

</div>

</body>
</html>