<?php
require 'common/config.php';
session_destroy();
header("Location: login.php");
exit;
?>