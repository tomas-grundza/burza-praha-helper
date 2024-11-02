<?php
//global variables
$develop = 1;
$time_start = microtime(true);
$output = array();
$output['error'] = 0;

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $output['http_origin'] =  $_SERVER['HTTP_ORIGIN'];
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600');
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

  exit(0);
}

//environment settings
setlocale(LC_ALL, 'cs_CZ.UTF-8');
ini_set('display_errors', $develop);
ini_set('display_startup_errors', $develop);
error_reporting(E_ALL);

//db credentials & API token
$dbServer = "127.0.0.1";
$dbUser = "tomasgrundzaDipl";
$dbName = "tomasgrundzaDipl";
$dbPass = "**********";
$apiToken = "**********";

//validate token exists and is valid
if (!isset($_GET['token']) || $_GET['token'] != $apiToken) {
  $output['errorCode'] = 401;
  $output['errorMessage'] = "Unauthorized. (401-001)";
  goto endOfCode;
}

//connect to the db
$mysqli = new mysqli($dbServer, $dbUser, $dbPass, $dbName);

if ($mysqli->connect_error) {
  $output['error'] = 1;
  $output['errorCode'] = 500;
  $output['errorMessage'] = "Internal server error. Unable to establish connection with database. (500-002)";
  goto endOfCode;
}

$mysqli->query("SET NAMES 'utf8'");

//sql inject prevention fc
function osetri($promenna){
  global $mysqli;
  $promenna = $mysqli->real_escape_string(htmlspecialchars(trim($promenna)));
  return $promenna;
}

//parse values as numbers fc
function getNumber($x){
  $arrayToDelete = array("Kč", "€", "$", "£", "%", "ks", " ", " ");
  $endDeleted = str_replace($arrayToDelete, '', $x);
  $returnValue = str_replace(",", '.', $endDeleted);
  return rtrim($returnValue);
}

//get currency from string fc
function getCurrency($x){
  $curencyRaw = trim(substr($x, -3));
  switch ($curencyRaw) {
    case 'Kč':  $currency = "CZK"; break;
    case '€':  $currency = "EUR"; break;
    case '$':  $currency = "USD"; break;
    case '£':  $currency = "GBP"; break;
    default: $currency = $currencyRaw; break;
  }
  return $currency;
}

//array for SQL query
$sqlArray = array();

//parse input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$output['date'] = $input['date'];

foreach ($input['items'] as $value) {
  unset($sqlString);
  unset($uniqueKey);

  $uniqueKey = $output['date']."_".$value['isin'];

  $sqlString = "
    (
      NULL,
      '".$output['date']."',
      '".$value['jmeno']."',
      '".$value['isin']."',
      '".getNumber($value['kurz'])."', '".getCurrency($value['kurz'])."',
      '".getNumber($value['zmena'])."',
      '".getNumber($value['pocet'])."',
      '".getNumber($value['objem'])."',
      '".$uniqueKey."'
    )";

  array_push($sqlArray, $sqlString);

}

//insert in to db
$query = "INSERT INTO `rawData` (`id`, `date`, `jmeno`, `isin`, `kurz`, `mena`, `zmena`, `pocet`, `objem`, `uniqueKey`) VALUES ";

foreach ($sqlArray as $key => $value) {
  $query .= $value;
  if ($key != array_key_last($sqlArray)) {
        $query .= ", ";
    }
}

if (!$mysqli->query($query)) {
  $output['errorCode'] = 500;
  $output['errorMessage'] = "Internal server error (500-001)";
  goto endOfCode;
}

$output['success'] = 1;

//close db connection
$mysqli->close();

endOfCode:

$output['executionTime'] = (microtime(true) - $time_start);

if (isset($output['errorCode'])) {
  $output['error'] = 1;
  http_response_code($output['errorCode']);
}

$outputJSON = json_encode($output);

header('Content-Type: application/json; charset=utf-8');
echo $outputJSON;

?>
