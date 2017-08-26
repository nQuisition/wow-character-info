<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/cacher.php';
use \Util\DBObject, \Util\CurlObject, \Util\Cacher, \PDO, \PDOException;

class Character {
  private $attributes = array();
  private static $db = null;
  private static $keys = array('id', 'name', 'realm', 'region', 'className', 'classColor', 'classIcon', 'raceName',
    'gender', 'thumbnail', 'achievementPoints', 'lastModified', 'ilvl', 'ilvle', 'lastUpdated');

  private function __construct($attrs) {
    foreach (self::$keys as $key) {
      $this->attributes[$key] = isset($attrs[$key]) ? $attrs[$key] : null;
    }
  }

  public static function fetchCharacter($name, $realm, $region) {
    self::$db = DBObject::getDBObject();
    self::$db->establishConnection();
    $result = null;
    if ($charInfo = self::checkCharacterExists($name, $realm, $region)) {
      $result = new Character($charInfo);
    } else {
      $result = self::createCharacter($name, $realm, $region);
    }
    self::$db->closeConnection();
    return $result;
  }

  private static function createCharacter($name, $realm, $region) {
    $curl = CurlObject::getCurlObject();
    $curl->init();
    $result = null;
    if($json = $curl->curlCharacter($name, $realm, $region)) {
      $talentIds = self::getTalentIds($json);
      $activeSpec = $talentIds[count($talentIds)-1];
      $ilvl = 0;
      $ilvle = 0;
      $lastModified = $json['lastModified']/1000;
      $lastUpdated = time();

      $insertStatement = self::$db->prepareStatement(
        "INSERT INTO `character` (name, realm, region, class, race, gender, thumbnail,
        achievementPoints, lastModified, activeSpec, ilvl, ilvle, lastUpdated)
        VALUES (:name, :realm, :region, :class, :race, :gender, :thumbnail,
        :achievementPoints, :lastModified, :activeSpec, :ilvl, :ilvle, :lastUpdated);"
      );
      $insertStatement->bindParam(':name', $name);
      $insertStatement->bindParam(':realm', $realm);
      $insertStatement->bindParam(':region', $region);
      $insertStatement->bindParam(':class', $json['class']);
      $insertStatement->bindParam(':race', $json['race']);
      $insertStatement->bindParam(':gender', $json['gender']);
      $insertStatement->bindParam(':thumbnail', $json['thumbnail']);
      $insertStatement->bindParam(':achievementPoints', $json['achievementPoints']);
      $insertStatement->bindParam(':lastModified', $lastModified);
      $insertStatement->bindParam(':activeSpec', $activeSpec);
      $insertStatement->bindParam(':ilvl', $ilvl);
      $insertStatement->bindParam(':ilvle', $ilvle);
      $insertStatement->bindParam(':lastUpdated', $lastUpdated);
      $insertStatement->execute();
      $charId = self::$db->lastInsertId();
      self::insertTalents(array_slice($talentIds, 0, count($talentIds)-1), $charId);
    }
    $curl->closeConnection();
    return $result;
  }

  private static function insertTalents($talentIds, $charId) {
    $insertStatement = self::$db->prepareStatement(
      "INSERT INTO character_talent (`character`, talent)
      VALUES (:character, :talent);"
    );
    $insertStatement->bindParam(':character', $charId);
    $insertStatement->bindParam(':talent', $talId);
    foreach($talentIds as $talentId) {
      $talId = $talentId;
      $insertStatement->execute();
    }
  }

  //Last element of the returned array is the active spec id
  private static function getTalentIds($json) {
    $result = array();
    $activeSpecId = -1;
    $talents = $json['talents'];
    foreach($talents as $talentSet) {
      if(!isset($talentSet['spec']))
        continue;
      $spec = $talentSet['spec'];
      $specId = self::getSpecId($json['class'], $spec);
      if(isset($talentSet['selected']) && $talentSet['selected']) {
        $activeSpecId = $specId;
      }
      $talentArray = $talentSet['talents'];

      $checkStatement = self::$db->prepareStatement(
        "SELECT id FROM talent WHERE spec=:spec AND tier=:tier AND `column`=:column;"
      );
      $checkStatement->bindParam(':spec', $specId);
      $checkStatement->bindParam(':tier', $tier);
      $checkStatement->bindParam(':column', $column);

      $insertStatement = self::$db->prepareStatement(
        "INSERT INTO talent (name, tier, `column`, spellid, icon, spec)
        VALUES (:name, :tier, :column, :spellid, :icon, :spec);"
      );
      $insertStatement->bindParam(':name', $talName);
      $insertStatement->bindParam(':tier', $talTier);
      $insertStatement->bindParam(':column', $talColumn);
      $insertStatement->bindParam(':spellid', $talSpellid);
      $insertStatement->bindParam(':icon', $talIcon);
      $insertStatement->bindParam(':spec', $specId);

      foreach($talentArray as $talent) {
        $tier = $talent['tier'];
        $column = $talent['column'];
        $checkStatement->execute();
        if($row = $checkStatement->fetch(PDO::FETCH_ASSOC)) {
          $result[] = $row['id'];
        }
        else {
          $talName = $talent['spell']['name'];
          $talTier = $talent['tier'];
          $talColumn = $talent['column'];
          $talSpellid = $talent['spell']['id'];
          $talIcon = $talent['spell']['icon'];
          Cacher::cacheIcon('spell', $talIcon);
          if($insertStatement->execute()) {
            $result[] = self::$db->lastInsertId();
          }
        }
      }
    }
    $result[] = $activeSpecId;
    return $result;
  }

  private static function getSpecId($class, $spec) {
    $checkStatement = self::$db->prepareStatement(
      "SELECT id FROM spec WHERE class=:class AND name=:name;"
    );
    $checkStatement->bindParam(':name', $spec['name']);
    $checkStatement->bindParam(':class', $class);
    if(!$checkStatement->execute()) {
      return false;
    }
    if($row = $checkStatement->fetch(PDO::FETCH_ASSOC)) {
      return $row['id'];
    }
    $insertStatement = self::$db->prepareStatement(
      "INSERT INTO spec (class, name, role, `order`, backgroundImage, icon)
      VALUES (:class, :name, :role, :order, :backgroundImage, :icon);"
    );
    $insertStatement->bindParam(':class', $class);
    $insertStatement->bindParam(':name', $spec['name']);
    $insertStatement->bindParam(':role', $spec['role']);
    $insertStatement->bindParam(':order', $spec['order']);
    $insertStatement->bindParam(':backgroundImage', $spec['backgroundImage']);
    $insertStatement->bindParam(':icon', $spec['icon']);
    Cacher::cacheIcon('spec', $spec['icon']);
    if(!$insertStatement->execute()) {
      return false;
    }
    return self::$db->lastInsertId();
  }

  private static function checkCharacterExists($name, $realm, $region) {
    $checkStatement = self::$db->prepareStatement(
      "SELECT ch.*, cl.name AS className, cl.color AS classColor, cl.icon AS classIcon, ra.name AS raceName
      FROM `character` ch
      INNER JOIN class cl ON ch.class=cl.id
      INNER JOIN race ra ON ch.race=ra.id
      WHERE ch.name=:name AND ch.realm=:realm AND ch.region=:region;"
    );
    $checkStatement->bindParam(':name', $name);
    $checkStatement->bindParam(':realm', $realm);
    $checkStatement->bindParam(':region', $region);
    if(!$checkStatement->execute()) {
      return false;
    }
    return($checkStatement->fetch(PDO::FETCH_ASSOC));
  }

  public function vardump() {
    var_dump($this->attributes);
  }
}
