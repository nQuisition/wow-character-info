<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/cacher.php';
include_once __DIR__.'/../util/clocker.php';
include_once __DIR__.'/../util/wowdbutil.php';
include_once __DIR__.'/dbbase.php';

use \Util\DBObject, \Util\CurlObject, \Util\Cacher, \Util\Clocker, \Util\WoWDBUtil, \PDO, \PDOException;

class Boss extends DBBase {
  private $id;
  private $name;

  public function __construct($id, $name) {
    $this->$id = $id;
    $this->$name = $name;
  }

  public function getId() {
    return $this->id;
  }

  public function getName() {
    return $this->name;
  }

  public static function getFromDB($id) {

  }
}
