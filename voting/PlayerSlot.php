<?php

namespace mafiascum\restApi\voting;

/**
 * Represents a player slot for the purposes of voting.
 *
 * A player is represented by a main slot game, and alises
 * (aliases are useful to handle replacements, and hydrae)
 */
class PlayerSlot {
	private $mainName;
	private $aliases;
	public static $NO_LYNCH;
	public function __construct($mainName, $aliases) {
		$this->mainName = $mainName;
		$this->aliases = $aliases;
	}
	public function getAliases() {
		return $aliases;
	}
	public function getMainName() {
		return $mainName;
	}
}

PlayerSlot::$NO_LYNCH = new PlayerSlot ( "No Lynch", array () );

