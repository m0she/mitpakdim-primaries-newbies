<?php
include "common.php";
header( 'Content-Type:text/javascript; charset=utf-8' );

function dump_table($table) {
    $con = connect_readonly();
    $fields = array();
    $describe_res = mysql_query("DESCRIBE ". $table . ";");
    while ($field = mysql_fetch_row($describe_res)) {
        array_push($fields, $field[0]);
    }
    //var_dump($fields);
    echo "{\n  \"objects\": [\n    ";
    $res = mysql_query("SELECT * FROM ". $table . ";");
    //var_dump($res);
    $first = true;
    while ($row = mysql_fetch_row($res)) {
        if ($first) {
            $first = false;
        } else {
            echo ",\n    ";
        }
        echo "{\n        ";
        foreach (array_keys($fields) as $index) {
            if ($index > 0) {
                echo ",\n        ";
            }
            echo json_encode($fields[$index]) . ': ' . json_encode($row[$index]);
        }
        echo "\n    }\n    ";
    }
    echo "\n    ]\n}";
	mysql_close($con);
}

//dump_table('CANDIDATES');
//dump_table('PARTY');
dump_table('REGION');

?>
