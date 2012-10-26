<?php
include "common.php";
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'on') {
    header( 'Location: admin_login.php?err=nologin' ) ;
	exit();
}

function print_column_personal_info($row) {
	echo "<td>";
	echo "<b>ת.ז.:</b> " . $row['CA_ZEHUT'] . "<br/>";
	echo "<b>שם פרטי:</b> " . $row['CA_FNAME'] . "<br/>";
	echo "<b>שם משפחה:</b> " . $row['CA_LNAME'] . "<br/>";
	echo "<b>תאריך רישום:</b> " . $row['CA_REGDATE'];
	echo "</td>";
}

function print_column_communication_info($row) {
	echo "<td>";
	echo "<b>דואר אלקטרוני:</b> " . $row['CA_EMAIL'] . "<br/>";
	echo "<b>טלפון:</b> " . $row['CA_PHONE'] . "<br/>";
	$url = $row['CA_WEBSITE'];
	if (substr($url, 0, 4) != "http")
		$url = "http://" . $url;
	echo "<b>אתר אינטרנט:</b> <a href=\"". $url . "\" target=\"_blank\">" . $row['CA_WEBSITE'] . "</a><br/>";
	$url = $row['CA_FACEBOOK'];
	if (substr($url, 0, 4) != "http")
		$url = "http://" . $url;
	echo "<b>פייסבוק:</b> <a href=\"". $url . "\" target=\"_blank\">" . $row['CA_FACEBOOK'] . "</a><br/>";
	echo "</td>";
}

function print_column_party_info($row) {
	echo "<td>";
	echo "<b>מפלגה:</b> " . $row['PA_NAME_HE'] . "<br/>";
	echo "<b>מחוז:</b> " . $row['RE_NAME_HE'] . "<br/>";
	echo "</td>";
}

function print_column_files_info($row, $allow_delete) {
	echo "<td>";
	echo "<b>תמונה:</b> ";
	if ($row['CA_FILE_IMAGE'])
		echo "<a href=\"cand_data/". $row['CA_FILE_IMAGE'] . "\" target=\"_blank\">לחץ לצפייה</a>";
	else
		echo "אין";
	if ($allow_delete) {
		echo " (<a href=\"\">מחק קובץ</a>)";
	}
	echo "<br/>";
	
	echo "<b>צילום ת.ז.:</b> ";
	if ($row['CA_FILE_ZEHUT'])
		echo "<a href=\"cand_data/". $row['CA_FILE_ZEHUT'] . "\" target=\"_blank\">לחץ לצפייה</a>";
	else
		echo "אין";
	if ($allow_delete) {
		echo " (<a href=\"\">מחק קובץ</a>)";
	}
	echo "<br/>";
		
	echo "<b>קורות חיים:</b> ";
	if ($row['CA_FILE_CV'])
		echo "<a href=\"cand_data/". $row['CA_FILE_CV'] . "\" target=\"_blank\">לחץ לצפייה</a>";
	else
		echo "אין";
	if ($allow_delete) {
		echo " (<a href=\"\">מחק קובץ</a>)";
	}
	echo "</td>";
}

function print_column_agenda($row, $allow_delete) {
	if ($row['CA_AGENDAS'])
		echo "<td><a onclick=\"\" style=\"cursor:pointer\">לחץ לפירוט</a></td>";
	else
		echo "<td>אין</td>";
	if ($allow_delete) {
		echo " (<a href=\"\">מחק העדפות</a>)";
	}
}

function print_columns_headers() {
	echo "<tr>";
	echo "<td><b>פרטים אישיים</b></td>";
	echo "<td><b>פרטי תקשורת</b></td>";
	echo "<td><b>מפלגה ומחוז</b></td>";
	echo "<td><b>קבצים מצורפים</b></td>";
	echo "<td><b>אג'נדות</b></td>";
	echo "<td><b>פעולות</b></td>";
	echo "</tr>";
}

/*
This page should let the admin see all registrants that completed their registration but are not yet approved (and are not declined).
The admin should be able to see the general details, the attached files, and the agenda selection, and should have the option to:
- Approve the candidate
- Decline the candidate
- Delete file(s) and set the profile as incomplete (and automatically send an email to the candidate)
- Remove agenda selection and set the profile as incomplete (and automatically send an email to the candidate)
*/
?>
<html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>ממשק ניהול מערכת רישום מועמדים</title>
    </head>
    <body dir="rtl">
		<h2>ממשק ניהול מערכת רישום מועמדים</h2>
		<a href="admin_logout.php">יציאה מהמערכת</a><br/>
		<br/>
		<br/>	
		<b>מועמדים שנרשמו וטרם אושרו:</b><br/>
		<table width="100%" border="1" style="font-size:10pt">
			<?php
			print_columns_headers();
			
			$con = connect();
			
			$query = "call ADMIN_USPS_CANDIDATES_WAITING()";
			$result = mysql_query($query);
			
			if ($result) {
				while ($row = mysql_fetch_array($result)) {
					echo "<tr>";
					print_column_personal_info($row);
					print_column_communication_info($row);
					print_column_party_info($row);
					print_column_files_info($row, true);
					print_column_agenda($row, true);	
					
					echo "<td><a href=\"\">אשר מועמד</a><br/><a href=\"\">דחה מועמד</a><br/><a href=\"\">מחק מועמד</a></td>";
					
					echo "</tr>";					
				}
			} else {
				$err = mysql_error();
				mysql_close($con);
				DIE("DB ERROR: " . $err);
			}
			
			mysql_close($con);
			
			?>
		</table>
		<br/>
		<br/>
		<b>מועמדים שטרם סיימו תהליך רישום:</b><br/>
		<table width="100%" border="1"	style="font-size:10pt">
			<?php
			print_columns_headers();
			
			$con = connect();
			
			$query = "call ADMIN_USPS_CANDIDATES_INCOMPLETE()";
			$result = mysql_query($query);
			
			if ($result) {
				while ($row = mysql_fetch_array($result)) {
					echo "<tr>";
					print_column_personal_info($row);
					print_column_communication_info($row);
					print_column_party_info($row);
					print_column_files_info($row, false);
					print_column_agenda($row, false);	
					
					echo "<td><a href=\"\">מחק מועמד</a></td>";
					
					echo "</tr>";
				}
			} else {
				$err = mysql_error();
				mysql_close($con);
				DIE("DB ERROR: " . $err);
			}
			
			mysql_close($con);
			
			?>
		</table>
		<br/>
		<br/>
		<b>מועמדים שאושרו:</b><br/>
		<span style="font-size:10pt">הסרת אישור תגרום להחזרת המועמד לסטטוס "טרם אושר".</span>
		<table width="100%" border="1"	style="font-size:10pt">
			<?php
			print_columns_headers();
			
			$con = connect();
			
			$query = "call ADMIN_USPS_CANDIDATES_APPROVED()";
			$result = mysql_query($query);
			
			if ($result) {
				while ($row = mysql_fetch_array($result)) {
					echo "<tr>";
					print_column_personal_info($row);
					print_column_communication_info($row);
					print_column_party_info($row);
					print_column_files_info($row, false);
					print_column_agenda($row, false);	
					
					echo "<td><a href=\"\">הסר אישור</a></td>";
					
					echo "</tr>";
				}
			} else {
				$err = mysql_error();
				mysql_close($con);
				DIE("DB ERROR: " . $err);
			}
			
			mysql_close($con);
			
			?>
		</table>		<br/>
		<br/>
		<b>מועמדים שנדחו:</b><br/>
		<span style="font-size:10pt">הסרת דחיה תגרום להחזרת המועמד לסטטוס "טרם אושר".</span>
		<table width="100%" border="1"	style="font-size:10pt">
			<?php
			print_columns_headers();
			
			$con = connect();
			
			$query = "call ADMIN_USPS_CANDIDATES_DECLINED()";
			$result = mysql_query($query);
			
			if ($result) {
				while ($row = mysql_fetch_array($result)) {
					echo "<tr>";
					print_column_personal_info($row);
					print_column_communication_info($row);
					print_column_party_info($row);
					print_column_files_info($row, false);
					print_column_agenda($row, false);	
					
					echo "<td><a href=\"\">הסר דחיה</a></td>";
					
					echo "</tr>";
				}
			} else {
				$err = mysql_error();
				mysql_close($con);
				DIE("DB ERROR: " . $err);
			}
			
			mysql_close($con);
			
			?>
		</table>
	</body>
</html>
