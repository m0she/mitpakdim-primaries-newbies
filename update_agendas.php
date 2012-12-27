<?php
include "common.php";
check_session();

function update_agendas($user, $agendas) {
    $con = connect();
    $query = 'UPDATE CANDIDATES SET '.
             'CA_AGENDAS = "' . mysql_real_escape_string($agendas) . '", ' .
             'CA_STATUS = 1 ' .
             ' WHERE CA_USER = "' . mysql_real_escape_string($user) . '";';
	$result = mysql_query($query);
    mysql_close($con);
    if ($result) {
        header("Content-Type:application/json; charset=utf-8");
        printf('{ "type": "success", "value": {} }');
    } else {
        DIE("{ type: 'error', msg: 'ERR_BAD_STATE' }");
    }
}
update_agendas($_SESSION['CA_USER'], $_POST['agendas']);

?>
