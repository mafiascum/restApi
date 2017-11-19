<?php

namespace mafiascum\restApi\voting;

/**
 * Takes an array of PlayerSlot and returns a matching PlayerSlot for this raw target
 * or NULL if none of the targets match.
 */
class VoteNameMatcher {
	private $playerSlotArray;

	public function __construct($playerSlotArray) {
		$this->playerSlotArray = $playerSlotArray;
	}

	public function match($str) {
		foreach ( $playerSlotArray as $playerSlot ) {
			if (strtolower ( $playerSlot->getMainName () ) == $str) {
				return $playerSlot;
			}
			foreach ( $playerSlot->getAliases () as $alias ) {
				if (strtolower ( $alias ) == $str) {
					return $playerSlot;
				}
			}
		}
		return NULL;
	}
}
