<?php
include "common.php";
check_session();

function update_completed($user, $completed) {
    $con = connect();
    $query = 'UPDATE CANDIDATES SET CA_COMPLETED = "' . ($completed ? "NOW()" : "NULL") 
        . '" WHERE CA_USER = "' . mysql_real_escape_string($user) . '";';
	$result = mysql_query($query);
    mysql_close($con);
    if ($result) {
        header("Content-Type:application/json; charset=utf-8");
        printf('{ "type": "success", "value": {} }');
    } else {
        DIE("{ type: 'error', msg: 'ERR_BAD_STATE' }");
    }
}
update_completed($_SESSION['CA_USER'], true);

?>

