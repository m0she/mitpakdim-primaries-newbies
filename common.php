<?php
session_start();
include "settings.php";

function connect() {
    global $settings;
    $db = $settings["db"]["readwrite"];
    $con = mysql_connect($db["hostname"],$db["username"], $db["password"]) OR DIE ("{ type: 'error', msg: 'DB_CONN' }");
    mysql_select_db($db["dbname"]);
    mysql_query("SET NAMES utf8");
    return $con;
}

function connect_readonly() {
    global $settings;
    $db = $settings["db"]["readonly"];
    $con = mysql_connect($db["hostname"],$db["username"], $db["password"]) OR DIE ("{ type: 'error', msg: 'DB_CONN' }");
    mysql_select_db($db["dbname"]);
    return $con;
}

?>
