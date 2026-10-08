<?php

session_start();

include 'includes/db.php';


/* =========================================================
   LOAD SERVICES
   ========================================================= */

$services = mysqli_query($conn, "

    SELECT *
    FROM services

    ORDER BY

    CASE category

        WHEN 'Massage' THEN 1
        WHEN 'Package' THEN 2
        WHEN 'Promo' THEN 3
        WHEN 'Add-ons' THEN 4

        ELSE 5

    END,

    service_name ASC

");


/* =========================================================
   GROUP SERVICES BY CATEGORY
   ========================================================= */

$serviceGroups = [];

if ($services) {

    while ($service = mysqli_fetch_assoc($services)) {

        $category = !empty($service['category'])
            ? $service['category']
            : 'Other';

        $serviceGroups[$category][] = $service;

    }

}


/* =========================================================
   CATEGORY INFORMATION
   ========================================================= */

$categoryInfo = [

    'Massage' => [
        'label' => 'Massage Treatments',
        'description' =>
            'Relax, recharge, and restore your body with our massage treatments.'
    ],

    'Package' => [
        'label' => 'Wellness Packages',
        'description' =>
            'Enjoy a complete wellness experience with our carefully selected spa packages.'
    ],

    'Promo' => [
        'label' => 'Special Promos',
        'description' =>
            'Discover selected treatments and wellness experiences at special rates.'
    ],

    'Add-ons' => [
        'label' => 'Treatment Add-ons',
        'description' =>
            'Enhance your spa experience with additional treatments.'
    ]

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Our Services | Mizpah Wellness Spa</title>

<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@500;600;700&display=swap"
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

html{
    scroll-behavior:smooth;
}

body{

    min-height:100vh;

    background:#0b0b0b;

    color:#fff;

    font-family:
        'Poppins',
        sans-serif;

    overflow-x:hidden;

}


/* =========================================================
   BACKGROUND
   ========================================================= */

body::before{

    content:"";

    position:fixed;

    top:-220px;
    left:-220px;

    width:520px;
    height:520px;

    background:
        rgba(214,194,156,.05);

    filter:blur(120px);

    pointer-events:none;

    z-index:-1;

}

body::after{

    content:"";

    position:fixed;

    bottom:-250px;
    right:-220px;

    width:520px;
    height:520px;

    background:
        rgba(214,194,156,.035);

    filter:blur(130px);

    pointer-events:none;

    z-index:-1;

}


/* =========================================================
   HEADER
   ========================================================= */

.header{

    position:sticky;

    top:0;

    z-index:1000;

    min-height:70px;

    padding:
        12px 6%;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:30px;

    background:
        rgba(11,11,11,.96);

    backdrop-filter:
        blur(15px);

    border-bottom:
        1px solid
        rgba(214,194,156,.12);

}

.logo{

    display:flex;

    align-items:center;

    gap:11px;

    text-decoration:none;

}

.logo img{

    width:42px;
    height:42px;

    object-fit:contain;

}

.logo span{

    font-family:
        'Playfair Display',
        serif;

    font-size:17px;

    color:#D6C29C;

    letter-spacing:.5px;

    white-space:nowrap;

}


/* =========================================================
   NAVIGATION
   ========================================================= */

.nav{

    display:flex;

    align-items:center;

    justify-content:center;

    gap:25px;

    margin-left:auto;

}

.nav a{

    color:#aaa;

    text-decoration:none;

    font-size:11px;

    transition:.2s;

    position:relative;

}

.nav a:hover,
.nav a.active{

    color:#D6C29C;

}

.nav a.active::after{

    content:"";

    position:absolute;

    left:0;
    right:0;

    bottom:-8px;

    height:1px;

    background:#D6C29C;

}


/* =========================================================
   HEADER LOGIN
   ========================================================= */

.login-btn{

    padding:
        8px 15px;

    border:
        1px solid
        rgba(214,194,156,.4);

    border-radius:7px;

    color:#D6C29C;

    text-decoration:none;

    font-size:11px;

    transition:.2s;

}

.login-btn:hover{

    background:#D6C29C;

    color:#111;

}


/* =========================================================
   HERO
   ========================================================= */

.hero{

    min-height:330px;

    position:relative;

    display:flex;

    justify-content:center;

    align-items:center;

    text-align:center;

    padding:
        80px 20px
        65px;

    background:

        linear-gradient(
            rgba(8,8,8,.76),
            rgba(11,11,11,.97)
        ),

        url('assets/images/hero.jpg')
        center/cover no-repeat;

    border-bottom:
        1px solid
        rgba(255,255,255,.04);

}

.hero-content{

    max-width:720px;

}

.eyebrow{

    color:#9b8969;

    font-size:9px;

    text-transform:uppercase;

    letter-spacing:3px;

    margin-bottom:12px;

}

.hero h1{

    font-family:
        'Playfair Display',
        serif;

    color:#D6C29C;

    font-size:
        clamp(
            34px,
            5vw,
            52px
        );

    font-weight:600;

    margin-bottom:13px;

}

.hero p{

    color:#aaa;

    font-size:12px;

    line-height:1.8;

    max-width:590px;

    margin:auto;

}


/* =========================================================
   CATEGORY NAVIGATION
   ========================================================= */

.category-nav-wrapper{

    position:sticky;

    top:70px;

    z-index:900;

    background:
        rgba(11,11,11,.96);

    backdrop-filter:
        blur(12px);

    border-bottom:
        1px solid
        rgba(255,255,255,.05);

}

.category-nav{

    max-width:1150px;

    margin:auto;

    padding:
        13px 5%;

    display:flex;

    justify-content:center;

    align-items:center;

    gap:8px;

    flex-wrap:wrap;

}

.category-nav a{

    padding:
        7px 14px;

    color:#888;

    text-decoration:none;

    border:
        1px solid
        #252525;

    border-radius:20px;

    font-size:9px;

    transition:.2s;

}

.category-nav a:hover{

    color:#111;

    background:#D6C29C;

    border-color:#D6C29C;

}


/* =========================================================
   MAIN CONTENT
   ========================================================= */

.main{

    max-width:1150px;

    margin:auto;

    padding:
        65px 5%
        80px;

}


/* =========================================================
   CATEGORY SECTION
   ========================================================= */

.category-section{

    margin-bottom:75px;

    scroll-margin-top:150px;

}

.category-heading{

    display:flex;

    justify-content:space-between;

    align-items:flex-end;

    gap:25px;

    padding-bottom:16px;

    margin-bottom:22px;

    border-bottom:
        1px solid
        rgba(214,194,156,.12);

}

.category-heading-left{

    max-width:650px;

}

.category-small{

    color:#776a55;

    text-transform:uppercase;

    letter-spacing:2px;

    font-size:8px;

    margin-bottom:5px;

}

.category-heading h2{

    font-family:
        'Playfair Display',
        serif;

    color:#D6C29C;

    font-size:27px;

    font-weight:600;

    margin-bottom:6px;

}

.category-heading p{

    color:#777;

    font-size:10px;

    line-height:1.7;

}

.service-count{

    color:#666;

    font-size:9px;

    white-space:nowrap;

}


/* =========================================================
   SERVICE GRID
   ========================================================= */

.service-grid{

    display:grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );

    gap:15px;

}


/* =========================================================
   SERVICE CARD
   ========================================================= */

.service-card{

    min-height:260px;

    position:relative;

    display:flex;

    flex-direction:column;

    padding:23px;

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

    transition:.25s;

    overflow:hidden;

}

.service-card::before{

    content:"";

    position:absolute;

    top:0;
    left:0;

    width:100%;

    height:2px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(214,194,156,.45),
            transparent
        );

    opacity:0;

    transition:.25s;

}

.service-card:hover{

    transform:
        translateY(-4px);

    border-color:
        rgba(214,194,156,.22);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,.28);

}

.service-card:hover::before{

    opacity:1;

}


/* =========================================================
   SERVICE TOP
   ========================================================= */

.service-top{

    display:flex;

    justify-content:space-between;

    align-items:flex-start;

    gap:15px;

    margin-bottom:12px;

}

.service-title{

    font-family:
        'Playfair Display',
        serif;

    color:#D6C29C;

    font-size:19px;

    font-weight:600;

    line-height:1.3;

}

.category-badge{

    flex-shrink:0;

    padding:
        4px 8px;

    border:
        1px solid
        rgba(214,194,156,.17);

    border-radius:15px;

    color:#8f8068;

    font-size:7px;

    text-transform:uppercase;

    letter-spacing:.6px;

}

.service-desc{

    color:#888;

    font-size:10px;

    line-height:1.8;

    margin-bottom:18px;

}


/* =========================================================
   DURATION / PRICE
   ========================================================= */

.duration-title{

    color:#666;

    font-size:8px;

    text-transform:uppercase;

    letter-spacing:1px;

    margin-bottom:8px;

}

.duration-list{

    display:flex;

    flex-wrap:wrap;

    gap:7px;

    margin-bottom:20px;

}

.duration-item{

    display:flex;

    align-items:center;

    gap:6px;

    padding:
        7px 9px;

    background:#0c0c0c;

    border:
        1px solid
        #252525;

    border-radius:7px;

    font-size:9px;

}

.duration-name{

    color:#999;

}

.duration-price{

    color:#D6C29C;

    font-weight:500;

}

.no-data{

    color:#666;

    font-size:9px;

}


/* =========================================================
   CARD BOTTOM
   ========================================================= */

.card-bottom{

    margin-top:auto;

    padding-top:15px;

    border-top:
        1px solid
        rgba(255,255,255,.055);

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

}

.starting-price{

    display:flex;

    flex-direction:column;

    gap:2px;

}

.starting-price span{

    color:#555;

    font-size:8px;

    text-transform:uppercase;

    letter-spacing:.5px;

}

.starting-price strong{

    color:#D6C29C;

    font-size:15px;

    font-weight:500;

}


/* =========================================================
   BOOK BUTTON
   ========================================================= */

.book-btn{

    display:inline-flex;

    justify-content:center;

    align-items:center;

    padding:
        9px 14px;

    background:#D6C29C;

    color:#111;

    text-decoration:none;

    border-radius:7px;

    font-size:9px;

    font-weight:600;

    transition:.2s;

}

.book-btn:hover{

    background:#ead7ad;

    transform:
        translateY(-1px);

}


/* =========================================================
   EMPTY
   ========================================================= */

.empty{

    max-width:700px;

    margin:
        80px auto;

    text-align:center;

    color:#777;

    padding:40px;

    background:#111;

    border:
        1px solid
        #222;

    border-radius:14px;

}

.empty h3{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    margin-bottom:8px;

}


/* =========================================================
   BOTTOM CTA
   ========================================================= */

.bottom-cta{

    max-width:900px;

    margin:
        10px auto
        0;

    padding:
        40px 25px;

    text-align:center;

    background:
        linear-gradient(
            145deg,
            rgba(214,194,156,.07),
            rgba(255,255,255,.015)
        );

    border:
        1px solid
        rgba(214,194,156,.14);

    border-radius:16px;

}

.bottom-cta h2{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:25px;

    margin-bottom:7px;

}

.bottom-cta p{

    color:#777;

    font-size:10px;

    line-height:1.7;

    margin-bottom:18px;

}

.bottom-actions{

    display:flex;

    justify-content:center;

    align-items:center;

    gap:12px;

    flex-wrap:wrap;

}

.bottom-book{

    display:inline-block;

    padding:
        11px 20px;

    background:#D6C29C;

    color:#111;

    text-decoration:none;

    border-radius:8px;

    font-size:10px;

    font-weight:600;

}

.account-link{

    color:#777;

    font-size:9px;

}

.account-link a{

    color:#D6C29C;

    text-decoration:none;

    font-weight:500;

}

.account-link a:hover{

    text-decoration:underline;

}


/* =========================================================
   FOOTER
   ========================================================= */

.footer{

    padding:
        28px 5%;

    border-top:
        1px solid
        rgba(255,255,255,.05);

    color:#555;

    font-size:9px;

    text-align:center;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:850px){

    .nav{
        display:none;
    }

    .service-grid{

        grid-template-columns:1fr;

    }

}

@media(max-width:600px){

    .header{

        padding:
            10px 4%;

    }

    .logo span{

        font-size:14px;

    }

    .logo img{

        width:36px;
        height:36px;

    }

    .login-btn{

        padding:
            7px 11px;

        font-size:9px;

    }

    .hero{

        min-height:280px;

        padding:
            65px 20px
            50px;

    }

    .hero p{

        font-size:10px;

    }

    .category-nav-wrapper{

        top:57px;

    }

    .category-nav{

        justify-content:flex-start;

        overflow-x:auto;

        flex-wrap:nowrap;

        padding:
            10px 4%;

    }

    .category-nav a{

        flex-shrink:0;

    }

    .main{

        padding:
            45px 4%
            60px;

    }

    .category-heading{

        align-items:flex-start;

        flex-direction:column;

        gap:7px;

    }

    .service-card{

        min-height:auto;

        padding:18px;

    }

    .service-top{

        flex-direction:column;

        gap:8px;

    }

    .card-bottom{

        align-items:flex-end;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     HEADER
     ========================================================= -->

<header class="header">


    <a
        href="index.php"
        class="logo"
    >

        <img
            src="assets/images/logo.png"
            alt="Mizpah Wellness Spa"
        >

        <span>
            Mizpah Wellness Spa
        </span>

    </a>


    <nav class="nav">

        <a href="index.php">
            Home
        </a>

        <a
            href="services.php"
            class="active"
        >
            Services
        </a>

        <a href="therapist.php">
            Therapists
        </a>

        <a href="index.php#virtual-tour">
            Virtual Tour
        </a>

    </nav>


    <a
        href="login.php"
        class="login-btn"
    >
        Login
    </a>


</header>


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="hero">

    <div class="hero-content">

        <div class="eyebrow">
            Mizpah Wellness Spa
        </div>

        <h1>
            Our Services
        </h1>

        <p>
            Discover massage treatments, wellness packages,
            special offers, and add-ons designed to help you
            relax, recharge, and enjoy a more comfortable
            wellness experience.
        </p>

    </div>

</section>


<!-- =========================================================
     CATEGORY NAVIGATION
     ========================================================= -->

<?php if (!empty($serviceGroups)): ?>

<div class="category-nav-wrapper">

    <div class="category-nav">

        <?php foreach ($serviceGroups as $category => $items): ?>

            <?php

            $categoryId =
                'category-' .
                preg_replace(
                    '/[^a-z0-9]+/',
                    '-',
                    strtolower($category)
                );

            ?>

            <a href="#<?= htmlspecialchars($categoryId) ?>">

                <?= htmlspecialchars($category) ?>

            </a>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
     SERVICES
     ========================================================= -->

<main class="main">


<?php if (!empty($serviceGroups)): ?>


    <?php foreach ($serviceGroups as $category => $items): ?>


        <?php

        $categoryId =
            'category-' .
            preg_replace(
                '/[^a-z0-9]+/',
                '-',
                strtolower($category)
            );


        $categoryLabel =
            $categoryInfo[$category]['label']
            ??
            $category;


        $categoryDescription =
            $categoryInfo[$category]['description']
            ??
            'Explore our available wellness services.';

        ?>


        <section
            class="category-section"
            id="<?= htmlspecialchars($categoryId) ?>"
        >


            <div class="category-heading">


                <div class="category-heading-left">

                    <div class="category-small">
                        Mizpah Wellness
                    </div>


                    <h2>

                        <?= htmlspecialchars(
                            $categoryLabel
                        ) ?>

                    </h2>


                    <p>

                        <?= htmlspecialchars(
                            $categoryDescription
                        ) ?>

                    </p>

                </div>


                <div class="service-count">

                    <?= count($items) ?>

                    <?= count($items) === 1
                        ? 'service'
                        : 'services'
                    ?>

                </div>


            </div>


            <div class="service-grid">


                <?php foreach ($items as $service): ?>


                    <?php

                    $serviceId =
                        (int)$service['id'];


                    $durations = [];


                    $durationQuery =
                        mysqli_query(
                            $conn,
                            "
                            SELECT
                                duration,
                                price
                            FROM service_durations
                            WHERE service_id = $serviceId
                            ORDER BY price ASC
                            "
                        );


                    $startingPrice = null;


                    if ($durationQuery) {

                        while (
                            $duration =
                            mysqli_fetch_assoc(
                                $durationQuery
                            )
                        ) {

                            $durations[] =
                                $duration;


                            if (
                                $startingPrice === null
                                ||
                                (float)$duration['price']
                                <
                                $startingPrice
                            ) {

                                $startingPrice =
                                    (float)$duration['price'];

                            }

                        }

                    }

                    ?>


                    <article class="service-card">


                        <div class="service-top">


                            <div class="service-title">

                                <?= htmlspecialchars(
                                    $service['service_name']
                                ) ?>

                            </div>


                            <div class="category-badge">

                                <?= htmlspecialchars(
                                    $category
                                ) ?>

                            </div>


                        </div>


                        <div class="service-desc">

                            <?php if (
                                !empty(
                                    $service['description']
                                )
                            ): ?>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $service['description']
                                    )
                                ) ?>

                            <?php else: ?>

                                Relax and enjoy a wellness
                                experience from Mizpah
                                Wellness Spa.

                            <?php endif; ?>

                        </div>


                        <div class="duration-title">
                            Available Options
                        </div>


                        <div class="duration-list">


                            <?php if (!empty($durations)): ?>


                                <?php foreach ($durations as $duration): ?>


                                    <div class="duration-item">

                                        <span class="duration-name">

                                            <?= htmlspecialchars(
                                                $duration['duration']
                                            ) ?>

                                        </span>


                                        <span class="duration-price">

                                            ₱<?= number_format(
                                                (float)$duration['price'],
                                                2
                                            ) ?>

                                        </span>

                                    </div>


                                <?php endforeach; ?>


                            <?php else: ?>


                                <div class="no-data">

                                    No duration available

                                </div>


                            <?php endif; ?>


                        </div>


                        <div class="card-bottom">


                            <div class="starting-price">

                                <span>
                                    Starting at
                                </span>


                                <strong>

                                    <?php if (
                                        $startingPrice !== null
                                    ): ?>

                                        ₱<?= number_format(
                                            $startingPrice,
                                            2
                                        ) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </strong>

                            </div>


                            <a
                                href="booking-guest.php"
                                class="book-btn"
                            >
                                Book This Service
                            </a>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        </section>


    <?php endforeach; ?>


<?php else: ?>


    <div class="empty">

        <h3>
            No Services Available
        </h3>

        <p>
            Services will appear here once they
            are added to the system.
        </p>

    </div>


<?php endif; ?>


<!-- =========================================================
     BOTTOM CTA
     ========================================================= -->

<div class="bottom-cta">

    <h2>
        Ready for Your Wellness Experience?
    </h2>

    <p>
        You can book immediately without an account,
        or create an account to manage your appointments
        and wellness profile.
    </p>


    <div class="bottom-actions">

        <a
            href="booking-guest.php"
            class="bottom-book"
        >
            Book as Guest
        </a>


        <div class="account-link">

            Want your own account?

            <a href="login.php">
                Login or Create Account
            </a>

        </div>

    </div>

</div>


</main>


<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="footer">

    Mizpah Wellness Spa · Kawit, Cavite

</footer>


</body>

</html>