<?php
namespace Util;

include_once __DIR__.'/../config/config.php';
use \Config\Config;

class CurlObject {
  private static $curlobject = null;
  private $curl = null;

  private function __construct() {

  }

  public static function getCurlObject() {
    if(!isset(self::$curlobject)) {
      self::$curlobject = new CurlObject();
    }
    return self::$curlobject;
  }

  public function init($reconnect=false) {
    if(isset($this->curl) && !is_null($this->curl)) {
      if($reconnect)
        $this->closeConnection();
      else
        return;
    }
    $this->curl=curl_init();
  }

  public function curlCharacter($name, $realm, $region) {
    curl_setopt($this->curl, CURLOPT_URL, 'https://eu.api.battle.net/wow/character/'.$realm.'/'.$name.'?fields=stats,items,talents&locale=en_GB&apikey='.Config::BNET_API_KEY);
    curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    $buffer = curl_exec($this->curl);

    if (empty($buffer)) {
      return false;
    }

    return json_decode($buffer, true);
  }

  public function closeConnection() {
    curl_close($this->curl);
  }
}
