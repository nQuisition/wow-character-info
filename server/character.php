<?php
include_once __DIR__.'/model/character.php';
use \Model\Character;

$char = Character::fetchCharacter('Quelthariel', 'Draenor', 'EU');
$char->vardump();
