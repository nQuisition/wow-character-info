<?php
namespace Util;

class Clocker {
  private $lastTime;
  private $startTime;

  public function __construct() {
    $this->lastTime = microtime(true);
    $this->startTime = microtime(true);
  }

  public function clock($message) {
    if(!defined('_DEBUG'))
      return;
    $diff = microtime(true)-$this->lastTime;
    echo $message.'. Elapsed: '.$diff."s\n";
    $this->lastTime = microtime(true);
  }

  public function getTotal() {
    if(!defined('_DEBUG'))
      return;
    $diff = microtime(true)-$this->startTime;
    echo 'Total time elapsed: '.$diff."s\n";
  }

  public function reset() {
    $this->lastTime = microtime(true);
    $this->startTime = microtime(true);
  }
}
