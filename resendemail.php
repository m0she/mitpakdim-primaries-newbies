<?php
session_start();
if (!isset($_SESSION['CA_USER']) || !isset($_SESSION['CA_NAME'])) {
    header( 'Location: login.php' ) ;
    exit();
}
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>רישום למועמדים חדשים</title>
	<script type="text/javascript" src="candidates.js"></script>
	<script type="text/javascript">
	function send() {
		var res = resend_confirmation();
		if (res) {
			document.getElementById("divSuccess").style.display = "block";
		} else {
			document.getElementById("divFail").style.display = "block";
		}
	}
	</script>
</head>
<body dir="rtl" style="font-family:Helvetica,Arial;font-size:12pt" onload="send();">
	<img src="http://www.mitpakdim.co.il/site/logo.png"/><br/><br/>
	<div id="divSuccess" style="display:none">
	מכתב האישרור נשלח שוב לכתובת הדואר האלקטרוני שלך.<br/>
	<br/>
	</div>
	<div id="divFail" style="display:none">
	אירעה תקלה. לא ניתן לשלוח את המכתב.<br/>
	אם התקלה חוזרת על עצמה אנא פנו אלינו ל-info@mitpakdim.co.il.<br/>
	<br/>
	</div>
	<a href="candidate.php">לחזרה לחצו כאן</a>
</body>
</html>