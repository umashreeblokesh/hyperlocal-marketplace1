<?php
session_start();
session_unset();
session_destroy();
header("Location: ../../frontend/admin/admin-login.html");
exit;
?>
