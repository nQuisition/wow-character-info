<?php
namespace Model;

include_once __DIR__.'/../util/dbobject.php';
include_once __DIR__.'/../util/curlobject.php';
include_once __DIR__.'/../util/clocker.php';
include_once __DIR__.'/../util/wowdbutil.php';
include_once __DIR__.'/dbbase.php';
use \Util\DBObject, \Util\CurlObject, \Util\Cacher, \Util\Clocker, \Util\WoWDBUtil, \PDO, \PDOException;

class Report extends DBBase {
  private $reportId;
  private $start;
  private $end;

  private $fights = array();

  //TODO $zone!
  private function __construct($id, $start, $end) {
    $this->reportId = $id;
    $this->start = $start;
    $this->end = $end;
  }

  public function addFight($fight) {
    $fights[] = $fight;
  }

  public static function fetchFromWCL($reportId, $reportStart, $reportEnd) {
    $result = new Report($reportId, $reportStart, $reportEnd);
    if($json = $self::curl->curlWCLReport($reportId)) {
      $friendlies = array();
      foreach($json['friendlies'] as $value) {
        $friendlies[$value['id']] = $value['name'];
      }

      foreach($json['fights'] as $fightJson) {
        if(!isset($fightJson['boss'] || $fightJson['boss'] <= 0))
          continue;

        $fight = new Fight($reportId, $fightJson, $friendlies, true);
        $result->addFight($fight);
      }
    }
    return $result;
  }
}
