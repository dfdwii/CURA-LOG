<?php
if (!isset($_SESSION['username'])) {
    header("Location: " . $base_url . "login.php");
    exit;
}
?>