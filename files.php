<?php
include "common.php";
check_session();
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>רישום למועמדים חדשים</title>
	<script type="text/javascript" src="candidates.js"></script>
	<script type="text/javascript">
	var status;
	
	function load() {
		status = get_candidate_status();
		if (status && status.status) {
			if (status.status == "COMPLETED") {
				window.location.href = "candidate.php";
			} else {
				if (status.file_image && status.file_cv && status.file_zehut) {
					document.getElementById('lblFilesStatus').innerHTML = "הושלם";
				} else {
					document.getElementById('lblFilesStatus').innerHTML = "טרם הושלם";
				}
				if (status.file_image) {
					document.getElementById('lblImageStatus').innerHTML = "קיימת תמונה במערכת<br/><br/>";
				} else {
					document.getElementById('lblImageStatus').innerHTML = "חסרה תמונה<br/><br/>";
				}
				if (status.file_zehut) {
					document.getElementById('lblIdStatus').innerHTML = "קיים קובץ במערכת<br/><br/>";
				} else {
					document.getElementById('lblIdStatus').innerHTML = "חסר קובץ<br/><br/>";
				}
				if (status.file_cv) {
					document.getElementById('lblCvStatus').innerHTML = "קיים קובץ במערכת<br/><br/>";
				} else {
					document.getElementById('lblCvStatus').innerHTML = "חסר קובץ<br/><br/>";
				}
			}
		}
	}
	</script>
</head>
<body dir="rtl" style="font-family:Helvetica,Arial;font-size:12pt" onload="load();">
	<img src="http://www.mitpakdim.co.il/site/logo.png"/><br/><br/>
	<b>שלום <?php echo $_SESSION['CA_NAME']; ?>,</b><br/>
	<br/>
        מצב העלאת הקבצים שלך הינו: <b><span id="lblFilesStatus"></span></b><br/>
        <br/>
		<?php
			if (isset($_GET["err"])) {
				$err = $_GET["err"];
				$msg = "";
				if ($err == 'err_system') {
					$msg = "אירעה תקלת מערכת. אנא נסו שנית מאוחר יותר.";
				} else if ($err == 'bad_size') {
					$msg = "הקובץ שנשלח גדול מדי. יש לשלוח קובץ לפי מגבלת הגודל המצויינת.";
				} else if ($err == 'bad_type') {
					$msg = "סוג הקובץ אינו מתאים. יש לשלוח קובץ מהסוגים המותרים בלבד.";
				} else if ($err == 'bad_file') {
					$msg = "הקובץ שנשלח אינו תקין. יש לבדוק את הקובץ ואם הוא תקין, לנסות שנית.";
				} else {
					$msg = "אירעה תקלה לא ידועה";
				}
				echo '<span style="font-weight:bold;color:#ff0000">' . $msg . '</span><br/><br/>';
			}
		?>
        <u>תמונה</u><br/>
		<b><span id="lblImageStatus" style="font-size:12pt"></span></b>
        יש להעלות תמונה בגודל
        <span dir="ltr">75 x 110</span> פיקסלים בדיוק (העלאת תמונה בגודל שאינו תקין תגרום לעיוות תמונתך בעיני הבוחרים) 
        <br/>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="uploadtype" value="photo"/>
            <input type="file" name="file" id="photo"/>
            <input type="submit" value="שליחה" id="btnUploadPhoto"/>
			<br/><span style="font-size:8pt">גודל קובץ מקסימלי: 512KB</span>
			<br/><span style="font-size:8pt">סוגי קובץ מותרים: jpg, jpeg, gif, png</span>
        </form>
        <br/>
        <u>צילום/סריקת תעודת זהות</u><br/>
		<b><span id="lblIdStatus" style="font-size:12pt"></span></b>
        יש להעלות צילום תעודת הזהות של המועמד/ת על מנת שנוכל לוודא זהותם ולמנוע התחזות<br/>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="uploadtype" value="id"/>
            <input type="file" name="file" id="id"/>
            <input type="submit" value="שליחה" id="btnUploadId"/>
			<br/><span style="font-size:8pt">גודל קובץ מקסימלי: 512KB</span>
			<br/><span style="font-size:8pt">סוגי קובץ מותרים: jpg, jpeg, gif, png, pdf</span>
        </form>
        <br/>
        <u>קורות חיים</u><br/>
		<b><span id="lblCvStatus" style="font-size:12pt"></span></b>
        יש להעלות קובץ תמצית קורות חיים של המועמד/ת, בפורמט PDF בלבד, על מנת לספק מידע על עשייתם לבוחרים.<br/>
        לא ניתן לסיים את התהליך ולהציג את המועמד/ת במערכת ללא קובץ זה!<br/>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="uploadtype" value="resume"/>
            <input type="file" name="file" id="resume"/>
            <input type="submit" value="שליחה" id="btnUploadCv"/>
			<br/><span style="font-size:8pt">גודל קובץ מקסימלי: 1MB</span>
			<br/><span style="font-size:8pt">סוגי קובץ מותרים: pdf</span>
        </form>
        <br/><br/>
        <a href="candidate.php">חזרה לדף הראשי</a>
</body>
</html>
