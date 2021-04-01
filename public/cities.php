<?php
$string = file_get_contents('./cities.json');
$json_a = json_decode($string, true);
$names = array_column($json_a, 'name');
$newNames = array_filter($names, function ($var) {
    $term = $_GET['term'];

    return strpos(mb_strtolower($var), mb_strtolower($term)) !== false;
});
header('Content-Type: application/json');
echo json_encode($newNames);

