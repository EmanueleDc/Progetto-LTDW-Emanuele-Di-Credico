<?php
require_once '../../include/auth.php';
logoutUser();
header("Location: index.php");
exit;
?>
