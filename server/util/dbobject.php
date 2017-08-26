<?php
include_once '../config/config.php';
use \Config\Config;

namespace Util;

class DBObject {
  private static $dbobject = null;
  private $conn = null;

  private function __construct() {

  }

  public static function getDBObject() {
    if(!isset(self::dbobject)) {
      self::dbobject = new DBObject();
    }
    return self::dbobject;
  }

  public function establishConnection($reconnect=false) {
    if(isset($this->conn) && !is_null($this->conn)) {
      if($reconnect)
        $this->closeConnection();
      else
        return;
    }
    try {
      $this->conn = new PDO("mysql:host=".Config::DBHOST.";dbname=".Config::DBNAME.";charset=utf8", Config::DBUSER, Config::DBPASSWORD);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
      exit("Connection failed: " . $e->getMessage());
    }
  }

  public function prepareStatement($statement) {
    return $this->conn->prepare($statement);
  }

  public function closeConnection() {
    $this->conn = null;
  }
}
