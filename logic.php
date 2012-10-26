<?php
include "common.php";

function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255) {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local)) {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain)) {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

//	$firstname = mysql_real_escape_string($firstname);

function list_parties() {
	$con = connect();
	if (!$con) {
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . mysql_real_escape_string(mysql_error()) . "'}");
	}
	
	$query = "call REG_USPS_PARTY();";
	//$query = "SELECT PA_ID, PA_NAME_HE FROM PARTY WHERE PA_SHOW = 1";
	$result = mysql_query($query, $con);
	if ($result) {
		$res = array();
		while ($row = mysql_fetch_array($result)) {
			array_push($res, "{ id: " . $row['PA_ID'] . ", name: '" . $row['PA_NAME_HE'] . "'}");
		}
		printf("{ type: 'success', value: [" . join(",", $res) . "]}");
	} else {
		$err = mysql_real_escape_string(mysql_error());
		mysql_close($con);
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
	}
	mysql_close($con);
}

function list_regions($party) {
	if (!is_numeric($party)) {
		DIE("{type: 'error', msg: 'BAD_PARTY_ID', details: 'Party identifier must be numeric'}");
	}

	$con = connect();
	
	$query = "call REG_USPS_REGIONS(" . $party . ")";
	$result = mysql_query($query);
	if ($result) {
		$res = array();
		while ($row = mysql_fetch_array($result)) {
			array_push($res, "{ id: " . $row['RE_ID'] . ", name: '" . $row['RE_NAME_HE'] . "'}");
		}
		printf("{ type: 'success', value: [" . join(",", $res) . "]}");
	} else {
		$err = mysql_real_escape_string(mysql_error());
		mysql_close($con);
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
	}
	mysql_close($con);
}

function new_candidate($fname, $lname, $zehut, $party, $region, $email, $phone, $website, $facebook) {
	if (strlen($fname) < 2) DIE("{type: 'error', msg: 'BAD_FIRST_NAME' }");
	if (strlen($lname) < 2) DIE("{type: 'error', msg: 'BAD_LAST_NAME' }");
	if (strlen($phone) < 7) DIE("{type: 'error', msg: 'BAD_PHONE' }");
	if (!validEmail($email)) DIE("{type: 'error', msg: 'BAD_EMAIL' }");
	
	if (!is_numeric($zehut) || $zehut > 999999999) DIE("{type: 'error', msg: 'BAD_ZEHUT' }");
	if (!is_numeric($party)) DIE("{type: 'error', msg: 'BAD_PARTY_ID' }");
	if (!is_numeric($region)) DIE("{type: 'error', msg: 'BAD_REGION_ID' }");
	
	$con = connect();
	
	$fname = mysql_real_escape_string($fname);
	$lname = mysql_real_escape_string($lname);
	$email = mysql_real_escape_string($email);
	$website = mysql_real_escape_string($website);
	$facebook = mysql_real_escape_string($facebook);
	
	$zehut_valid = false;
	$candidate_published = false;
	$query = "call REG_USPS_ZEHUT_EXISTS(" . $zehut . ")";
	$result = mysql_query($query);
	if ($result) {
		if ($row = mysql_fetch_array($result)) {
			if ($row['CA_APPROVEDATE'] != null) {
				$candidate_published = true;
			}
		} else {
			$zehut_valid = true;
		}
	} else {
		$err = mysql_real_escape_string(mysql_error());
		mysql_close($con);
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
	}
	
	mysql_close($con);
	
	if (!$zehut_valid) {
		if ($candidate_published) {
			DIE("{ type: 'error', msg: 'המועמד/ת כבר סיים/ה לבנות פרופיל במערכת.'}");			
		} else {
			DIE("{ type: 'error', msg: 'קיים פרופיל עבור המועמד/ת שטרם הושלם. יש להכנס למערכת עם הקוד שניתן בעבר ולהשלימו.'}");
		}
	}

	$con = connect();
	
	$email_confkey = sha1("email conf " . date("Y-m-d H:i:s") . $zehut . uniqid());

	$query = "call REG_USPI_CANDIDATE('" . date("Y-m-d H:i:s") . "', '" . $fname . "', '" . $lname . "', " . $zehut . ", " . $party . ", " . $region . ", '" . $email . "', '" . $phone . "', '" . $website . "', '" . $facebook . "', '" . $_SERVER['REMOTE_ADDR'] . "','" . $email_confkey . "')";
	$result = mysql_query($query);
	if ($result) {
		$res = array();
		if ($row = mysql_fetch_array($result)) {
			printf("{ type: 'success', value: '%s' }", $row['CA_USER']);
			$_SESSION['CA_USER'] = $row['CA_USER'];
			$_SESSION['CA_NAME'] = $fname . ' ' . $lname;
		} else {
			mysql_close($con);
			DIE("{ type: 'error', msg: 'ERR_NO_USERID' }");
		}
	} else {
		$err = mysql_real_escape_string(mysql_error());
		mysql_close($con);
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
	}
	mysql_close($con);
	
	send_confirm_mail($email, $fname . ' ' . $lname, $_SESSION['CA_USER'], $email_confkey);
}

function resend_confirmation($user) {
	$con = connect();
	
	$query = "SELECT CA_EMAIL_CONFKEY, CA_EMAIL, CA_FNAME, CA_LNAME FROM CANDIDATES WHERE CA_USER = '" . $user . "' AND CA_EMAIL_CONFDATE IS NULL AND CA_COMPLETED IS NULL";
	$result = mysql_query($query);
	if ($result) {
		if ($row = mysql_fetch_array($result)) {
			send_confirm_mail($row['CA_EMAIL'], $row['CA_FNAME'] . ' ' . $row['CA_LNAME'], $user, $row['CA_EMAIL_CONFKEY']);
			printf("{ type: 'success', value: '{}' }");
		} else {
			mysql_close($con);
			DIE("{ type: 'error', msg: 'ERR_BAD_STATE' }");
		}
	} else {
		$err = mysql_real_escape_string(mysql_error());
		mysql_close($con);
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
	}
	mysql_close($con);
}

function send_confirm_mail($email, $name, $user, $confkey) {
	// multiple recipients
	$from = "מתפקדים <info@mitpakdim.co.il>";
	$to  = $email;
	$bcc = "info@mitpakdim.co.il";

	// subject
	$subject = 'רישום למערכת פריימריז';

	// message
	$message = '
	<html>
	<head>
	  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
	  <title>רישום למערכת פריימריז</title>
	</head>
	<body>
	  שלום ' . $name . ',<br/>
	  <br/>
	  נרשמת בהצלחה למערכת. הקוד האישי שלך הינו ' . $user . '. קוד זה ישמש אותך להמשך בניית הפרופיל שלך.<br/>
	  <br/>
	  לאישור כתובת הדואר האלקטרוני יש להכנס ללינק הבא: http://www.mitpakdim.co.il/site/primaries/candidates/email_conf.php?key=' . $confkey . 
	  '.<br/><br/>
	  לאחר מכן תוכלו להכנס למערכת דרך דף הכניסה: http://www.mitpakdim.co.il/site/primaries/candidates/login.php<br/>
	  <br/>
	  לאחר השלמת כל הפרטים והמסמכים הדרושים, יש לאשר את הפרופיל על מנת ששמך יופיע בתוצאות המוצגות למשתמשים במערכת.<br/>
	  <b>חשוב להשלים את הפרטים ולאשרם מוקדם ככל האפשר!</b><br/>
	  <br/>
	  בכל שאלה, אנא פנו אלינו במייל info@mitpakdim.co.il.<br/>
	  <br/>
	  בברכה,<br/>
	  צוות "מתפקדים" ו"כנסת פתוחה"
	</body>
	</html>
	';

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

	// Additional headers
	$headers .= 'From: ' . $from . "\r\n";
	$headers .= 'Bcc: ' . $bcc . "\r\n";

	// Mail it
	mail($to, $subject, $message, $headers);
}

function get_candidate_status($user) {
	$con = connect();
	
	$user = mysql_real_escape_string($user);
	
	$query = "call REG_USPS_CAND_STATUS('" . $user . "')";
	$result = mysql_query($query);
	if ($result) {
		if ($row = mysql_fetch_array($result)) {
			$email_conf = $row["CA_EMAIL_CONFDATE"];
			$email = $row["CA_EMAIL"];
			$image = $row["CA_FILE_IMAGE"];
			$cv = $row["CA_FILE_CV"];
			$zehut = $row["CA_FILE_ZEHUT"];
			$agenda = $row["CA_AGENDAS"];
			$completed = $row["CA_COMPLETED"];
			
			if ($completed != null) {
				printf("{ type: 'success', value: { status: 'COMPLETED' } }");
			} else {
				printf("{ type: 'success', value: { status: 'INCOMPLETE', email_conf: %s, email: '%s', file_image: %s, file_cv: %s, file_zehut: %s, has_agenda: %s } }",
					   ($email_conf == null ? 'false' : 'true'),
					   $email,
				       ($image == null ? 'false' : 'true'),
				       ($cv == null ? 'false' : 'true'),
				       ($zehut == null ? 'false' : 'true'),
				       ($agenda ? 'false' : 'true'));
			}
		} else {
			mysql_close($con);
			DIE("{ type: 'error', msg: 'NO_USER' }");
		}
	} else {
		$err = mysql_real_escape_string(mysql_error());
		mysql_close($con);
		DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
	}
	mysql_close($con);
}

function read_post() {
	$method = $_REQUEST["method"];
	
	if ($method == 'list_parties') {
		list_parties();
	} else if ($method == 'list_regions') {
		$party = $_POST['party'] OR DIE ("{ type: 'error', msg: 'BAD_PARTY_ID' }");
		list_regions($party);
	} else if ($method == 'new_candidate') {
		$fname = $_POST['fname'] OR DIE ("{ type: 'error', msg: 'BAD_FIRST_NAME' }");
		$lname = $_POST['lname'] OR DIE ("{ type: 'error', msg: 'BAD_LAST_NAME' }");
		$zehut = $_POST['zehut'] OR DIE ("{ type: 'error', msg: 'BAD_ZEHUT' }");
		$party = $_POST['party'] OR DIE ("{ type: 'error', msg: 'BAD_PARTY_ID' }");
		$region = $_POST['region'];// OR DIE ("{ type: 'error', msg: 'BAD_REGION_ID' }");
		$email = $_POST['email'] OR DIE ("{ type: 'error', msg: 'BAD_EMAIL' }");
		$phone = $_POST['phone'] OR DIE ("{ type: 'error', msg: 'BAD_PHONE' }");
		$website = $_POST['website'];// OR DIE ("{ type: 'error', msg: 'BAD_WEBSITE' }");
		$facebook = $_POST['facebook'];// OR DIE ("{ type: 'error', msg: 'BAD_FACEBOOK' }");
		new_candidate($fname, $lname, $zehut, $party, $region, $email, $phone, $website, $facebook);
	} else if ($method == 'get_candidate_status') {
		if (!isset($_SESSION['CA_USER'])) {
			DIE("{ type: 'error', msg: 'BAD_LOGIN' }");
		} else {
			get_candidate_status($_SESSION['CA_USER']);
		}
	} else if ($method == 'resend_confirmation') {
		if (!isset($_SESSION['CA_USER'])) {
			DIE("{ type: 'error', msg: 'BAD_LOGIN' }");
		} else {
			resend_confirmation($_SESSION['CA_USER']);
		}	
	} else {
		DIE("{ type: 'error', msg: 'BAD_METHOD' }");
	}
}

read_post();
?>
