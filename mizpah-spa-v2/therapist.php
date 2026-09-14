<?php
session_start();
include 'includes/db.php';

$therapists = mysqli_query($conn,"
SELECT t.*,
IFNULL(AVG(tr.rating),0) as avg_rating,
COUNT(tr.id) as total_reviews
FROM therapists t
LEFT JOIN therapist_ratings tr ON tr.therapist_id=t.id
WHERE t.status='Active'
GROUP BY t.id
ORDER BY t.name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Therapists</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

body{
margin:0;
background:#0b0b0b;
font-family:Poppins, sans-serif;
color:#fff;
}

/* SUBTLE BACKGROUND (NO AI LOOK) */
body::before{
content:"";
position:fixed;
top:-200px;
left:-200px;
width:450px;
height:450px;
background:rgba(214,194,156,0.05);
filter:blur(120px);
z-index:-1;
}

body::after{
content:"";
position:fixed;
bottom:-200px;
right:-200px;
width:450px;
height:450px;
background:rgba(255,255,255,0.03);
filter:blur(140px);
z-index:-1;
}

/* HEADER (SPA MINIMAL LUXURY) */
.header{
display:flex;
justify-content:space-between;
align-items:center;
padding:18px 28px;
background:#0f0f0f;
border-bottom:1px solid rgba(255,255,255,0.06);
}

.left{
display:flex;
align-items:center;
gap:12px;
}

.logo{
width:42px;
height:42px;
object-fit:contain;
}

.title{
font-family:'Playfair Display', serif;
font-size:22px;
color:#D6C29C;
letter-spacing:1px;
}

.back{
color:#aaa;
text-decoration:none;
font-size:13px;
transition:.2s;
}

.back:hover{
color:#D6C29C;
}

/* GRID */
.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
gap:20px;
padding:28px;
max-width:1100px;
margin:auto;
}

/* CARD (HOTEL SPA STYLE) */
.card{
background:#121212;
border:1px solid rgba(255,255,255,0.06);
border-radius:14px;
padding:22px;
transition:0.3s ease;
position:relative;
overflow:hidden;
}

.card:hover{
transform:translateY(-6px);
border-color:rgba(214,194,156,0.25);
box-shadow:0 12px 25px rgba(0,0,0,0.6);
}

/* subtle highlight on hover */
.card::before{
content:"";
position:absolute;
inset:0;
background:radial-gradient(circle at top, rgba(214,194,156,0.06), transparent 60%);
opacity:0;
transition:.3s;
}

.card:hover::before{
opacity:1;
}

/* NAME */
.name{
font-size:18px;
font-weight:600;
color:#D6C29C;
font-family:'Playfair Display', serif;
margin-bottom:6px;
}

/* SPECIALTY */
.spec{
font-size:13px;
color:#b8b8b8;
line-height:1.6;
margin-bottom:14px;
min-height:50px;
}

/* RATING */
.rating{
color:#D6C29C;
font-size:14px;
font-weight:600;
margin-top:10px;
}

.small{
font-size:12px;
color:#777;
margin-top:4px;
}

/* HEADER LOGO STYLE IMPROVE */
.left img{
border-radius:6px;
}

</style>
</head>

<body>

<div class="header">

<div class="left">
<img src="assets/images/logo.png" class="logo">
<div class="title">Our Therapists</div>
</div>

<a href="index.php" class="back">← Back Home</a>

</div>

<div class="grid">

<?php while($t=mysqli_fetch_assoc($therapists)): ?>

<div class="card">

<div class="name"><?= htmlspecialchars($t['name']) ?></div>

<div class="spec">
<?= htmlspecialchars($t['specialty']) ?>
</div>

<div class="rating">
⭐ <?= number_format($t['avg_rating'],1) ?>/5
</div>

<div class="small">
<?= $t['total_reviews'] ?> review(s)
</div>

</div>

<?php endwhile; ?>

</div>

</body>
</html>