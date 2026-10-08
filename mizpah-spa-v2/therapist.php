<?php

session_start();

include 'includes/db.php';


/* =========================================================
   LOAD ACTIVE THERAPISTS + RATINGS
   ========================================================= */

$therapists = mysqli_query($conn, "

    SELECT
        t.*,
        IFNULL(AVG(tr.rating), 0) AS avg_rating,
        COUNT(tr.id) AS total_reviews

    FROM therapists t

    LEFT JOIN therapist_ratings tr
        ON tr.therapist_id = t.id

    WHERE t.status = 'Active'

    GROUP BY t.id

    ORDER BY t.name ASC

");


/* =========================================================
   GET THERAPIST COUNT
   ========================================================= */

$therapistCount = 0;

if ($therapists) {

    $therapistCount =
        mysqli_num_rows($therapists);

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

<title>Our Therapists | Mizpah Wellness Spa</title>

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
   SUBTLE BACKGROUND
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

    filter:
        blur(120px);

    pointer-events:none;

    z-index:-1;

}

body::after{

    content:"";

    position:fixed;

    bottom:-240px;

    right:-220px;

    width:520px;

    height:520px;

    background:
        rgba(214,194,156,.03);

    filter:
        blur(130px);

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


/* =========================================================
   LOGO
   ========================================================= */

.logo-link{

    display:flex;

    align-items:center;

    gap:11px;

    text-decoration:none;

}

.logo{

    width:42px;

    height:42px;

    object-fit:contain;

}

.brand{

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
   LOGIN BUTTON
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
            rgba(8,8,8,.77),
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

    max-width:600px;

    margin:auto;

}


/* =========================================================
   MAIN
   ========================================================= */

.main{

    max-width:1150px;

    margin:auto;

    padding:
        65px 5%
        80px;

}


/* =========================================================
   SECTION HEADING
   ========================================================= */

.section-heading{

    display:flex;

    align-items:flex-end;

    justify-content:space-between;

    gap:25px;

    padding-bottom:17px;

    margin-bottom:25px;

    border-bottom:
        1px solid
        rgba(214,194,156,.12);

}

.section-small{

    color:#776a55;

    font-size:8px;

    letter-spacing:2px;

    text-transform:uppercase;

    margin-bottom:5px;

}

.section-heading h2{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:27px;

    font-weight:600;

    margin-bottom:5px;

}

.section-heading p{

    color:#777;

    font-size:10px;

    line-height:1.7;

}

.therapist-count{

    color:#666;

    font-size:9px;

    white-space:nowrap;

}


/* =========================================================
   THERAPIST GRID
   ========================================================= */

.grid{

    display:grid;

    grid-template-columns:
        repeat(
            3,
            minmax(0,1fr)
        );

    gap:16px;

}


/* =========================================================
   THERAPIST CARD
   ========================================================= */

.card{

    min-height:285px;

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

    overflow:hidden;

    transition:.25s;

}

.card::before{

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

.card:hover{

    transform:
        translateY(-5px);

    border-color:
        rgba(214,194,156,.22);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,.30);

}

.card:hover::before{

    opacity:1;

}


/* =========================================================
   CARD TOP
   ========================================================= */

.card-top{

    display:flex;

    align-items:center;

    gap:13px;

    margin-bottom:18px;

}


/* =========================================================
   INITIAL AVATAR
   ========================================================= */

.avatar{

    width:48px;

    height:48px;

    flex-shrink:0;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:50%;

    background:
        rgba(214,194,156,.08);

    border:
        1px solid
        rgba(214,194,156,.20);

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:20px;

    font-weight:600;

}


/* =========================================================
   NAME
   ========================================================= */

.name{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    font-size:18px;

    font-weight:600;

    line-height:1.3;

}

.status{

    display:inline-block;

    margin-top:4px;

    color:#777;

    font-size:8px;

    text-transform:uppercase;

    letter-spacing:.7px;

}

.status::before{

    content:"";

    width:5px;

    height:5px;

    display:inline-block;

    margin-right:5px;

    border-radius:50%;

    background:#9d956f;

    vertical-align:middle;

}


/* =========================================================
   SPECIALTY
   ========================================================= */

.specialty-label{

    color:#666;

    font-size:8px;

    text-transform:uppercase;

    letter-spacing:1px;

    margin-bottom:7px;

}

.spec{

    color:#999;

    font-size:10px;

    line-height:1.8;

    margin-bottom:20px;

}


/* =========================================================
   RATING
   ========================================================= */

.rating-box{

    margin-top:auto;

    padding-top:16px;

    border-top:
        1px solid
        rgba(255,255,255,.055);

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

}

.rating-left{

    display:flex;

    flex-direction:column;

    gap:3px;

}

.rating{

    color:#D6C29C;

    font-size:13px;

    font-weight:600;

}

.stars{

    color:#D6C29C;

    letter-spacing:1px;

    font-size:10px;

}

.small{

    color:#666;

    font-size:8px;

}


/* =========================================================
   NO REVIEW
   ========================================================= */

.no-rating{

    color:#777;

    font-size:9px;

}


/* =========================================================
   BOOK BUTTON
   ========================================================= */

.book-btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    padding:
        8px 12px;

    background:#D6C29C;

    color:#111;

    border-radius:7px;

    text-decoration:none;

    font-size:8px;

    font-weight:600;

    transition:.2s;

    white-space:nowrap;

}

.book-btn:hover{

    background:#ead7ad;

    transform:
        translateY(-1px);

}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty{

    max-width:650px;

    margin:
        40px auto;

    padding:45px 25px;

    background:#111;

    border:
        1px solid
        #222;

    border-radius:14px;

    text-align:center;

}

.empty h3{

    color:#D6C29C;

    font-family:
        'Playfair Display',
        serif;

    margin-bottom:8px;

}

.empty p{

    color:#777;

    font-size:10px;

}


/* =========================================================
   BOTTOM CTA
   ========================================================= */

.bottom-cta{

    max-width:900px;

    margin:
        65px auto
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

    max-width:570px;

    margin:
        0 auto
        18px;

    color:#777;

    font-size:10px;

    line-height:1.7;

}

.bottom-actions{

    display:flex;

    justify-content:center;

    align-items:center;

    flex-wrap:wrap;

    gap:12px;

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

    transition:.2s;

}

.bottom-book:hover{

    background:#ead7ad;

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

    text-align:center;

    color:#555;

    font-size:9px;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:950px){

    .grid{

        grid-template-columns:
            repeat(
                2,
                minmax(0,1fr)
            );

    }

}


@media(max-width:850px){

    .nav{

        display:none;

    }

}


@media(max-width:650px){

    .header{

        padding:
            10px 4%;

    }

    .logo{

        width:36px;

        height:36px;

    }

    .brand{

        font-size:14px;

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

    .main{

        padding:
            45px 4%
            60px;

    }

    .section-heading{

        align-items:flex-start;

        flex-direction:column;

        gap:7px;

    }

    .grid{

        grid-template-columns:1fr;

    }

    .card{

        min-height:auto;

        padding:19px;

    }

}


/* =========================================================
   VERY SMALL SCREEN
   ========================================================= */

@media(max-width:390px){

    .brand{

        display:none;

    }

    .rating-box{

        align-items:flex-start;

        flex-direction:column;

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
        class="logo-link"
    >

        <img
            src="assets/images/logo.png"
            class="logo"
            alt="Mizpah Wellness Spa"
        >

        <span class="brand">
            Mizpah Wellness Spa
        </span>

    </a>


    <nav class="nav">

        <a href="index.php">
            Home
        </a>

        <a href="services.php">
            Services
        </a>

        <a
            href="therapist.php"
            class="active"
        >
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
            Meet Our Therapists
        </h1>


        <p>

            Meet the wellness professionals behind your
            Mizpah experience. Our active therapists are
            here to provide relaxing and personalized
            treatments based on your selected service.

        </p>


    </div>

</section>


<!-- =========================================================
     MAIN
     ========================================================= -->

<main class="main">


    <div class="section-heading">


        <div>


            <div class="section-small">
                Wellness Professionals
            </div>


            <h2>
                Our Active Therapists
            </h2>


            <p>

                Explore our therapists, their specialties,
                and ratings from previous customer experiences.

            </p>


        </div>


        <div class="therapist-count">

            <?= $therapistCount ?>

            <?= $therapistCount === 1
                ? 'therapist'
                : 'therapists'
            ?>

            available

        </div>


    </div>


    <!-- =====================================================
         THERAPIST CARDS
         ===================================================== -->


    <?php if (
        $therapists &&
        $therapistCount > 0
    ): ?>


        <div class="grid">


            <?php while (
                $t =
                mysqli_fetch_assoc(
                    $therapists
                )
            ): ?>


                <?php

                $therapistName =
                    trim(
                        $t['name']
                    );


                $firstLetter =
                    !empty($therapistName)
                    ?
                    strtoupper(
                        substr(
                            $therapistName,
                            0,
                            1
                        )
                    )
                    :
                    'M';


                $totalReviews =
                    (int)$t['total_reviews'];


                $averageRating =
                    (float)$t['avg_rating'];


                $roundedStars =
                    (int)round(
                        $averageRating
                    );

                ?>


                <article class="card">


                    <!-- TOP -->

                    <div class="card-top">


                        <div class="avatar">

                            <?= htmlspecialchars(
                                $firstLetter
                            ) ?>

                        </div>


                        <div>


                            <div class="name">

                                <?= htmlspecialchars(
                                    $therapistName
                                ) ?>

                            </div>


                            <div class="status">
                                Available Therapist
                            </div>


                        </div>


                    </div>


                    <!-- SPECIALTY -->

                    <div class="specialty-label">
                        Specialty
                    </div>


                    <div class="spec">

                        <?php if (
                            !empty(
                                $t['specialty']
                            )
                        ): ?>

                            <?= nl2br(
                                htmlspecialchars(
                                    $t['specialty']
                                )
                            ) ?>

                        <?php else: ?>

                            Wellness and massage therapy

                        <?php endif; ?>

                    </div>


                    <!-- RATING -->

                    <div class="rating-box">


                        <div class="rating-left">


                            <?php if (
                                $totalReviews > 0
                            ): ?>


                                <div class="rating">

                                    <?= number_format(
                                        $averageRating,
                                        1
                                    ) ?>/5

                                </div>


                                <div class="stars">

                                    <?php

                                    for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ) {

                                        echo
                                            $i <= $roundedStars
                                            ? '★'
                                            : '☆';

                                    }

                                    ?>

                                </div>


                                <div class="small">

                                    <?= $totalReviews ?>

                                    <?= $totalReviews === 1
                                        ? 'customer review'
                                        : 'customer reviews'
                                    ?>

                                </div>


                            <?php else: ?>


                                <div class="no-rating">
                                    No reviews yet
                                </div>


                                <div class="small">
                                    Be the first to rate
                                    this therapist
                                </div>


                            <?php endif; ?>


                        </div>


                        <a
                            href="booking-guest.php"
                            class="book-btn"
                        >
                            Book Appointment
                        </a>


                    </div>


                </article>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty">


            <h3>
                No Therapists Available
            </h3>


            <p>

                There are currently no active therapists
                available to display.

            </p>


        </div>


    <?php endif; ?>


    <!-- =====================================================
         BOTTOM CTA
         ===================================================== -->

    <div class="bottom-cta">


        <h2>
            Ready to Relax?
        </h2>


        <p>

            Choose your preferred service and schedule.
            You may select a therapist during booking
            or choose No Preference.

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