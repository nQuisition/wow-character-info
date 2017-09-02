<?php
namespace Util;

include_once __DIR__.'/dbobject.php';
use \PDO, \PDOException;

class WoWDBUtil {
  private static $dbobject=null;

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
  public static function getClassInfo($class) {
    self::checkDBObject();
    $stmt = self::$dbobject->prepareStatement(
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
  public static function getRaceInfo($race) {
    self::checkDBObject();
    $stmt = self::$dbobject->prepareStatement(
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
  public static function getSpecInfo($spec) {
    self::checkDBObject();
    $stmt = self::$dbobject->prepareStatement(
      "SELECT * FROM spec WHERE id=:id;"
    );
    $stmt->execute(array(':id' => $spec));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function getClassSpecs($class) {
    self::checkDBObject();
    $stmt = self::$dbobject->prepareStatement(
      "SELECT id, name, role, backgroundImage, icon
      FROM spec WHERE class=:class ORDER BY `order` ASC;"
    );
    $stmt->execute(array(':class' => $class));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getItemSlots() {
    self::checkDBObject();
    $stmt = self::$dbobject->prepareStatement(
      "SELECT id, name FROM itemslot;"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getItemQualities() {
    self::checkDBObject();
    $stmt = self::$dbobject->prepareStatement(
      "SELECT id, name, color FROM itemquality;"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private static function checkDBObject() {
    if(self::$dbobject == null) {
      self::$dbobject = DBObject::getDBObject();
    }
  }
}
