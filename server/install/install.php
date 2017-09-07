<?php
namespace Install;

include_once __DIR__.'/../util/cacher.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/dbobject.php';

use \Util\DBObject, \Util\CurlObject, \Util\Cacher;

$db = DBObject::getDBObject();
$db->establishConnection();

//TODO get classes

//TODO get races

//Specs and talents
$curl = CurlObject::getCurlObject();
$curl->init();
if($json = $curl->curlTalents()) {
  //TODO create tables?
  $db->beginTransaction();
  $specStatement = $db->prepareStatement(
    "INSERT IGNORE INTO spec(class, name, role, `order`, backgroundImage, icon)
    VALUES (:class, :name, :role, :order, :backgroundImage, :icon);"
  );
  $talentStatement = $db->prepareStatement(
    "INSERT IGNORE INTO talent(name, tier, `column`, spellid, icon, spec)
    VALUES (:name, :tier, :column, :spellid, :icon, :spec);"
  );
  $classId = 1;
  $totalCounter = 0;
  while(true) {

    if(!isset($json[$classId])) {
      break;
    }
    $classBlock = $json[$classId];
    echo "Processing class ".$classId." ".$classBlock['class']."\n";
    $specIds = array();
    foreach($classBlock['specs'] as $spec) {
      $specStatement->execute(array(
        ':class' => $classId,
        ':name' => $spec['name'],
        ':role' => $spec['role'],
        ':order' => $spec['order'],
        ':backgroundImage' => $spec['backgroundImage'],
        ':icon' => $spec['icon']
      ));
      $specIds[$spec['name']] = $db->lastInsertId();
      Cacher::cacheIcon('spec', $spec['icon']);
    }

    foreach($classBlock['talents'] as $talentTier) {
      foreach($talentTier as $talentSlot) {
        $specs = $specIds;
        $multiTalent = null;
        foreach($talentSlot as $talent) {
          if(!isset($talent['spec'])) {
            $multiTalent = $talent;
            continue;
          }
          $talentStatement->execute(array(
            ':name' => $talent['spell']['name'],
            ':tier' => $talent['tier'],
            ':column' => $talent['column'],
            ':spellid' => $talent['spell']['id'],
            ':icon' => $talent['spell']['icon'],
            ':spec' => $specIds[$talent['spec']['name']]
          ));
          Cacher::cacheIcon('spell', $talent['spell']['icon']);
          unset($specs[$talent['spec']['name']]);
          $totalCounter++;
        }

        if(!is_null($multiTalent)) {
          foreach ($specs as $specId) {
            $talentStatement->execute(array(
              ':name' => $multiTalent['spell']['name'],
              ':tier' => $multiTalent['tier'],
              ':column' => $multiTalent['column'],
              ':spellid' => $multiTalent['spell']['id'],
              ':icon' => $multiTalent['spell']['icon'],
              ':spec' => $specId
            ));
            $totalCounter++;
          }
          Cacher::cacheIcon('spell', $multiTalent['spell']['icon']);
        }
      }
    }
    $classId++;
  }
  echo $totalCounter;
  $db->commit();
}

$db->closeConnection();
