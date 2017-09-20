<?php
include_once __DIR__.'/model/character.php';
include_once __DIR__.'/util/dbobject.php';
include_once __DIR__.'/util/curlobject.php';
use \Model\Character, \Util\DBObject, \Util\CurlObject;

define('_RELEASE', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
$db = new DBObject();
$db->establishConnection();
$curl = new CurlObject();
$curl->init();

Character::init($db, $curl);
if(!isset($_GET['name']) || !isset($_GET['realm']) || !isset($_GET['region'])) {
  header('HTTP/1.1 400 Missing Parameters');
  die(json_encode(array('error' => 'One or more parameters are missing', 'code' => 400)));
} else {
  $action = 'base';
  if(isset($_GET['action']))
    $action = $_GET['action'];
  $name = $_GET['name'];
  $realm = $_GET['realm'];
  $region = $_GET['region'];
  $char = Character::fetchCharacter($name, $realm, $region, true);
  if(is_null($char)) {
    header('HTTP/1.1 400 Character Not Found');
    die(json_encode(array('error' => 'Character not found', 'code' => 400)));
  }
  if($action == 'base') {
    echo $char->getBaseJson();
  } else {
    $params = explode(',', $action);
    echo $char->getJson($params);
  }
}

$db->closeConnection();
$curl->closeConnection();
