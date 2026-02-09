<?php
session_name('ug_irb_session');
session_start();

session_unset();
session_destroy();

header("Location: /login");
exit;
?>