<?php
	session_start();
	if (isset($_SESSION['CA_USER']) && isset($_SESSION['CA_NAME'])) {
		header( 'Location: candidate.php' ) ;  
		DIE();
	}
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>רישום למועמדים חדשים</title>
	<script type="text/javascript" src="candidates.js"></script>
</head>
<body dir="rtl" style="font-family:Helvetica,Arial;font-size:12pt">
	<img src="http://www.mitpakdim.co.il/site/logo.png"/><br/><br/>
	<b>כניסה למועמדים רשומים</b><br/>
	<br/>
	<form action="login1.php" method="post">
	<table border="0">
		<tr>
			<td>מס' זהות:</td>
			<td><input type="text" name="zehut" /></td>
		</tr>
		<tr>
			<td>קוד אישי:</td>
			<td><input type="password" name="user" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" value="כניסה" /><br/>
				<span style="font-weight:bold;color:#ff0000">
					<?php
						if ($_GET['err'] == "server") {
							echo("אירעה תקלה בשרת. נסה שנית מאוחר יותר.");
						} else if ($_GET['err'] == "bad_login") {
							echo("פרטי המשתמש שגויים או שטרם בוצע רישום.");
						} else if ($_GET['err'] == "nologin") {
							echo("יש לבצע כניסה למערכת");
						}
					?>
				</span>
			</td>
		</tr>
	</table>
	</form>
	<br/>
	<b>רישום מועמדים חדשים</b><br/>
	<br/>
	מועמדים חדשים שטרם נרשמו למערכת מתבקשים <a href="reg1.php">ללחוץ כאן</a>
</body>
</html>