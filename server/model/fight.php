<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/clocker.php';
include_once __DIR__.'/../util/wowdbutil.php';
include_once __DIR__.'/dbbase.php';
use \Util\DBObject, \Util\CurlObject, \Util\Cacher, \Util\Clocker, \Util\WoWDBUtil, \PDO, \PDOException;

class Fight extends DBBase {
  //private $id;
  private $reportId;
  private $fightId;
  private $boss;
  private $start;
  private $end;
  private $raidSize;
  private $difficulty;
  private $isKill;
  private $bossPercentage;
  private $fightPercentage;

  private $friendlyDict;

  private $characters;

  private function __construct($reportId, $fightJson, $friendlies, $fetchFromWCL=false) {
    $this->reportId = $reportId;
    $this->fightId = $fightJson['id'];
    $this->boss = $fightJson['boss'];
    $this->start = $fightJson['start_time'];
    $this->end = $fightJson['end_time'];
    $this->raidSize = $fightJson['size'];
    $this->difficulty = $fightJson['difficulty'];
    $this->isKill = $fightJson['kill'] == 'true' ? true : false;
    $this->bossPercentage = $fightJson['bossPercentage'];
    $this->fightPercentage = $fightJson['fightPercentage'];
    $this->friendlyDict = $friendlies;
    $this->characters = array();
    if($fetchFromWCL) {
      $this->fetchFromWCL();
    }
  }

  public static function fetchFromDB() {

  }

  private function fetchFromWCL() {
    if($json = $self::curl->curlWCLFightTable($name, $realm, $region)) {
    }
    return $result;
  }
}
