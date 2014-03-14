<?php

function db_connect(){

    $configString   = "";

    if (file_exists('config.json')) {
        $configString = file_get_contents("config.json");
    } elseif (file_exists('../config.json')) {
        $configString = file_get_contents("../config.json");
    } elseif (file_exists('../../config.json')) {
        $configString = file_get_contents("../../config.json");
    } else {
        $configString = file_get_contents("../../../config.json");
    }

    $config = json_decode($configString,true);

    $db_name       = $config["db_name"];
    $db_server     = $config["db_server"];
    $db_username   = $config["db_username"];
    $db_password   = $config["db_password"];

    $connection = @mysql_connect($db_server, $db_username, $db_password) or die(mysql_error());
    $db = @mysql_select_db($db_name, $connection) or die(mysql_error());

    return $connection;

}