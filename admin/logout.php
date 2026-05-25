<?php
require '../common/config.php';
unset($_SESSION['admin_id']);
header("Location: login.php");
exit;
?>