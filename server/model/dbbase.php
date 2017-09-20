<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';

class DBBase {
  protected static $db = null;
  protected static $curl = null;

  //Assumes curl and DB connection is already established!
  public static function init($db, $curl) {
    self::$db = $db;
    self::$curl = $curl;
  }
}
