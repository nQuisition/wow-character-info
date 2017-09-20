<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/cacher.php';
include_once __DIR__.'/../util/clocker.php';
include_once __DIR__.'/../util/wowdbutil.php';
include_once __DIR__.'/dbbase.php';
include_once __DIR__.'/characteritem.php';
use \Util\DBObject, \Util\CurlObject, \Util\Cacher, \Util\Clocker, \Util\WoWDBUtil, \PDO, \PDOException;

class Character extends DBBase {
  //character
  private $attributes = array(
    'id' => -1,
    'instanceid' => -1,
    'name' => '',
    'realm' => '',
    'region' => '',
    'gender' => -1,
    'thumbnail' => '',
    'achievementPoints' => -1,
    'lastModified' => -1,
    'lastUpdated' => -1,

    //charinstance
    'ilvl' => -1,
    'ilvle' => -1
  );

  private $class = array(
    'id' => -1,
    'wclid' => -1,
    'name' => '',
    'wclname' => '',
    'color' => '',
    'icon' => ''
  );
  private $race = array(
    'id' => -1,
    'name' => '',
    'maleIcon' => '',
    'femaleIcon' => ''
  );
  private $activeSpec = array(
    'id' => -1,
    'name' => '',
    'role' => '',
    'icon' => ''
  );
  private $stats = array(
    'health' => -1,
    'str' => -1,
    'agi' => -1,
    'int' => -1,
    'sta' => -1,
    'crit' => -1,
    'critRating' => -1,
    'haste' => -1,
    'hasteRating' => -1,
    'mastery' => -1,
    'masteryRating' => -1,
    'versatility' => -1,
    'versatilityBonus' => -1
  );
  private $activeTalents = array();

  private $items = array();
  private $specs = array();

  private static $clocker = null;

  private function __construct($attrs) {
    foreach (array_keys(self::$attributes) as $key) {
      $this->attributes[$key] = isset($attrs[$key]) ? $attrs[$key] : null;
    }
    $this->class = WoWDBUtil::getClassInfo(self::$db, $attrs['class']);
    $this->race = WoWDBUtil::getRaceInfo(self::$db, $attrs['race']);
    $this->activeSpec = WOWDBUtil::getSpecInfo(self::$db, $attrs['activeSpec']);
    $this->activeTalents = WOWDBUtil::getTalentsForCharinstanceSpec(self::$db, $attrs['instanceid'], $attrs['activeSpec'])
    foreach (array_keys(self::$stats) as $key) {
      $this->stats[$key] = isset($attrs[$key]) ? $attrs[$key] : null;
    }
  }

  public function getId() {
    return $this->attributes['id'];
  }

  public function getInstanceId() {
    return $this->attributes['instanceid'];
  }

  public function getClassId() {
    return $this->class['id'];
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

  public function persist() {
    /*
    self::$db->beginTransaction();

    $talentIds = self::getTalentsFromJson($json);
    self::$clocker->clock("Got talent ids");
    $activeSpec = $talentIds[count($talentIds)-1];
    $ilvl = $json['items']['averageItemLevel'];
    $ilvle = $json['items']['averageItemLevelEquipped'];
    $lastModified = $json['lastModified'];
    $lastUpdated = round(microtime(true) * 1000);

    $insertStatement = self::$db->prepareStatement(
      "INSERT INTO `character` (name, realm, region, class, race, gender, thumbnail,
      achievementPoints, lastModified, activeSpec, ilvl, ilvle, lastUpdated,
      health, str, agi, `int`, sta, crit, critRating, haste, hasteRating, mastery, masteryRating, versatility, versatilityBonus)
      VALUES (:name, :realm, :region, :class, :race, :gender, :thumbnail,
      :achievementPoints, :lastModified, :activeSpec, :ilvl, :ilvle, :lastUpdated,
      :health, :str, :agi, :int, :sta, :crit, :critRating, :haste, :hasteRating, :mastery, :masteryRating, :versatility, :versatilityBonus);"
    );
    $insertParameters = array(
      ':name' => $name,
      ':realm' => $realm,
      ':region' => $region,
      ':class' => $json['class'],
      ':race' => $json['race'],
      ':gender' => $json['gender'],
      ':thumbnail' => $json['thumbnail'],
      ':achievementPoints' => $json['achievementPoints'],
      ':lastModified' => $lastModified,
      ':activeSpec' => $activeSpec,
      ':ilvl' => $ilvl,
      ':ilvle' => $ilvle,
      ':lastUpdated' => $lastUpdated,
      ':health' => $json['stats']['health'],
      ':str' => $json['stats']['str'],
      ':agi' => $json['stats']['agi'],
      ':int' => $json['stats']['int'],
      ':sta' => $json['stats']['sta'],
      ':crit' => $json['stats']['crit'],
      ':critRating' => $json['stats']['critRating'],
      ':haste' => $json['stats']['haste'],
      ':hasteRating' => $json['stats']['hasteRating'],
      ':mastery' => $json['stats']['mastery'],
      ':masteryRating' => $json['stats']['masteryRating'],
      ':versatility' => $json['stats']['versatility'],
      ':versatilityBonus' => $json['stats']['versatilityDamageDoneBonus']
    );
    $insertStatement->execute($insertParameters);
    self::$clocker->clock("Inserted character");
    $charId = self::$db->lastInsertId();
    self::insertTalents(array_slice($talentIds, 0, count($talentIds)-1), $charId);
    self::$clocker->clock("Inserted talents");

    //self::$clocker = new Clocker();
    $itemList = self::getItemsFromJson($json);
    self::$clocker->clock("Got items");
    self::insertItems($itemList, $charId);
    self::$clocker->clock("Inserted items");

    $result = new Character(self::getCharacterById($charId));

    self::$db->commit();
    self::$clocker->clock("Everything done");
    self::$clocker->getTotal();
      }
    }

    return $result;
    */
  }

  public static function init($db) {
    parent::init($db);
    self::$clocker = new Clocker();
  }

  public static function fetchCharacter($name, $realm, $region, $bnetFetch=false, $persist=false) {
    self::$clocker->clock("Starting character fetch");
    $result = self::fetchFromDB($name, $realm, $region);
    if (is_null($result) && $bnetFetch) {
      self::$clocker->clock("Fetching from BNet");
      $result = self::fetchFromBnet($name, $realm, $region);
      if($persist) {
        $result->persist();
      }
    }
    return $result;
  }

  public static function fetchFromDB($name, $realm, $region) {
    self::$clocker->clock("Fetching from DB");
    $result = null;
    if ($charInfo = self::getCheckCharacter($name, $realm, $region)) {
      $result = new Character($charInfo);
    } else {
      return null;
    }
    $charId = $result->getId();
    $classId = $result->getClassId();
    $result->setItems(self::getCharacterItems($charId));
    $result->setSpecs(self::getCharacterSpecs($charId, $classId));

    return $result;
  }

  public static function fetchFromBnet($name, $realm, $region) {
    self::$clocker->clock("Curl initialized");
    $result = null;
    if($json = $self::curl->curlCharacter($name, $realm, $region)) {
      $attrs = array();
      self::$clocker->clock("Curling complete");
      if(!(isset($json['status']) && $json['status'] == 'nok')) {
        $talents = self::getTalentsFromJson($json);

        self::$clocker->clock("Starting to fill in attrs");
        $attrs['name'] = $name;
        $attrs['realm'] = $realm;
        $attrs['region'] = $region;
        $attrs['class'] = $json['class'];
        $attrs['race'] = $json['race'];
        $attrs['gender'] = $json['gender'];
        $attrs['thumbnail'] = $json['thumbnail'];
        $attrs['achievementPoints'] = $json['achievementPoints'];
        $attrs['lastModified'] = $json['lastModified'];
        $attrs['activeSpec'] = $talents[count($talents)-1];
        unset($talents[count($talents)-1]);
        //$specInfo = WoWDBUtil::getSpecInfo($attrs['activeSpec']);
        //$attrs['specName'] = $specInfo['name'];
        //$attrs['specIcon'] = $specInfo['icon'];
        $specs = WoWDBUtil::getClassSpecs(self::$db, $attrs['class']);
        $specIndices = array();
        for($i = 0; $i < count($specs); $i++) {
          $specs[$i]['talents'] = array();
          if($specs[$i]['id'] == $attrs['activeSpec']) {
            $attrs['specName'] = $specs[$i]['name'];
            $attrs['specIcon'] = $specs[$i]['icon'];
          }
          $specIndices[$specs[$i]['id']] = $i;
        }

        $attrs['ilvl'] = $json['items']['averageItemLevel'];
        $attrs['ilvle'] = $json['items']['averageItemLevelEquipped'];
        $attrs['lastUpdated'] = round(microtime(true) * 1000);

        $attrs['health'] = $json['stats']['health'];
        $attrs['str'] = $json['stats']['str'];
        $attrs['agi'] = $json['stats']['agi'];
        $attrs['int'] = $json['stats']['int'];
        $attrs['sta'] = $json['stats']['sta'];
        $attrs['crit'] = $json['stats']['crit'];
        $attrs['critRating'] = $json['stats']['critRating'];
        $attrs['haste'] = $json['stats']['haste'];
        $attrs['hasteRating'] = $json['stats']['hasteRating'];
        $attrs['mastery'] = $json['stats']['mastery'];
        $attrs['masteryRating'] = $json['stats']['masteryRating'];
        $attrs['versatility'] = $json['stats']['versatility'];
        $attrs['versatilityBonus'] = $json['stats']['versatilityDamageDoneBonus'];
        self::$clocker->clock("Filling in attrs complete");

        $result = new Character($attrs);
        foreach($talents as $talent) {
          $specs[$specIndices[$talent['spec']]]['talents'][] = $talent;
        }
        $_it = self::getItemsFromJson($json);
        $items = array();
        foreach($_it as $item) {
          $items[] = $item->toAssocArray();
        }

        $result->setItems($items);
        $result->setSpecs($specs);

        //TODO doesn't work?
        //foreach ($specs as $value) {
        //  unset($value['id']);
        //}
      }
    }
    self::$clocker->clock("Everything done");
    self::$clocker->getTotal();
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

  private static function getItemsFromJson($json) {
    $result = array();
    $items = $json['items'];
    $itemSlots = WoWDBUtil::getItemSlots(self::$db);
    $_iq = WoWDBUtil::getItemQualities(self::$db);
    $itemQualities = array();
    foreach ($_iq as $value) {
      $itemQualities[$value['id']] = $value;
    }

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
      $characterItem->setSlot($slotId, $slotName);
      $characterItem->setQuality($itemQualities[$item['quality']]['name'], $itemQualities[$item['quality']]['color']);
      $result[] = $characterItem;
    }
    return $result;
  }

  //Last element of the returned array is the active spec id
  private static function getTalentsFromJson($json, $sort=true) {
    $result = array();
    $activeSpecId = -1;
    $talents = $json['talents'];

    $checkStatement = self::$db->prepareStatement(
      "SELECT * FROM talent WHERE spec=:spec AND tier=:tier AND `column`=:column;"
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
          $result[] = $row;
        }
        else {
          $talName = $talent['spell']['name'];
          $talTier = $talent['tier'];
          $talColumn = $talent['column'];
          $talSpellid = $talent['spell']['id'];
          $talIcon = $talent['spell']['icon'];
          Cacher::cacheIcon('spell', $talIcon);
          if($insertStatement->execute()) {
            $result[] = array(
              'id'      => self::$db->lastInsertId(),
              'name'    => $talName,
              'tier'    => $talTier,
              'column'  => $talColumn,
              'spellid' => $talSpellid,
              'icon'    => $talIcon,
              'spec'    => $specId
            );
          }
        }
      }
    }
    if($sort) {
      usort($result, function($a, $b) {
          return $a['tier']<$b['tier'] ? -1 : ($a['tier']>$b['tier'] ? 1 : 0);
      });
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
      "SELECT ch.*, ci.id AS instanceid, ci.activeSpec AS activeSpec, ci.ilvl AS ilvl, ci.ilvle AS ilvle, ci.health AS health, ci.str AS str,
      ci.agi AS agi, ci.`int` AS `int`, ci.sta AS sta, ci.crit AS crit, ci.critRating AS critRating, ci.haste AS haste,
      ci.hasteRating AS hasteRating, ci.mastery AS mastery, ci.masteryRating AS masteryRating, ci.versatility AS versatility, ci.versatilityBonus AS versatilityBonus
      FROM `character` ch
      INNER JOIN charinstance ci ON ch.id=ci.charid
      WHERE ch.id=:id AND ci.main=TRUE;"
    );
    $checkStatement->bindParam(':id', $id);
    if(!$checkStatement->execute()) {
      return false;
    }
    $char = $checkStatement->fetch(PDO::FETCH_ASSOC);
    //$char['thumbnail'] = Cacher::getCharacterThumbnailUrl($char['thumbnail']);
    return($char);
  }

  public static function getCharacterByInstanceId($id) {
    $checkStatement = self::$db->prepareStatement(
      "SELECT ch.*, ci.id AS instanceid, ci.activeSpec AS activeSpec, ci.ilvl AS ilvl, ci.ilvle AS ilvle, ci.health AS health, ci.str AS str,
      ci.agi AS agi, ci.`int` AS `int`, ci.sta AS sta, ci.crit AS crit, ci.critRating AS critRating, ci.haste AS haste,
      ci.hasteRating AS hasteRating, ci.mastery AS mastery, ci.masteryRating AS masteryRating, ci.versatility AS versatility, ci.versatilityBonus AS versatilityBonus
      FROM charinstance ci
      INNER JOIN `character` ch ON ch.id=ci.charid
      WHERE ci.id=:id;"
    );
    $checkStatement->bindParam(':id', $id);
    if(!$checkStatement->execute()) {
      return false;
    }
    $char = $checkStatement->fetch(PDO::FETCH_ASSOC);
    //$char['thumbnail'] = Cacher::getCharacterThumbnailUrl($char['thumbnail']);
    return($char);
  }

  private static function getCharacterItems($id, $instance=false) {
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
    $result = WoWDBUtil::getClassSpecs(self::$db, $class);
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
      //unset($talent['specId']);
      $result[$specIndices[$specId]]['talents'][] = $talent;
    }
    //TODO doesn't work?
    //foreach ($result as $value) {
    //  unset($value['id']);
    //}

    return $result;
  }
}
