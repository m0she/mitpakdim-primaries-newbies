<?php
include "common.php";
if (isset($_SESSION['CA_USER']) && isset($_SESSION['CA_NAME'])) {
	header( 'Location: candidate.php' ) ;
} else {
    $con = connect();
	if (!$con) {
		header( 'Location: login.php?err=server' ) ;
		exit();
	}
	
	$zehut = $_POST["zehut"];
	$user = $_POST["user"];
	
	if (strlen($zehut) == 0 || strlen($user) == 0) {
		header( 'Location: login.php?err=bad_login' ) ;
		exit();
	}
	
	$zehut = mysql_real_escape_string($zehut);
	$user = mysql_real_escape_string($user);
	
	$query = "call REG_USPS_LOGIN(" . $zehut . ",'" . $user . "');";
	$result = mysql_query($query, $con);
	if ($result) {
		if ($row = mysql_fetch_array($result)) {
			$_SESSION['CA_USER'] = $user;
			$_SESSION['CA_NAME'] = $row['CA_FNAME'] . ' ' . $row['CA_LNAME'];
			header( 'Location: candidate.php' ) ;
		} else {
			header( 'Location: login.php?err=bad_login' ) ;
		}
	} else {
		header( 'Location: login.php?err=server' ) ;
	}
	mysql_close($con);
}
?>
