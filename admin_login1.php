<?php
include "common.php";
if ($_POST['user'] == 'admin' && $_POST['pass'] == $settings['access']['adminpass']) {
	session_start();
    $_SESSION['admin'] = 'on';
    header( 'Location: admin.php' ) ;
} else {
    header( 'Location: admin_login.php?err=bad_login' ) ;
}
?>
