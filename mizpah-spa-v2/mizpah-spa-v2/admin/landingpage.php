<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';


/*
|--------------------------------------------------------------------------
| SECTIONS
|--------------------------------------------------------------------------
*/

$sections = [
    'signature' => 'Mizpah Signature Services',
    'package'   => 'Mizpah Packages',
    'popular'   => 'Popular Choices'
];


/*
|--------------------------------------------------------------------------
| TOGGLE SHOWN / HIDDEN
|--------------------------------------------------------------------------
*/

if (isset($_GET['toggle'])) {

    $id = intval($_GET['toggle']);

    $check = mysqli_query(
        $conn,
        "SELECT status 
         FROM landing_page_services 
         WHERE id=$id 
         LIMIT 1"
    );

    if ($check && mysqli_num_rows($check) > 0) {

        $row = mysqli_fetch_assoc($check);

        $newStatus = ($row['status'] === 'shown')
            ? 'hidden'
            : 'shown';

        mysqli_query(
            $conn,
            "UPDATE landing_page_services
             SET status='$newStatus'
             WHERE id=$id"
        );
    }

    header("Location: landingpage.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| ADD SERVICE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {

    $service_id = intval($_POST['service_id']);
    $section = $_POST['section'] ?? '';

    if (
        $service_id > 0 &&
        array_key_exists($section, $sections)
    ) {

        /*
        |--------------------------------------------------------------------------
        | Check if already exists in this section
        |--------------------------------------------------------------------------
        */

        $check = mysqli_query(
            $conn,
            "SELECT id
             FROM landing_page_services
             WHERE service_id=$service_id
             AND section='$section'
             LIMIT 1"
        );

        if (!$check || mysqli_num_rows($check) === 0) {

            /*
            |--------------------------------------------------------------------------
            | Get next sort order
            |--------------------------------------------------------------------------
            */

            $sortQ = mysqli_query(
                $conn,
                "SELECT COALESCE(MAX(sort_order),0) + 1 AS next_order
                 FROM landing_page_services
                 WHERE section='$section'"
            );

            $sortRow = mysqli_fetch_assoc($sortQ);
            $nextOrder = intval($sortRow['next_order']);

            /*
            |--------------------------------------------------------------------------
            | Add as SHOWN
            |--------------------------------------------------------------------------
            */

            mysqli_query(
                $conn,
                "INSERT INTO landing_page_services
                (service_id, section, sort_order, status)
                VALUES
                ($service_id, '$section', $nextOrder, 'shown')"
            );
        }
    }

    header("Location: landingpage.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DEFAULT SIGNATURE SERVICES
|--------------------------------------------------------------------------
| Keep the original 3 services if the signature section has no records.
|--------------------------------------------------------------------------
*/

$signatureCheck = mysqli_query(
    $conn,
    "SELECT id
     FROM landing_page_services
     WHERE section='signature'
     LIMIT 1"
);

if ($signatureCheck && mysqli_num_rows($signatureCheck) === 0) {

    $defaultServices = [
        'Swedish Massage',
        'MIZPAH Signature',
        'Lymphatic Massage'
    ];

    $order = 1;

    foreach ($defaultServices as $serviceName) {

        $serviceNameEscaped = mysqli_real_escape_string(
            $conn,
            $serviceName
        );

        $serviceQ = mysqli_query(
            $conn,
            "SELECT id
             FROM services
             WHERE service_name='$serviceNameEscaped'
             LIMIT 1"
        );

        if ($serviceQ && mysqli_num_rows($serviceQ) > 0) {

            $serviceRow = mysqli_fetch_assoc($serviceQ);

            $serviceId = intval($serviceRow['id']);

            mysqli_query(
                $conn,
                "INSERT IGNORE INTO landing_page_services
                (service_id, section, sort_order, status)
                VALUES
                ($serviceId, 'signature', $order, 'shown')"
            );

            $order++;
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET ALL SERVICES
|--------------------------------------------------------------------------
*/

$services = [];

$servicesQ = mysqli_query(
    $conn,
    "SELECT
        id,
        service_name,
        category,
        description
     FROM services
     ORDER BY service_name ASC"
);

if ($servicesQ) {

    while ($row = mysqli_fetch_assoc($servicesQ)) {
        $services[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| GET LANDING PAGE ITEMS
|--------------------------------------------------------------------------
| IMPORTANT:
| We DO NOT filter status here.
| Hidden items must remain visible in admin listing.
|--------------------------------------------------------------------------
*/

$landingItems = [
    'signature' => [],
    'package'   => [],
    'popular'   => []
];

foreach ($sections as $sectionKey => $sectionTitle) {

    $sectionEscaped = mysqli_real_escape_string(
        $conn,
        $sectionKey
    );

    $landingQ = mysqli_query(
        $conn,
        "SELECT
            lps.id AS landing_id,
            lps.service_id,
            lps.section,
            lps.sort_order,
            lps.status,
            s.service_name,
            s.category,
            s.description
         FROM landing_page_services lps
         INNER JOIN services s
            ON s.id = lps.service_id
         WHERE lps.section='$sectionEscaped'
         ORDER BY lps.sort_order ASC, lps.id ASC"
    );

    if ($landingQ) {

        while ($row = mysqli_fetch_assoc($landingQ)) {
            $landingItems[$sectionKey][] = $row;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Landing Page Management</title>

<style>

*{
box-sizing:border-box;
}

body{
margin:0;
font-family:Arial, sans-serif;
background:#0f0f0f;
color:#ddd;
}

.main-content{
margin-left:280px;
padding:35px;
min-height:100vh;
}

.page-title{
color:#D6C29C;
font-size:30px;
font-weight:600;
margin-bottom:8px;
}

.page-subtitle{
color:#999;
margin-bottom:30px;
}

.card{
background:#161616;
border:1px solid rgba(214,194,156,.18);
border-radius:14px;
padding:25px;
margin-bottom:30px;
box-shadow:0 8px 25px rgba(0,0,0,.25);
}

.card-title{
color:#D6C29C;
font-size:21px;
font-weight:600;
margin-bottom:18px;
}

table{
width:100%;
border-collapse:collapse;
}

thead{
background:#241a0d;
}

th{
color:#D6C29C;
font-size:13px;
text-transform:uppercase;
letter-spacing:.5px;
padding:15px;
text-align:left;
}

td{
padding:15px;
border-bottom:1px solid #292929;
vertical-align:middle;
}

tbody tr:hover{
background:#1b1b1b;
}

.service-name{
color:#fff;
font-weight:500;
}

.category{
color:#999;
font-size:13px;
}

.status-badge{
display:inline-block;
padding:6px 12px;
border-radius:20px;
font-size:12px;
font-weight:600;
text-transform:uppercase;
}

.status-shown{
background:rgba(214,194,156,.15);
color:#D6C29C;
border:1px solid rgba(214,194,156,.3);
}

.status-hidden{
background:#252525;
color:#888;
border:1px solid #3a3a3a;
}

.add-btn,
.toggle-btn{
display:inline-block;
padding:9px 15px;
border-radius:8px;
text-decoration:none;
font-size:13px;
font-weight:600;
cursor:pointer;
border:none;
}

.add-btn{
background:#D6C29C;
color:#111;
}

.add-btn:hover{
background:#e5d5b5;
}

.toggle-hide{
background:#292929;
color:#ccc;
}

.toggle-hide:hover{
background:#3a3a3a;
}

.toggle-show{
background:#D6C29C;
color:#111;
}

.toggle-show:hover{
background:#e5d5b5;
}

.add-service-btn{
background:#D6C29C;
color:#111;
border:none;
padding:11px 18px;
border-radius:9px;
font-weight:600;
cursor:pointer;
}

.add-service-btn:hover{
background:#e5d5b5;
}

.empty{
padding:25px;
text-align:center;
color:#777;
}

.modal{
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,.75);
z-index:9999;
align-items:center;
justify-content:center;
padding:20px;
}

.modal-box{
width:500px;
max-width:100%;
background:#161616;
border:1px solid rgba(214,194,156,.25);
border-radius:14px;
padding:25px;
position:relative;
max-height:90vh;
overflow:auto;
}

.modal-box h2{
margin-top:0;
color:#D6C29C;
}

.close{
position:absolute;
right:18px;
top:12px;
font-size:28px;
color:#D6C29C;
cursor:pointer;
}

.form-group{
margin-bottom:20px;
}

.form-group label{
display:block;
margin-bottom:8px;
color:#ccc;
font-size:14px;
}

.form-group select{
width:100%;
padding:12px;
background:#0f0f0f;
border:1px solid #333;
border-radius:8px;
color:#ddd;
outline:none;
}

.form-group select:focus{
border-color:#D6C29C;
}

.submit-btn{
width:100%;
padding:12px;
background:#D6C29C;
color:#111;
border:none;
border-radius:8px;
font-weight:600;
cursor:pointer;
}

.submit-btn:hover{
background:#e5d5b5;
}

.section-label{
display:inline-block;
padding:5px 10px;
background:#241a0d;
color:#D6C29C;
border-radius:6px;
font-size:12px;
}

</style>

</head>


<body>

<?php include 'includes/sidebar.php'; ?>


<div class="main-content">

    <h1 class="page-title">
        Landing Page
    </h1>

    <p class="page-subtitle">
        Manage which services appear on the customer landing page.
    </p>


    <?php foreach ($sections as $sectionKey => $sectionTitle): ?>

    <div class="card">

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin-bottom:20px;
        ">

            <div class="card-title" style="margin-bottom:0;">
                <?= htmlspecialchars($sectionTitle) ?>
            </div>

            <button
                type="button"
                class="add-service-btn"
                onclick="openAddModal('<?= htmlspecialchars($sectionKey) ?>')"
            >
                + Add Service
            </button>

        </div>


        <?php if (!empty($landingItems[$sectionKey])): ?>

        <div style="overflow-x:auto;">

        <table>

        <thead>

        <tr>

            <th>Service</th>

            <th>Category</th>

            <th>Status</th>

            <th style="text-align:right;">
                Action
            </th>

        </tr>

        </thead>

        <tbody>

        <?php foreach ($landingItems[$sectionKey] as $item): ?>

        <?php
        $isShown = ($item['status'] === 'shown');
        ?>

        <tr>

            <td>

                <div class="service-name">
                    <?= htmlspecialchars($item['service_name']) ?>
                </div>

            </td>


            <td>

                <span class="category">
                    <?= htmlspecialchars($item['category']) ?>
                </span>

            </td>


            <td>

                <?php if ($isShown): ?>

                    <span class="status-badge status-shown">
                        Shown
                    </span>

                <?php else: ?>

                    <span class="status-badge status-hidden">
                        Hidden
                    </span>

                <?php endif; ?>

            </td>


            <td style="text-align:right;">

                <?php if ($isShown): ?>

                    <a
                        href="landingpage.php?toggle=<?= intval($item['landing_id']) ?>"
                        class="toggle-btn toggle-hide"
                    >
                        Hide
                    </a>

                <?php else: ?>

                    <a
                        href="landingpage.php?toggle=<?= intval($item['landing_id']) ?>"
                        class="toggle-btn toggle-show"
                    >
                        Show
                    </a>

                <?php endif; ?>

            </td>

        </tr>

        <?php endforeach; ?>

        </tbody>

        </table>

        </div>

        <?php else: ?>

            <div class="empty">
                No services added to this section yet.
            </div>

        <?php endif; ?>

    </div>

    <?php endforeach; ?>

</div>


<!-- ADD SERVICE MODAL -->

<div class="modal" id="addModal">

    <div class="modal-box">

        <span
            class="close"
            onclick="closeAddModal()"
        >
            &times;
        </span>


        <h2>
            Add Service
        </h2>


        <form method="POST">

            <input
                type="hidden"
                name="add_service"
                value="1"
            >


            <input
                type="hidden"
                name="section"
                id="selectedSection"
                value=""
            >


            <div class="form-group">

                <label>
                    Section
                </label>

                <select
                    id="sectionDisplay"
                    disabled
                >

                    <option value="signature">
                        Mizpah Signature Services
                    </option>

                    <option value="package">
                        Mizpah Packages
                    </option>

                    <option value="popular">
                        Popular Choices
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Select Service
                </label>

                <select
                    name="service_id"
                    required
                >

                    <option value="">
                        -- Select Service --
                    </option>

                    <?php foreach ($services as $service): ?>

                    <option value="<?= intval($service['id']) ?>">

                        <?= htmlspecialchars($service['service_name']) ?>

                        <?php if (!empty($service['category'])): ?>

                            — <?= htmlspecialchars($service['category']) ?>

                        <?php endif; ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <button
                type="submit"
                class="submit-btn"
            >
                Add Service
            </button>

        </form>

    </div>

</div>


<script>

function openAddModal(section){

    document.getElementById("selectedSection").value = section;

    document.getElementById("sectionDisplay").value = section;

    document.getElementById("addModal").style.display = "flex";

}


function closeAddModal(){

    document.getElementById("addModal").style.display = "none";

}


window.addEventListener("click", function(event){

    const modal = document.getElementById("addModal");

    if(event.target === modal){

        closeAddModal();

    }

});

</script>


</body>
</html>