<?php
namespace Model;

class CharacterItem {
  private $attributes = array(
    ':item' => -1,
    ':quality' => -1,
    ':ilvl' => 0,
    ':setList' => null,
    ':transmogItem' => null,
    ':bonusList' => null,
    ':enchant' => null
  );
  private $gems = array();
  private $relics = array();
  private $traits = null;

  public function __construct($json) {
    $this->attributes[':item'] = $json['id'];
    $this->attributes[':quality'] = $json['quality'];
    $this->attributes[':ilvl'] = $json['itemLevel'];
    if(isset($json['tooltipParams']['set'])) {
      $this->attributes[':setList'] = join($json['tooltipParams']['set']);
    }
    if(isset($json['tooltipParams']['transmogItem'])) {
      $this->attributes[':transmogItem'] = $json['tooltipParams']['transmogItem'];
    }
    if(isset($json['tooltipParams']['enchant'])) {
      $this->attributes[':enchant'] = $json['tooltipParams']['enchant'];
    }
    if(isset($json['bonusLists'])) {
      $this->attributes[':bonusList'] = join($json['bonusLists']);
    }
    for($i=0; $i<3; $i++) {
      if(isset($json['tooltipParams']['gem'.$i])) {
        $this->gems[] = $json['tooltipParams']['gem'.$i];
      }
    }
    if(isset($json['artifactTraits'])) {
      $this->traits = $json['artifactTraits'];
    }
    if(isset($json['relics'])) {
      $this->relics = $json['relics'];
    }
  }

  public function getBaseAttributes() {
    return $this->attributes;
  }

  public function getGems() {
    return $this->gems;
  }

  public function getRelics() {
    return $this->relics;
  }

  public function getTraits() {
    return $this->traits;
  }
}
