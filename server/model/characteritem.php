<?php
namespace Model;

class CharacterItem {
  private $dbAttributes = array(
    ':item'         => -1,
    ':quality'      => -1,
    ':ilvl'         => 0,
    ':setList'      => null,
    ':transmogItem' => null,
    ':bonusList'    => null,
    ':enchant'      => null
  );
  private $additionalAttributes = array(
    'name'          => null,
    'icon'          => null,
    'slotNum'       => -1,
    'slot'          => null, //name
    'quality'       => null, //name
    'qualityColor'  => null
  );
  private $gems = array();
  private $relics = array();
  private $traits = array();

  public function __construct($json) {
    if(isset($json['_fromdb'])) {
      $this->fromDBJson($json);
    } else {
      $this->fromBnetJson($json);
    }

  }

  private function fromDBJson($json) {

  }

  private function fromBnetJson($json) {
    $this->dbAttributes[':item'] = $json['id'];
    $this->dbAttributes[':quality'] = $json['quality'];
    $this->dbAttributes[':ilvl'] = $json['itemLevel'];
    if(isset($json['tooltipParams']['set'])) {
      $this->dbAttributes[':setList'] = join(':', $json['tooltipParams']['set']);
    }
    if(isset($json['tooltipParams']['transmogItem'])) {
      $this->dbAttributes[':transmogItem'] = $json['tooltipParams']['transmogItem'];
    }
    if(isset($json['tooltipParams']['enchant'])) {
      $this->dbAttributes[':enchant'] = $json['tooltipParams']['enchant'];
    }
    if(isset($json['bonusLists'])) {
      $this->dbAttributes[':bonusList'] = join(':', $json['bonusLists']);
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

    $this->additionalAttributes['name'] = $json['name'];
    $this->additionalAttributes['icon'] = $json['icon'];
  }

  public function setSlot($slotNum, $slot) {
    $this->additionalAttributes['slotNum'] = $slotNum;
    $this->additionalAttributes['slot']    = $slot;
  }

  public function setQuality($quality, $qualityColor) {
    $this->additionalAttributes['quality'] = $quality;
    $this->additionalAttributes['qualityColor']    = $qualityColor;
  }

  public function getBaseAttributes() {
    return $this->dbAttributes;
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

  public function toAssocArray() {
    $result = array();
    foreach ($this->dbAttributes as $key => $value) {
      $newKey = substr($key, 1);
      $result[$newKey] = $value;
    }
    $result['id'] = $result['item'];
    unset($result['item']);
    $result = array_merge($result, $this->additionalAttributes);
    $result['gems'] = $this->gems;
    $result['relics'] = $this->relics;
    $result['traits'] = $this->traits;
    return $result;
  }
}
