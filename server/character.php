<?php
include_once __DIR__.'/model/character.php';
use \Model\Character;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
if(!isset($_GET['action']) || !isset($_GET['name']) || !isset($_GET['realm']) || !isset($_GET['region'])) {
  header('HTTP/1.1 400 Missing Parameters');
  die(json_encode(array('error' => 'One or more parameters are missing', 'code' => 400)));
} else {
  $action = $_GET['action'];
  $name = $_GET['name'];
  $realm = $_GET['realm'];
  $region = $_GET['region'];
  $char = Character::fetchCharacter($name, $realm, $region);
  if(is_null($char)) {
    header('HTTP/1.1 400 Character Not Found');
    die(json_encode(array('error' => 'Character not found', 'code' => 400)));
  }
  if($action == 'base') {
    echo $char->getBaseJson();
  }
  else {
    die(json_encode(array('error' => 'Invalid operation')));
  }
}
