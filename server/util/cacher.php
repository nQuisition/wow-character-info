<?php
namespace Util;

include_once __DIR__.'/../config/config.php';
use \Config\Config;

class Cacher {
  private function __construct() {

  }

  public static function cacheIcon($context, $icon) {
    $fullPath = __DIR__.'/..'.Config::ICON_ROOT.$context.'/'.$icon.'.jpg';
    if(file_exists($fullPath)) {
      return;
    }
    $imageContent = file_get_contents('https://render-eu.worldofwarcraft.com/icons/56/'.$icon.'.jpg');
    file_put_contents($fullPath, $imageContent);
  }
}
