<?php
namespace Util;

include_once __DIR__.'/../config/config.php';
use \Config\Config;

class CurlObject {
  private $curl = null;

  public function __construct() {

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

  private function curlURL($url) {
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    $buffer = curl_exec($this->curl);

    if (empty($buffer)) {
      return false;
    }

    return json_decode($buffer, true);
  }

  public function curlCharacter($name, $realm, $region) {
    return $this->curlURL('https://eu.api.battle.net/wow/character/'.$realm.'/'.$name.'?fields=stats,items,talents&locale=en_GB&apikey='.Config::BNET_API_KEY);
  }

  public function curlTalents() {
    return $this->curlURL('https://eu.api.battle.net/wow/data/talents?locale=en_GB&apikey='.Config::BNET_API_KEY);
  }

  public function curlWCLZones() {
    return $this->curlURL('https://www.warcraftlogs.com:443/v1/zones?api_key='.Config::WCL_API_KEY);
  }

  public function curlWCLReports($startTime=0) {
    return $this->curlURL('https://www.warcraftlogs.com:443/v1/reports/guild/'
        .Config::GUILD_NAME.'/'.Config::GUILD_REALM.'/'.Config::GUILD_REGION.'?start='.$startTime.'&api_key='.Config::WCL_API_KEY);
  }

  public function curlWCLReport($reportId) {
    return $this->curlURL('https://www.warcraftlogs.com:443/v1/report/tables/'
        .$view.'/'.$reportId.'?start='.$start.'&end='.$end.'&api_key='.Config::WCL_API_KEY);
  }

  public function curlWCLFightTable($reportId, $start, $end, $view) {
    return $this->curlURL('https://www.warcraftlogs.com:443/v1/report/fights/'.$reportId.'?api_key='.Config::WCL_API_KEY);
  }

  public function closeConnection() {
    curl_close($this->curl);
  }
}
