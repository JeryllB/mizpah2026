<?php
session_start();
include '../includes/db.php';

/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/* SESSION */
$user_id = (int)($_SESSION['user_id'] ?? 0);
$name = $_SESSION['name'] ?? '';

/* ======================
   PROFILE PIC
====================== */
$userQ = mysqli_query($conn, "SELECT profile_pic FROM users WHERE id = $user_id LIMIT 1");
$userRow = mysqli_fetch_assoc($userQ);

$profilePic = $userRow['profile_pic'] ?? '';

if (empty($profilePic)) {
    $profilePic = '../assets/images/default-profile.png';
} else {
    $profilePic = '../uploads/profile/' . $profilePic;
}

/* ======================
   STATS (SAFE VERSION)
====================== */
$total = 0;
$pending = 0;
$approved = 0;
$completed = 0;

/* TOTAL */
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE user_id = $user_id");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $total = $row['total'] ?? 0;
}

/* PENDING */
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE user_id = $user_id AND status='Pending'");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $pending = $row['total'] ?? 0;
}

/* APPROVED */
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE user_id = $user_id AND status IN ('Approved','Confirmed')");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $approved = $row['total'] ?? 0;
}

/* COMPLETED */
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE user_id = $user_id AND status='Completed'");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $completed = $row['total'] ?? 0;
}

/* ======================
   BOOKINGS
====================== */
$bookings = mysqli_query($conn, "
    SELECT *
    FROM bookings
    WHERE user_id = $user_id
    ORDER BY id DESC
    LIMIT 5
");

/* ======================
   NOTIFICATIONS
====================== */
$notif = 0;
$res = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = $user_id
    AND is_read = 0
");

if ($res) {
    $row = mysqli_fetch_assoc($res);
    $notif = $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Customer Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif;}

body{
background:linear-gradient(rgba(0,0,0,.72),rgba(0,0,0,.82)),
url('../assets/images/hero.jpg') center/cover fixed no-repeat;
color:#fff;
min-height:100vh;
}

header{
display:flex;
justify-content:space-between;
align-items:center;
padding:14px 8%;
background:rgba(10,10,10,.88);
position:sticky;
top:0;
z-index:99;
}

.logo{
display:flex;
align-items:center;
gap:10px;
color:#D6C29C;
font-weight:600;
}

nav a{
color:#fff;
margin-left:12px;
text-decoration:none;
font-size:14px;
opacity:.8;
}

nav a:hover{color:#D6C29C;opacity:1;}

.profile-mini{
width:34px;height:34px;border-radius:50%;
object-fit:cover;border:2px solid #D6C29C;
}

.wrap{padding:30px 8%;}

.hero{
background:rgba(20,20,20,.7);
padding:25px;
border-radius:15px;
display:flex;
gap:15px;
align-items:center;
}

.hero img{
width:70px;height:70px;border-radius:50%;
object-fit:cover;border:3px solid #D6C29C;
}

.hero h1{margin:0;color:#D6C29C;}

.card{
margin-top:20px;
padding:20px;
background:rgba(20,20,20,.7);
border-radius:12px;
}
</style>
</head>

<body>

<header>
<div class="logo">
<img src="../assets/images/logo.png" style="height:40px;">
<span>Mizpah Wellness Spa</span>
</div>

<nav>
<a href="dashboard.php">Home</a>
<a href="booking.php">Book</a>
<a href="mybookings.php">My Bookings</a>
<a href="notifications.php">Notifications</a>
<a href="profile.php">Profile</a>
<a href="logout.php">Logout</a>

<img src="<?php echo $profilePic; ?>" class="profile-mini">
</nav>
</header>

<div class="wrap">

<div class="hero">
<img src="<?php echo $profilePic; ?>">
<div>
<h1>Welcome, <?php echo htmlspecialchars($name); ?></h1>
<p>Manage your bookings, schedules, and therapist ratings.</p>
</div>
</div>

<div class="card">
<h3>Total: <?php echo $total; ?></h3>
<h3>Pending: <?php echo $pending; ?></h3>
<h3>Approved: <?php echo $approved; ?></h3>
<h3>Completed: <?php echo $completed; ?></h3>
</div>

</div>

</body>
</html>