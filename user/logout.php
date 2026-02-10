<?php
// Session is already started in index.php, no need to start again

session_unset();
session_destroy();

header("Location: /login");
exit;
?>