<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/cacher.php';
include_once __DIR__.'/../util/clocker.php';
include_once __DIR__.'/characteritem.php';
use \Util\DBObject, \Util\CurlObject, \Util\Cacher, \Util\Clocker, \PDO, \PDOException;

class Character {
  private $attributes = array();
  private $items = array();
  private $specs = array();
  private static $clocker = null;
  private static $db = null;
  private static $keys = array('id', 'name', 'realm', 'region', 'class', 'className', 'classColor', 'classIcon', 'raceName',
    'gender', 'thumbnail', 'achievementPoints', 'lastModified', 'specName', 'specIcon', 'ilvl', 'ilvle', 'lastUpdated');

  private function __construct($attrs) {
    foreach (self::$keys as $key) {
      $this->attributes[$key] = isset($attrs[$key]) ? $attrs[$key] : null;
    }
  }

  public function getId() {
    return $this->attributes['id'];
  }

  public function getClassId() {
    return $this->attributes['class'];
  }

  public function getBaseJson() {
    return json_encode($this->attributes);
  }

  public function getJson($params) {
    $base = $this->attributes;
    if(in_array('items', $params)) {
      $base['items'] = $this->items;
    }
    if(in_array('specs', $params)) {
      $base['specs'] = $this->specs;
    }

    return json_encode($base);
  }

  public function setItems($items) {
    $this->items = $items;
  }

  public function setSpecs($specs) {
    $this->specs = $specs;
  }

  public static function fetchCharacter($name, $realm, $region) {
    self::$clocker = new Clocker();
    self::$db = DBObject::getDBObject();
    self::$db->establishConnection();
    self::$clocker->clock("Connection to db established");
    $result = null;
    if ($charInfo = self::getCheckCharacter($name, $realm, $region)) {
      $result = new Character($charInfo);
    } else {
      self::$clocker->clock("GotCheckeded character info");
      $result = self::createCharacter($name, $realm, $region);
    }
    if($result != null) {
      $charId = $result->getId();
      $classId = $result->getClassId();
      $result->setItems(self::getCharacterItems($charId));
      $result->setSpecs(self::getCharacterSpecs($charId, $classId));
    }
    self::$db->closeConnection();
    return $result;
  }

  private static function createCharacter($name, $realm, $region) {
    $curl = CurlObject::getCurlObject();
    $curl->init();
    self::$clocker->clock("Curl initialized");
    $result = null;
    if($json = $curl->curlCharacter($name, $realm, $region)) {
      self::$clocker->clock("Curling complete");
      if(!(isset($json['status']) && $json['status'] == 'nok')) {
        self::$db->beginTransaction();

        $talentIds = self::getTalentIds($json);
        self::$clocker->clock("Got talent ids");
        $activeSpec = $talentIds[count($talentIds)-1];
        $ilvl = $json['items']['averageItemLevel'];
        $ilvle = $json['items']['averageItemLevelEquipped'];
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
        self::$clocker->clock("Inserted character");
        $charId = self::$db->lastInsertId();
        self::insertTalents(array_slice($talentIds, 0, count($talentIds)-1), $charId);
        self::$clocker->clock("Inserted talents");

        //self::$clocker = new Clocker();
        $itemList = self::getItems($json);
        self::$clocker->clock("Got items");
        self::insertItems($itemList, $charId);
        self::$clocker->clock("Inserted items");

        $result = new Character(self::getCharacterById($charId));

        self::$db->commit();
        self::$clocker->clock("Everything done");
        self::$clocker->getTotal();
      }
    }
    $curl->closeConnection();
    return $result;
  }

  private static function insertItems($itemList, $charId) {
    $itemStatement = self::$db->prepareStatement(
      "INSERT INTO character_item (`character`, item, quality, ilvl, setList, transmogItem, bonusList, enchant)
      VALUES (:character, :item, :quality, :ilvl, :setList, :transmogItem, :bonusList, :enchant);"
    );

    $gemStatement = self::$db->prepareStatement(
      "INSERT INTO character_item_gem (character_item, gemid)
      VALUES (:character_item, :gemid);"
    );
    $gemStatement->bindParam(':character_item', $charItemId);
    $gemStatement->bindParam(':gemid', $gemId);

    $relicStatement = self::$db->prepareStatement(
      "INSERT INTO character_item_relic (character_item, relicid, bonusList)
      VALUES (:character_item, :relicid, :bonusList);"
    );
    $relicStatement->bindParam(':character_item', $charItemId);
    $relicStatement->bindParam(':relicid', $relicId);
    $relicStatement->bindParam(':bonusList', $relicBonusList);

    $traitStatement = self::$db->prepareStatement(
      "INSERT INTO character_item_trait (character_item, traitid, rank)
      VALUES (:character_item, :traitid, :rank);"
    );
    $traitStatement->bindParam(':character_item', $charItemId);
    $traitStatement->bindParam(':traitid', $traitId);
    $traitStatement->bindParam(':rank', $traitRank);

    foreach($itemList as $item) {
      $attrs = $item->getBaseAttributes();
      $attrs[':character'] = $charId;
      $itemStatement->execute($attrs);
      $gems = $item->getGems();
      $relics = $item->getRelics();
      $traits = $item->getTraits();
      if(count($gems)>0 || count($relics)>0 || count($traits)>0) {
        $charItemId = self::$db->lastInsertId();
        foreach ($gems as $gemId) {
          $gemStatement->execute();
        }
        foreach ($relics as $relic) {
          $relicId = $relic['itemId'];
          $relicBonusList = isset($relic['bonusLists']) ? join(':', $relic['bonusLists']) : null;
          $relicStatement->execute();
        }
        foreach ($traits as $trait) {
          $traitId = $trait['id'];
          $traitRank = $trait['rank'];
          $traitStatement->execute();
        }
      }
    }
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

  private static function getItems($json) {
    $result = array();
    $items = $json['items'];
    $itemSlotStatement = self::$db->prepareStatement(
      "SELECT id, name FROM itemslot;"
    );
    $itemSlotStatement->execute();
    $itemSlots = $itemSlotStatement->fetchAll(PDO::FETCH_ASSOC);

    $insertStatement = self::$db->prepareStatement(
      "INSERT IGNORE INTO item (id, name, icon, slot)
      VALUES (:id, :name, :icon, :slot);"
    );
    $insertStatement->bindParam(':id', $itemId);
    $insertStatement->bindParam(':name', $itemName);
    $insertStatement->bindParam(':icon', $itemIcon);
    $insertStatement->bindParam(':slot', $slotId);

    foreach($itemSlots as $slot) {
      $slotName = $slot['name'];
      $slotId = $slot['id'];
      if(!isset($items[$slotName]))
        continue;
      $item = $items[$slotName];
      $itemId = $item['id'];
      $itemName = $item['name'];
      $itemIcon = $item['icon'];
      Cacher::cacheIcon('item', $itemIcon);
      $insertStatement->execute();
      $characterItem = new CharacterItem($item);
      $result[] = $characterItem;
    }
    return $result;
  }

  //Last element of the returned array is the active spec id
  private static function getTalentIds($json) {
    $result = array();
    $activeSpecId = -1;
    $talents = $json['talents'];

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

    foreach($talents as $talentSet) {
      if(!isset($talentSet['spec']))
        continue;
      $spec = $talentSet['spec'];
      $specId = self::getSpecId($json['class'], $spec);
      if(isset($talentSet['selected']) && $talentSet['selected']) {
        $activeSpecId = $specId;
      }
      $talentArray = $talentSet['talents'];

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

  private static function getCheckCharacter($name, $realm, $region) {
    $checkStatement = self::$db->prepareStatement(
      "SELECT id
      FROM `character`
      WHERE name=:name AND realm=:realm AND region=:region;"
    );
    $checkStatement->bindParam(':name', $name);
    $checkStatement->bindParam(':realm', $realm);
    $checkStatement->bindParam(':region', $region);
    if(!$checkStatement->execute()) {
      return false;
    }
    if($row = $checkStatement->fetch(PDO::FETCH_ASSOC)) {
      return self::getCharacterById($row['id']);
    }
    return false;
  }

  public static function getCharacterById($id) {
    $checkStatement = self::$db->prepareStatement(
      "SELECT ch.*, cl.name AS className, cl.color AS classColor, cl.icon AS classIcon, ra.name AS raceName, sp.name AS specName, sp.icon AS specIcon
      FROM `character` ch
      INNER JOIN class cl ON ch.class=cl.id
      INNER JOIN race ra ON ch.race=ra.id
      INNER JOIN spec sp ON ch.activeSpec=sp.id
      WHERE ch.id=:id;"
    );
    $checkStatement->bindParam(':id', $id);
    if(!$checkStatement->execute()) {
      return false;
    }
    $char = $checkStatement->fetch(PDO::FETCH_ASSOC);
    $char['thumbnail'] = Cacher::getCharacterThumbnailUrl($char['thumbnail']);
    return($char);
  }

  private static function getCharacterItems($id) {
    $itemStatement = self::$db->prepareStatement(
      "SELECT chit.id AS chitId, it.id AS id, it.name AS name, it.icon AS icon, isl.id AS slotNum, isl.name AS slot, iq.name AS quality, iq.color AS qualityColor,
      chit.ilvl AS ilvl, chit.setList AS setList, chit.transmogItem AS transmogItem, chit.bonusList AS bonusList, chit.enchant AS enchant
      FROM character_item chit
      INNER JOIN item it ON chit.item=it.id
      INNER JOIN itemslot isl ON it.slot=isl.id
      INNER JOIN itemquality iq ON chit.quality=iq.id
      WHERE chit.`character`=:id ORDER BY slotNum ASC;"
    );
    $gemStatement = self::$db->prepareStatement(
      "SELECT cig.character_item AS chitId, cig.gemid as gemId
      FROM character_item chit
      INNER JOIN character_item_gem cig ON cig.character_item=chit.id
      WHERE chit.`character`=:id;"
    );
    $itemStatement->execute(array(':id' => $id));
    $items = $itemStatement->fetchAll(PDO::FETCH_ASSOC);
    $gemStatement->execute(array(':id' => $id));
    $gems = $gemStatement->fetchAll(PDO::FETCH_ASSOC);
    $result = array();
    foreach($items as $item) {
      $chitId = $item['chitId'];
      unset($item['chitId']);
      $item['gems'] = array();
      foreach($gems as $gem) {
        if($gem['chitId'] == $chitId) {
          $item['gems'][] = $gem['gemId'];
        }
      }
      $result[] = $item;
    }

    return $result;
  }

  private static function getCharacterSpecs($id, $class) {
    $specStatement = self::$db->prepareStatement(
      "SELECT id, name, role, backgroundImage, icon
      FROM spec WHERE class=:class ORDER BY `order` ASC;"
    );
    $specStatement->execute(array(':class' => $class));
    $result = $specStatement->fetchAll(PDO::FETCH_ASSOC);
    $specIndices = array();
    for($i = 0; $i < count($result); $i++) {
      $result[$i]['talents'] = array();
      $specIndices[$result[$i]['id']] = $i;
    }
    $talentStatement = self::$db->prepareStatement(
      "SELECT tal.name as name, tal.tier as tier, tal.`column` as `column`, tal.spellid as spellid,
      tal.icon as icon, sp.id as specId
      FROM character_talent ct
      INNER JOIN talent tal ON ct.talent=tal.id
      INNER JOIN spec sp ON tal.spec=sp.id
      WHERE ct.character=:id
      ORDER BY sp.`order` ASC, tier ASC;"
    );
    $talentStatement->execute(array(':id' => $id));
    $talents = $talentStatement->fetchAll(PDO::FETCH_ASSOC);
    foreach($talents as $talent) {
      $specId = $talent['specId'];
      unset($talent['specId']);
      $result[$specIndices[$specId]]['talents'][] = $talent;
    }
    //TODO doesn't work?
    foreach ($result as $value) {
      unset($value['id']);
    }

    return $result;
  }
}
