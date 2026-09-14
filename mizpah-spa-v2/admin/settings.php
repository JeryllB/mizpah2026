<?php
session_start();
include '../includes/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

/* SAVE */
if(isset($_POST['save'])){

$site_name = $_POST['site_name'];
$tagline = $_POST['tagline'];
$contact = $_POST['contact_number'];
$address = $_POST['address'];
$copyright = $_POST['copyright_text'];

mysqli_query($conn,"
UPDATE settings SET
site_name='$site_name',
tagline='$tagline',
contact_number='$contact',
address='$address',
copyright_text='$copyright'
WHERE id=1
");

echo "<script>alert('Settings Updated');window.location='settings.php';</script>";
}

/* LOAD */
$q = mysqli_query($conn,"SELECT * FROM settings WHERE id=1");
$row = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Settings</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<style>
body{
margin:0;
font-family:Poppins,sans-serif;
background:#0b0b0b;
color:#fff;
}

.main{
margin-left:250px;
padding:30px;
}

.card{
background:#161616;
border:1px solid #222;
padding:25px;
border-radius:14px;
max-width:700px;
}

h2{
color:#D6C29C;
margin-bottom:20px;
}

label{
display:block;
margin:12px 0 6px;
font-size:13px;
color:#aaa;
}

input,textarea{
width:100%;
padding:12px;
border:none;
border-radius:8px;
background:#111;
color:#fff;
font-size:14px;
}

textarea{
height:90px;
resize:none;
}

button{
margin-top:18px;
padding:12px 18px;
background:#D6C29C;
color:#111;
font-weight:700;
border:none;
border-radius:8px;
cursor:pointer;
}
</style>
</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main">

<div class="card">

<h2>Website Settings</h2>

<form method="POST">

<label>Site Name</label>
<input type="text" name="site_name"
value="<?= $row['site_name'] ?>">

<label>Tagline</label>
<input type="text" name="tagline"
value="<?= $row['tagline'] ?>">

<label>Contact Number</label>
<input type="text" name="contact_number"
value="<?= $row['contact_number'] ?>">

<label>Address</label>
<input type="text" name="address"
value="<?= $row['address'] ?>">

<label>Copyright</label>
<textarea name="copyright_text"><?= $row['copyright_text'] ?></textarea>

<button name="save">Save Settings</button>

</form>

</div>

</div>

</body>
</html>