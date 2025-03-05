<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1, 1000);
}
?>
<a href="group_call.php">Join Call</a>
