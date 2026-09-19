<?php

session_start();

include '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer'){
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* SAVE WELLNESS PROFILE */

if(isset($_POST['save_wellness'])){

    $allergies = mysqli_real_escape_string($conn, $_POST['allergies']);
    $skin_sensitivity = mysqli_real_escape_string($conn, $_POST['skin_sensitivity']);
    $medical_conditions = mysqli_real_escape_string($conn, $_POST['medical_conditions']);
    $body_pain_injuries = mysqli_real_escape_string($conn, $_POST['body_pain_injuries']);
    $pregnancy_status = mysqli_real_escape_string($conn, $_POST['pregnancy_status']);
    $preferred_pressure = mysqli_real_escape_string($conn, $_POST['preferred_pressure']);
    $special_requests = mysqli_real_escape_string($conn, $_POST['special_requests']);
    $emergency_contact_name = mysqli_real_escape_string($conn, $_POST['emergency_contact_name']);
    $emergency_contact_number = mysqli_real_escape_string($conn, $_POST['emergency_contact_number']);

    /* CHECK IF PROFILE ALREADY EXISTS */

    $check = mysqli_query($conn,"
        SELECT id
        FROM wellness_profiles
        WHERE user_id='$user_id'
        LIMIT 1
    ");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,"
            UPDATE wellness_profiles SET
                allergies='$allergies',
                skin_sensitivity='$skin_sensitivity',
                medical_conditions='$medical_conditions',
                body_pain_injuries='$body_pain_injuries',
                pregnancy_status='$pregnancy_status',
                preferred_pressure='$preferred_pressure',
                special_requests='$special_requests',
                emergency_contact_name='$emergency_contact_name',
                emergency_contact_number='$emergency_contact_number'
            WHERE user_id='$user_id'
        ");

    }else{

        mysqli_query($conn,"
            INSERT INTO wellness_profiles
            (
                user_id,
                allergies,
                skin_sensitivity,
                medical_conditions,
                body_pain_injuries,
                pregnancy_status,
                preferred_pressure,
                special_requests,
                emergency_contact_name,
                emergency_contact_number
            )
            VALUES
            (
                '$user_id',
                '$allergies',
                '$skin_sensitivity',
                '$medical_conditions',
                '$body_pain_injuries',
                '$pregnancy_status',
                '$preferred_pressure',
                '$special_requests',
                '$emergency_contact_name',
                '$emergency_contact_number'
            )
        ");
    }

    /* AFTER WELLNESS PROFILE → MY PROFILE */

    header("Location: profile.php");
    exit();
}


/* GET EXISTING WELLNESS PROFILE */

$profile = [];

$result = mysqli_query($conn,"
    SELECT *
    FROM wellness_profiles
    WHERE user_id='$user_id'
    LIMIT 1
");

if(mysqli_num_rows($result) > 0){
    $profile = mysqli_fetch_assoc($result);
}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Wellness Profile</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins,sans-serif;
}

body{
    background:
    linear-gradient(rgba(0,0,0,.78),rgba(0,0,0,.88)),
    url('../assets/images/hero.jpg') center/cover fixed no-repeat;
    color:#fff;
    min-height:100vh;
}

.container{
    width:100%;
    max-width:800px;
    margin:auto;
    padding:40px 20px;
}

.card{
    background:rgba(20,20,20,.82);
    backdrop-filter:blur(12px);
    border:1px solid rgba(214,194,156,.18);
    border-radius:18px;
    padding:35px;
    box-shadow:0 15px 40px rgba(0,0,0,.5);
}

h1{
    text-align:center;
    font-family:'Playfair Display',serif;
    color:#D6C29C;
    font-size:30px;
    margin-bottom:8px;
}

.subtitle{
    text-align:center;
    color:#aaa;
    font-size:13px;
    margin-bottom:30px;
}

.section-title{
    color:#D6C29C;
    font-size:17px;
    font-weight:600;
    margin:25px 0 15px;
    padding-bottom:8px;
    border-bottom:1px solid rgba(214,194,156,.15);
}

.group{
    margin-bottom:17px;
}

label{
    display:block;
    font-size:12px;
    color:#bbb;
    margin-bottom:7px;
}

.required{
    color:#D6C29C;
}

.optional{
    color:#777;
    font-size:11px;
    font-weight:400;
}

input,
select,
textarea{
    width:100%;
    padding:12px 13px;
    border-radius:10px;
    border:1px solid #333;
    background:#111;
    color:#fff;
    outline:none;
    transition:.2s;
}

input:focus,
select:focus,
textarea:focus{
    border-color:#D6C29C;
    box-shadow:0 0 8px rgba(214,194,156,.25);
}

select option{
    background:#111;
    color:#fff;
}

textarea{
    min-height:100px;
    resize:vertical;
}

button{
    width:100%;
    padding:14px;
    margin-top:15px;
    background:#D6C29C;
    border:none;
    border-radius:10px;
    color:#111;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    transition:.2s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(214,194,156,.2);
}

.skip{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#999;
    text-decoration:none;
    font-size:13px;
}

.skip:hover{
    color:#D6C29C;
}

@media(max-width:600px){

    .container{
        padding:25px 12px;
    }

    .card{
        padding:22px;
    }

    h1{
        font-size:26px;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Wellness Profile</h1>

        <p class="subtitle">
            Help us understand your wellness needs and preferences.
        </p>


        <form method="POST">


            <!-- WELLNESS INFORMATION -->

            <div class="section-title">
                Wellness Information
            </div>


            <div class="group">

                <label>
                    Allergies <span class="required">*</span>
                </label>

                <select name="allergies" required>

                    <option value="">Select an option</option>

                    <option value="Lavender"
                    <?= (($profile['allergies'] ?? '') == 'Lavender') ? 'selected' : '' ?>>
                        Lavender
                    </option>

                    <option value="Coconut Oil"
                    <?= (($profile['allergies'] ?? '') == 'Coconut Oil') ? 'selected' : '' ?>>
                        Coconut Oil
                    </option>

                    <option value="None"
                    <?= (($profile['allergies'] ?? '') == 'None') ? 'selected' : '' ?>>
                        None
                    </option>

                </select>

            </div>


            <div class="group">

                <label>
                    Skin Sensitivity <span class="required">*</span>
                </label>

                <select name="skin_sensitivity" required>

                    <option value="">Select an option</option>

                    <option value="Sensitive Skin"
                    <?= (($profile['skin_sensitivity'] ?? '') == 'Sensitive Skin') ? 'selected' : '' ?>>
                        Sensitive Skin
                    </option>

                    <option value="Normal Skin"
                    <?= (($profile['skin_sensitivity'] ?? '') == 'Normal Skin') ? 'selected' : '' ?>>
                        Normal Skin
                    </option>

                    <option value="Not Sure"
                    <?= (($profile['skin_sensitivity'] ?? '') == 'Not Sure') ? 'selected' : '' ?>>
                        Not Sure
                    </option>

                </select>

            </div>


            <div class="group">

                <label>
                    Medical Conditions <span class="required">*</span>
                </label>

                <select name="medical_conditions" required>

                    <option value="">Select an option</option>

                    <option value="Asthma"
                    <?= (($profile['medical_conditions'] ?? '') == 'Asthma') ? 'selected' : '' ?>>
                        Asthma
                    </option>

                    <option value="None"
                    <?= (($profile['medical_conditions'] ?? '') == 'None') ? 'selected' : '' ?>>
                        None
                    </option>

                    <option value="Other"
                    <?= (($profile['medical_conditions'] ?? '') == 'Other') ? 'selected' : '' ?>>
                        Other
                    </option>

                </select>

            </div>


            <div class="group">

                <label>
                    Current Injuries or Body Pain
                </label>

                <input
                    type="text"
                    name="body_pain_injuries"
                    value="<?= htmlspecialchars($profile['body_pain_injuries'] ?? '') ?>"
                    placeholder="e.g. Lower back pain"
                >

            </div>


            <div class="group">

                <label>
                    Pregnancy Status <span class="required">*</span>
                </label>

                <select name="pregnancy_status" required>

                    <option value="">Select an option</option>

                    <option value="Not Applicable"
                    <?= (($profile['pregnancy_status'] ?? '') == 'Not Applicable') ? 'selected' : '' ?>>
                        Not Applicable
                    </option>

                    <option value="Prefer not to say"
                    <?= (($profile['pregnancy_status'] ?? '') == 'Prefer not to say') ? 'selected' : '' ?>>
                        Prefer not to say
                    </option>

                    <option value="Pregnant"
                    <?= (($profile['pregnancy_status'] ?? '') == 'Pregnant') ? 'selected' : '' ?>>
                        Pregnant
                    </option>

                </select>

            </div>


            <div class="group">

                <label>
                    Preferred Massage Pressure <span class="required">*</span>
                </label>

                <select name="preferred_pressure" required>

                    <option value="">Select an option</option>

                    <option value="Light"
                    <?= (($profile['preferred_pressure'] ?? '') == 'Light') ? 'selected' : '' ?>>
                        Light
                    </option>

                    <option value="Medium"
                    <?= (($profile['preferred_pressure'] ?? '') == 'Medium') ? 'selected' : '' ?>>
                        Medium
                    </option>

                    <option value="Strong"
                    <?= (($profile['preferred_pressure'] ?? '') == 'Strong') ? 'selected' : '' ?>>
                        Strong
                    </option>

                </select>

            </div>


            <div class="group">

                <label>
                    Special Requests
                </label>

                <textarea
                    name="special_requests"
                    placeholder="e.g. Avoid shoulder area"
                ><?= htmlspecialchars($profile['special_requests'] ?? '') ?></textarea>

            </div>


            <!-- EMERGENCY CONTACT -->

            <div class="section-title">
                Emergency Contact
            </div>


            <div class="group">

                <label>
                    Emergency Contact Name
                    <span class="optional">(Optional)</span>
                </label>

                <input
                    type="text"
                    name="emergency_contact_name"
                    value="<?= htmlspecialchars($profile['emergency_contact_name'] ?? '') ?>"
                    placeholder="Enter emergency contact name"
                >

            </div>


            <div class="group">

                <label>
                    Emergency Contact Number
                    <span class="optional">(Optional)</span>
                </label>

                <input
                    type="text"
                    name="emergency_contact_number"
                    value="<?= htmlspecialchars($profile['emergency_contact_number'] ?? '') ?>"
                    placeholder="Enter emergency contact number"
                >

            </div>


            <button type="submit" name="save_wellness">
                Save Wellness Profile
            </button>

        </form>


        <a href="dashboard.php" class="skip">
            Skip for now
        </a>

    </div>

</div>

</body>

</html>