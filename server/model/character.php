<?php
include_once '../util/dbobject.php';
include_once '../util/curlobject.php';
use \Util\DBObject, \Util\CurlObject;

namespace Model;

class Character {
  private $attributes = array();
  private static $keys = array('id', 'name', 'realm', 'region', 'className', 'classColor', 'classIcon', 'raceName',
    'gender', 'thumbnail', 'achievementPoints', 'lastModified', 'ilvl', 'ilvle', 'lastUpdated');

  private function __construct($attrs) {
    foreach (self::keys as $key) {
      $attributes[$key] = isset($attrs[$key]) ? $attrs[$key] : null;
    }
  }

  public static function fetchCharacter($name, $realm, $region) {
    $db = DBObject::getDBObject();
    $db->establishConnection();
    $result = null;
    if ($charInfo = self::checkCharacterExists($db, $name, $realm, $region)) {
      $result = new Character($charInfo);
    } else {
      $result = self::createCharacter($db, $name, $realm, $region);
    }
    $db->closeConnection();
    return $result;
  }

  private static function createCharacter($db, $name, $realm, $region) {
    $curl = CurlObject::getCurlObject();
    $curl->init();
    $json = $curl->curlCharacter($name, $realm, $region);
    $curl->closeConnection();
  }

  private static function checkCharacterExists($db, $name, $realm, $region) {
    $checkStatement = $db->prepareStatement("SELECT ch.*, cl.name AS className, cl.color AS classColor, cl.icon AS classIcon, ra.name AS raceName
      FROM character ch
      INNER JOIN class cl ON ch.class=cl.id
      INNER JOIN race ra ON ch.race=ra.id
      WHERE ch.name=:name AND ch.realm=:realm AND ch.region=:region;");
    $checkStatement->bindParam(':name', $name);
    $checkStatement->bindParam(':realm', $realm);
    $checkStatement->bindParam(':region', $region);
    if(!$checkStatement->execute()) {
      return false;
    }
    return($checkStatement->fetch(PDO::FETCH_ASSOC));
  }
}
