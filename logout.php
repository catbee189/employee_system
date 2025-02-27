<?php
session_start();
session_destroy(); // Destroy all session data
header("Location: index.php?logout=success"); // Redirect with a logout flag
exit();
?>
