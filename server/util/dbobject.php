<?php
namespace Util;

include_once __DIR__.'/../config/config.php';
use \Config\Config, \PDO, \PDOException;

class DBObject {
  private $conn = null;
  private $inTransaction = false;

  public function __construct() {

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

  public function lastInsertId() {
    return $this->conn->lastInsertId();
  }

  public function beginTransaction() {
    if($this->inTransaction)
      return false;
    $result = $this->conn->beginTransaction();
    if($result)
      $this->inTransaction = true;
    return $result;
  }

  public function commit() {
    if(!$this->inTransaction)
      return false;
    $result = $this->conn->commit();
    $this->inTransaction = false;
    return $result;
  }

  public function rollBack() {
    if(!$this->inTransaction)
      return false;
    $result = $this->conn->rollBack();
    $this->inTransaction = false;
    return $result;
  }

  public function closeConnection() {
    $this->conn = null;
  }
}
