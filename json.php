<?php
include "common.php";

function query_to_array($query, $processor = NULL) {
    $result = array();
    while ($obj = mysql_fetch_assoc($query)) {
        if ($processor) {
            $obj = call_user_func($processor, $obj);
        }
        array_push($result, $obj);
    }
    return $result;
}

function do_links($data, $output) {
    $links = array();
    if ($data['CA_WEBSITE']) {
        array_push($links, array(
            'title' => 'website',
            'url' => $data['CA_WEBSITE']
        ));
    }
    if ($data['CA_FACEBOOK']) {
        array_push($links, array(
            'title' => 'facebook',
            'url' => $data['CA_FACEBOOK']
        ));
    }
    if ($links) {
        $output['links'] = $links;
    }
    return $output;
}

$fields_names = array(
    'CA_ID' => 'id',
    'PA_NAME_HE' => 'party_name',
    'RE_NAME_HE' => 'district',
    'CA_AGENDAS' => 'agendas',
    'CA_FILE_IMAGE' => 'img_url',
    'CA_FILE_CV' => 'resume_link_url',
);

function do_fields_dict($data, $output) {
    global $fields_names;
    foreach (array_keys($fields_names) as $orig_name) {
        $new_name = $fields_names[$orig_name];
        $output[$new_name] = $data[$orig_name];
    }
    return $output;
}

function do_placeholder($data, $output) {
    $output['is_placeholder'] = (bool)$data['CA_EMPTY'];
    return $output;
}

function do_name($data, $output) {
    $name = $data['CA_FNAME'];
    if ($data['CA_LNAME']) {
        $name = $name . ' ' . $data['CA_LNAME'];
    }
    $output['name'] = $name;
    return $output;
}

$relatives = array('img_url', 'resume_link_url');
function do_relative_url($data, $output) {
    global $relatives;
    foreach(array_values($relatives) as $relative_field) {
        $output[$relative_field] = str_replace(
            'json.php',
            'cand_data/' . $output[$relative_field],
            $_SERVER['SCRIPT_URI']
        );
    }
    return $output;
}

function conversion($obj) {
    $new_obj = array();
    $convertors = array('do_fields_dict', 'do_name', 'do_links', 'do_relative_url', 'do_placeholder');
    foreach(array_values($convertors) as $convertor) {
        $new_obj = call_user_func($convertor, $obj, $new_obj);
    }
    return $new_obj;
}

function jsonp_requested() {
    $jsonp = strpos($_SERVER['HTTP_ACCEPT'], '/javascript');
    $json = strpos($_SERVER['HTTP_ACCEPT'], '/json');
    return $_GET['callback'] or $jsonp !== false and ($json === false or $jsonp < $json);
}

function output_data($data) {
    if (jsonp_requested()) {
        $callback = $_GET['callback'] or 'callback';
        header( 'Content-Type:text/javascript; charset=utf-8' );
        echo $callback . '(' . $data . ')';
        return;
    }
    header( 'Content-Type:text/json; charset=utf-8' );
    echo $data;
}

$con = connect();
$res = mysql_query("call ADMIN_USPS_CANDIDATES_APPROVED();");
if (!$res) {
    $err = mysql_real_escape_string(mysql_error());
    mysql_close($con);
    DIE("{ type: 'error', msg: 'DB_QUERY', details: '" . $err . "'}");
}

$data = array('objects' => query_to_array($res, 'conversion'));
$data = json_encode($data);
output_data($data);
?>
