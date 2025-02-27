<?php
$conn = mysqli_connect('localhost','root','','hcc_pms');

if(!$conn){
    die("Coonection Failed!: " .
    myqli_connect_error($conn));
}
?>