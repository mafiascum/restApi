<?php

namespace mafiascum\restApi\model\voting;

require_once('PlayerSlot.php');
require_once('VoteHistory.php');

/**
 * Game configuration to hold information about players and their roles.
 */
class GameConfig {
	/*
	 * An array of PlayerSlots representing the voting and votable slots in this game.
	 */
	private /*array of PlayerSlot*/ $playerSlotsArray;

	/*
	 * The post number where the current day starts
	 */
    private $currentDayStartPostNumber;

	public function __construct(
			$playerSlotsArray, $currentDayStartPostNumber) {
		$this->playerSlotsArray = $playerSlotsArray;
		$this->currentDayStartPostNumber = $currentDayStartPostNumber;
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
		return new GameConfig($playerSlots, 0);
	}

	public function getPlayerSlotsArray() {
		return $this->playerSlotsArray;
	}

	public function getCurrentDayStartPostNumber() {
		return $this->currentDayStartPostNumber;
	}

	public function newVoteHistory() {
		return new VoteHistory($this->playerSlotsArray);
	}
}
