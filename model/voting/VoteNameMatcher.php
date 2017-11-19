<?php

namespace mafiascum\restApi\model\voting;

/**
 * Takes an array of PlayerSlot and returns a matching PlayerSlot for this raw target
 * or NULL if none of the targets match.
 */
class VoteNameMatcher {
	private $playerSlotArray;

	public function __construct($playerSlotArray) {
		$this->playerSlotArray = $playerSlotArray;
	}

	public function matchExact($str) {
		$str = strtolower($str);
		foreach ( $this->playerSlotArray as $playerSlot ) {
			if (strtolower ( $playerSlot->getMainName () ) == $str) {
				return $playerSlot;
			}
			if ($playerSlot->getAliases()) {
				foreach ( $playerSlot->getAliases () as $alias ) {
					if (strtolower ( $alias ) == $str) {
						return $playerSlot;
					}
				}
			}
		}
		return NULL;
	}
}
