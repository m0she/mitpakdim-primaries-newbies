<?php
include "common.php";

function confirm($confkey) {
	// REG_USPU_EMAIL_CONF
	
	$con = connect();
	
	$confkey = mysql_real_escape_string($confkey);
	
	$query = "call REG_USPU_EMAIL_CONF('" . $confkey . "','" . date("Y-m-d H:i:s") . "')";
	
	$result = mysql_query($query);
	if ($result) {
		$row = mysql_fetch_array($result);
		if ($row) {
			$_SESSION['CA_USER'] = $row['CA_USER'];
			$_SESSION['CA_NAME'] = $row['CA_FNAME'] . ' ' . $row['CA_LNAME'];
			header( 'Location: candidate.php' ) ;
			exit();
		}
	}
	header( 'Location: login.php?err=bad_login' ) ;
}

session_start();
confirm($_GET['key']);
?>
