<?php
session_start();
session_unset();
session_destroy();

// Redirect specifically to your combined file with a 'logout' status
header("Location: login_register.php?logout=success");
exit();
?>