<?php
require_once '../../include/auth.php';

logoutUser();
setFlash("Hai effettuato il logout. A presto!");
redirect('login.php');
?>
