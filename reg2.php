<?php
session_start();
if (!isset($_SESSION['CA_USER']) || !isset($_SESSION['CA_NAME'])) {
    header( 'Location: reg1.php' ) ;
    exit();
}
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>רישום למועמדים חדשים</title>
</head>
<body dir="rtl" style="font-family:Helvetica,Arial;font-size:12pt">
	<img src="http://www.mitpakdim.co.il/site/logo.png"/><br/><br/>
	<div style="width:500px; height:300px; border-color:#000000; border-style:solid; border-width: 2px">
	<b>שלב א': פרטים אישיים של המועמד/ת</b><br/>
        <br/>
               רישומך הראשוני במערכת בוצע בהצלחה.<br/>
               <br/>
			   נשלח אליך דואר אלקטרוני לאישור הכתובת. <b>יש ללחוץ על הלינק המופיע בו על מנת להמשיך בתהליך</b>.<br/>
			   <br/>
               <b>הקוד האישי שלך הינו <?php echo $_SESSION['CA_USER']; ?></b><br/>
               <br/>
               קוד זה ישמש אותך להשלמת תהליך הרישום ומילוי הפרטים במידה ותחליט/י להפסיקו ולהמשיכו בשלב מאוחר יותר.<br/>
               חשוב! במידה ומפסיקים את התהליך וממשיכים בשלב מאוחר יותר, יש להשתמש בקוד זה ולא לנסות להרשם מחדש.<br/>
	</div>
</body>
</html>
