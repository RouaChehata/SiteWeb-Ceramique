<?php
session_start();
session_destroy();
header('Location: ce.php');
exit();
?> 