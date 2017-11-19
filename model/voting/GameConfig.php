<?php

namespace mafiascum\restApi\model\voting;

require_once('PlayerSlot.php');
require_once('VoteHistory.php');

/**
 * Game configuration to hold information about players and their roles.
 */
class GameConfig {
	private /*array of PlayerSlot*/ $playerSlotsArray;
	public function __construct($playerSlotsArray) {
		$this->playerSlotsArray = $playerSlotsArray;
	}

	/**
	 * Parses the game configuration from a comma (,) separated list of player slots.
	 *
	 *  Each player slot is represented as the mainName string, with optionally more alias usernames
	 *  separated by '|'
	 *
	 *  e.g: 'Toto|SomePlayerReplacedByToto,Kison|KisonAlt,SomePlayer'
	 */
	public static function parseFromString($slotsStr) {
		$slotsArrayStr = explode(',', $slotsStr );
		$playerSlots = array ();
		foreach ( $slotsArrayStr as $str ) {
			$playersInThisSlot = explode ( '|', $str );
			$playerSlots[] = new PlayerSlot (
					$playersInThisSlot [0], array_slice($playersInThisSlot, 1) );
		}
		return new GameConfig($playerSlots);
	}

	public function newVoteHistory() {
		return new VoteHistory($this->playerSlotsArray);
	}
}
