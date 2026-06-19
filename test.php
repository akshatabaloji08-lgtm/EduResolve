<?php
echo "PHP Working!<br>";
echo "PHP Version: " . phpversion() . "<br>";

$conn = mysqli_connect(
    'sql313.infinityfree.com',
    'if0_41933829',
    'AkshataBaloji',
    'if0_41933829_campuscare'
);

if($conn){
    echo "Database Connected! ✅";
} else {
    echo "Database Failed: " . mysqli_connect_error();
}
?>