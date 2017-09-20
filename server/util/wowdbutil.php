<?php
namespace Util;

include_once __DIR__.'/dbobject.php';
use \PDO, \PDOException;

class WoWDBUtil {
  private function __construct() {

  }

  /**
   * Get class info by class id
   *
   * array['id']    Class id.
   * array['wlid']  Warcraftlogs class id.
   * array['name']  Class name.
   * array['color'] Class color.
   * array['icon']  Class icon.
   *
   * @param int $class Class id
   * @return array Class info as array (see above)
   */
  public static function getClassInfo($db, $class) {
    $stmt = $db->prepareStatement(
      "SELECT * FROM class WHERE id=:id;"
    );
    $stmt->execute(array(':id' => $class));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get race info by race id
   *
   * array['id']          Race id.
   * array['name']        Race name.
   * array['maleIcon']    Race male icon.
   * array['femaleIcon']  Race female icon.
   *
   * @param int $race Race id
   * @return array Race info as array (see above)
   */
  public static function getRaceInfo($db, $race) {
    $stmt = $db->prepareStatement(
      "SELECT * FROM race WHERE id=:id;"
    );
    $stmt->execute(array(':id' => $race));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get spec info by spec id
   *
   * array['id']              Spec id.
   * array['class']           Class id.
   * array['name']            Spec name.
   * array['role']            Role(dps/healer/tank).
   * array['order']           Spec order in the class.
   * array['backgroundImage'] Background image.
   * array['icon']            Spec icon.
   *
   * @param int $spec Spec id
   * @return array Spec info as array (see above)
   */
  public static function getSpecInfo($db, $spec) {
    $stmt = $db->prepareStatement(
      "SELECT * FROM spec WHERE id=:id;"
    );
    $stmt->execute(array(':id' => $spec));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function getTalentsForCharSpec($db, $char, $spec) {
    $stmt = $db->prepareStatement(
      "SELECT tal.*
      FROM talent tal
      INNER JOIN charinstance_talent chit ON tal.id=chit.talent
      WHERE chit.charid=:charid AND chit.main=TRUE AND tal.spec=:specid;"
    );
    $stmt->execute(array(':charid' => $char, ':specid' => $spec));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function getTalentsForCharinstanceSpec($db, $charinstance, $spec) {
    $stmt = $db->prepareStatement(
      "SELECT tal.*
      FROM talent tal
      INNER JOIN charinstance_talent chit ON tal.id=chit.talent
      WHERE chit.id=:instanceid AND tal.spec=:specid;"
    );
    $stmt->execute(array(':instanceid' => $charinstance, ':specid' => $spec));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function getClassSpecs($db, $class) {
    $stmt = $db->prepareStatement(
      "SELECT id, name, role, backgroundImage, icon
      FROM spec WHERE class=:class ORDER BY `order` ASC;"
    );
    $stmt->execute(array(':class' => $class));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getClassTalents($db, $class) {
    $stmt = $db->prepareStatement(
      "SELECT tal.id AS id, tal.name AS name, tal.tier AS tier, tal.`column` AS `column`,
      tal.spellid AS spellid, tal.icon AS icon, tal.spec AS spec
      FROM talent tal
      INNER JOIN spec sp ON tal.spec=sp.id
      WHERE sp.class=:class ORDER BY spec ASC, tier ASC;"
    );
    $stmt->execute(array(':class' => $class));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getClassTalentsGrouped($db, $class) {
    $stmt = $db->prepareStatement(
      "SELECT tal.spellid AS index_id, tal.id AS id, tal.name AS name, tal.tier AS tier,
      tal.`column` AS `column`, tal.spellid AS spellid, tal.icon AS icon
      FROM talent tal
      INNER JOIN spec sp ON tal.spec=sp.id
      WHERE sp.class=:class GROUP BY index_id ORDER BY index_id ASC;"
    );
    $stmt->execute(array(':class' => $class));
    return $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
  }

  public static function getItemSlots($db) {
    $stmt = $db->prepareStatement(
      "SELECT id, name FROM itemslot;"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getItemQualities($db) {
    $stmt = $db->prepareStatement(
      "SELECT id, name, color FROM itemquality;"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
