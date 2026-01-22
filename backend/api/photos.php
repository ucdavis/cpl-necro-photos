<?php

require_once '../config/config.php'; // for DB config
require_once '../classes/dbConn.class.php';


$input_year = filter_input(INPUT_GET,'year',FILTER_SANITIZE_NUMBER_INT);
$photo_id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);

//var_dump($photo_id);
$params = [];
$where = '';


// New DB connection
try {
    $dbConn = new dbConn(DB_HOST, DB_USER, DB_PW, DB_NAME); // establish DB connection

    //var_dump($input_year);
    $params[] = substr($input_year,2,2);

    //print_r($params);

    if($photo_id){
        $where .= ' AND id = ? ';
        $params[] = $photo_id;
    }

    $q = "SELECT id, cpl_num, suffix, year, filename, size, date_uploaded, login
        FROM photos
        WHERE (year = ? $where )
        ORDER BY id DESC";

    $rst = $dbConn->query($q,$params);

    //echo '<pre>';
    echo json_encode($rst,JSON_PRETTY_PRINT);

    /*
    {
    "id": 6841,
    "cpl_num": "0047",
    "suffix": "J",
    "year": 22,
    "filename": "0047-22J-a.jpg",
    "size": 1309359,
    "date_uploaded": "2022-01-04 16:16:50",
    "login": "necropsy"
    },*/

} catch (mysqli_sql_exception $e) {
    echo 'ERROR: ['.$e->getMessage().']';
}


