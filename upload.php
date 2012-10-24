<?php
include "common.php";
$ERR_UNKNOWN = "err_unknown";
$ERR_SYSTEM = "err_system";
$ERR_BAD_SIZE = "bad_size";
$ERR_BAD_TYPE = "bad_type";
$ERR_BAD_FILE = "bad_file";

$CODE_SUCCESS = 0;
$CODE_ERR_UNKNOWN = -1;
$CODE_ERR_BAD_SIZE = -2;
$CODE_ERR_BAD_TYPE = -3;
$CODE_ERR_BAD_FILE = -4;
$CODE_ERR_DB = -5;

$TYPE_IMG = 1;
$TYPE_ID = 2;
$TYPE_CV = 3;

$ALLOWED_EXT = array(array("jpg", "jpeg", "gif", "png"), 
					 array("jpg", "jpeg", "gif", "png", "pdf"),
					 array("pdf"));

$ALLOWED_SIZES = array(524288, 524288, 1048576); // 512KB, 512KB, 1MB

$FILE_PREFIXES = array("img", "id", "cv");

function update_db($con, $user, $filename, $type) {
	global $TYPE_IMG, $TYPE_ID, $TYPE_CV;

	$query = "call REG_USPU_CANDIDATE_FILE('" . $user . "','" . $filename . "'," . ($type == $TYPE_IMG ? "1" : "0") . "," . ($type == $TYPE_ID ? "1" : "0") . "," . ($type == $TYPE_CV ? "1" : "0") . ");";
	if (!mysqli_multi_query($con, $query)) {
		return false;
	}
	$result = mysqli_store_result($con);
	if ($result) {
		mysqli_free_result($result);
		return true;
	} else {
		return false;
	}
}

function get_file_name($con, $user, $type) {
	global $TYPE_IMG, $TYPE_ID, $TYPE_CV;

	$query = "SELECT CA_FILE_IMAGE, CA_FILE_ZEHUT, CA_FILE_CV FROM CANDIDATES WHERE CA_USER = '" . mysqli_real_escape_string($con, $user) . "' LIMIT 1";
	if (!mysqli_multi_query($con, $query)) {
		return false;
	}
	$result = mysqli_store_result($con);
	if ($result) {
		if ($row = mysqli_fetch_array($result)) {
			switch($type) {
				case $TYPE_IMG:
					$res = $row["CA_FILE_IMAGE"];
					mysqli_free_result($result);
					return $res;
				case $TYPE_ID:
					$res = $row["CA_FILE_ZEHUT"];
					mysqli_free_result($result);
					return $res;
				case $TYPE_CV:
					$res = $row["CA_FILE_CV"];
					mysqli_free_result($result);
					return $res;
			}
		} else {
			mysqli_free_result($result);
			return false;
		}
	} else {
		return false;
	}
}

function do_upload($user, $field, $type) {
	global $TYPE_IMG, $TYPE_CV, $TYPE_ID, $ALLOWED_SIZES, $ALLOWED_EXT, $FILE_PREFIXES, $CODE_ERR_BAD_FILE, $CODE_ERR_DB, $CODE_ERR_BAD_SIZE, $CODE_ERR_BAD_TYPE, $CODE_SUCCESS;

	$allowedSize = $ALLOWED_SIZES[$type - 1];
	$allowedExts = $ALLOWED_EXT[$type - 1];
	$prefix = $FILE_PREFIXES[$type - 1];

	$extension = end(explode(".", $_FILES[$field]["name"]));
	
	$con = connect();
	
	if (($_FILES[$field]["size"] <= $allowedSize) && in_array($extension, $allowedExts)) {
		if ($_FILES[$field]["error"] > 0) {
			mysqli_close($con);
			return $CODE_ERR_BAD_FILE;
		} else {
			$path = "cand_data/";
			if (!($file_name = get_file_name($con, $user, $type)) || ($file_name == null)) {
				do {
					$file_name = $prefix . "_" . uniqid() . "." . $extension;
				} while (file_exists($path . $file_name));
			}
			
			move_uploaded_file($_FILES[$field]["tmp_name"],	$path . $file_name);
	
			// Update database
			if (!update_db($con, $user, $file_name, $type)) {
				unlink($path . $file_name);
				mysqli_close($con);
				return $CODE_ERR_DB;
			}			
		}
	} else {
		if ($_FILES[$field]["size"] > $allowedSize) {
			mysqli_close($con);
			return $CODE_ERR_BAD_SIZE;
		} else {
			mysqli_close($con);
			return $CODE_ERR_BAD_TYPE;
		}
	}
	mysqli_close($con);
	return $CODE_SUCCESS;
}

session_start();
if (!isset($_SESSION['CA_USER']) || !isset($_SESSION['CA_NAME'])) {
    header( 'Location: login.php?err=nologin' ) ;
    exit();
}

$type = null;
if ($_POST["uploadtype"] == "photo") {
	$type = $TYPE_IMG;
} else if ($_POST["uploadtype"] == "id") {
	$type = $TYPE_ID;
} else if ($_POST["uploadtype"] == "resume") {
	$type = $TYPE_CV;
}

if (type == null) {
	header( 'Location: files.php?err=bad_request' );
	exit();
} else {
	$res = do_upload($_SESSION['CA_USER'], "file", $type);
	if ($res != $CODE_SUCCESS) {
		$err = "";
		switch($res) {
			case $CODE_ERR_UNKNOWN:
				$err = $ERR_UNKNOWN;
				break;
			case $CODE_ERR_BAD_SIZE:
				$err = $ERR_BAD_SIZE;
				break;
			case $CODE_ERR_BAD_TYPE:
				$err = $ERR_BAD_TYPE;
				break;
			case $CODE_ERR_BAD_FILE:
				$err = $ERR_BAD_FILE;
				break;
			case $CODE_ERR_DB:
				$err = $ERR_SYSTEM;
				break;
		}
		header( 'Location: files.php?err=' . $err );
		exit();
	} else {
		// Success
		header( 'Location: files.php?success');
	}
}

?>
