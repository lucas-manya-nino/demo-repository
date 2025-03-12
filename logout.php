<?php
require_once 'includes/db.php';

logoutUser();

header("Location: index.php");
exit;
?>