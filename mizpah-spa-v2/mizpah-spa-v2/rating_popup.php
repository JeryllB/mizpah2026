<?php
include 'includes/db.php';

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"
    SELECT body_part, rating
    FROM booking_body_ratings
    WHERE booking_id='$id'
");

if(mysqli_num_rows($q) > 0){

    while($r=mysqli_fetch_assoc($q)){
        echo "<p>
            <b>".ucfirst($r['body_part'])."</b> 
            - ⭐ ".$r['rating']."
        </p>";
    }

}else{
    echo "<p>No ratings yet.</p>";
}
?>