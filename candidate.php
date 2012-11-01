<?php
	session_start();
	if (!isset($_SESSION['CA_USER']) || !isset($_SESSION['CA_NAME'])) {
		header( 'Location: login.php?err=nologin' ) ;  
	}
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>רישום למועמדים חדשים</title>
    <script type="text/javascript" src="lib/underscore-1.4.2.js"></script>
    <script type="text/javascript" src="lib/jquery-1.8.2.js"></script>
	<script type="text/javascript" src="candidates.js"></script>
	<script type="text/javascript">
	function load() {
		var status = get_candidate_status();
		if (status && status.status) {
			if (status.status == "COMPLETED") {
				document.getElementById('lblProfileStatus').innerHTML = "הושלם";
				document.getElementById('lblNoChage').style.display = "block";
			} else {
				document.getElementById('lblProfileStatus').innerHTML = "טרם הושלם";
				document.getElementById('divEmailStatus').style.display = "block";
				document.getElementById('divFilesStatus').style.display = "block";
				document.getElementById('divAgendaStatus').style.display = "block";
				
				document.getElementById('lblEmail').innerHTML = status.email;
				if (status.email_conf) {
					document.getElementById('lblEmailMissing').innerHTML = "הושלם";
					document.getElementById('btnResendEmail').style.display = "none";
				} else {
					document.getElementById('lblEmailMissing').innerHTML = "טרם הושלם";
				}
				
				if (status.file_image && status.file_cv && status.file_zehut) {
					document.getElementById('lblFilesMissing').innerHTML = "הושלם";
				} else {
					document.getElementById('lblFilesMissing').innerHTML = "טרם הושלם";
				}
				if (status.has_agenda) {
					document.getElementById('lblAgendaMissing').innerHTML = "הושלם";
				} else {
					document.getElementById('lblAgendaMissing').innerHTML = "טרם הושלם";
				}
				
				if (status.file_image && status.file_cv && status.file_zehut && status.has_agenda) {
					document.getElementById('divComplete').style.display = "block";
				}
			}
		}
	}
    function approve () {
        var $button = $('#divComplete input:button');
        $button[0].disabled = true;
        $button.after('<span id="approve_status">שולח...</span>');
        function setStatus(status) {
            var text = status ? 'תודה!' : 'ארעה שגיעה, אנא טען מחדש את העמוד ונסה שנית';
            $('#approve_status').text(text);
        }
        $.post('candidate_complete.php').done(function(resp, status) {
            if (resp.type == 'success') {
                setStatus(true);
            } else {
                setStatus(false);
            }
        }).fail(function(resp, status) {
            setStatus(false);
        });
    }

	</script>
</head>
<body dir="rtl" style="font-family:Helvetica,Arial;font-size:12pt" onload="load();">
	<img src="http://www.mitpakdim.co.il/site/logo.png"/><br/><br/>
	<b>שלום <?php echo $_SESSION['CA_NAME']; ?>,</b><br/>
	<br/>
	מצב הפרופיל שלך הינו: <b><span id="lblProfileStatus"></span></b><br/>
	<br/>
	<span id="lblNoChage" style="display:none">לא ניתן לשנות פרופיל לאחר השלמתו</span>
	<div id="divEmailStatus" style="display:none">
		אישרור דואר אלקטרוני: <b><span id="lblEmailMissing"></span></b><br/>
		כתובת הדואר האלקטרוני: <span id="lblEmail"></span>
		<div id="btnResendEmail"><br/><a href="resendemail.php">לשליחה חוזרת של מכתב האישרור לחצו כאן</a><br/>
		<span style="font-size:10pt">לאחר קבלת מכתב האישרור יש ללחוץ על לינק האישרור המופיע בו (או להעתיק את הכתובת במלואה ולהדביקה בדפדפן).</span></div>
	</div>
	<br/>
	<div id="divFilesStatus" style="display:none">
		העלאת קבצים דרושים: <b><span id="lblFilesMissing"></span></b>
		<div id="btnUpdateFiles"><a href="files.php">לעדכון לחצו כאן</a></div>
	</div>
	<br/>
	<div id="divAgendaStatus" style="display:none">
		בחירת העדפות לנושאים: <b><span id="lblAgendaMissing"></span></b>
		<div id="btnUpdateAgendas"><a href="agendas.php">לעדכון לחצו כאן</a></div>
	</div>
	<div id="divComplete" style="display:none">
		אישור פרופיל: <b>טרם הושלם</b><br/>
		<br/>
		אני מאשר/ת את הפרופיל ומעוניין/ת לצרפו למערכת באופן סופי. המידע הנמסר הינו התחייבות שלי ואני אחראי/ת לו. אני מאשר לתנועת מתפקדים ו/או לסדנא לידע ציבורי להשתמש במידע.<br/>
		<br/>
		<b><?php echo($_SESSION["CA_NAME"]); ?></b><br/>
		<input type="button" onclick="approve()" value="אישור"/>
	</div>
	<br/>
	<br/>
	<a href="logout.php">יציאה מהמערכת</a>
</body>
</html>
