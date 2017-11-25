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
		$str = strtolower ( $str );
		foreach ( $this->playerSlotArray as $playerSlot ) {
			if (strtolower ( $playerSlot->getMainName () ) == $str) {
				return $playerSlot;
			}
			if ($playerSlot->getAliases ()) {
				foreach ( $playerSlot->getAliases () as $alias ) {
					if (strtolower ( $alias ) == $str) {
						return $playerSlot;
					}
				}
			}
		}
		return NULL;
	}

	// TODO: this has not really been tested.
	// min edit distance impelemented with top-down dynamic programming
	public static function dist($a, $b, $i, $j, $mem = NULL) {
		// base case
		if (min($i, $j) == 0) {
			return max($i, $j);
		}

		// lazy-initialize memory
		if ($mem == NULL) {
			$mem = array();
		}
		if ($mem[$i] == NULL) {
			$mem[$i] == array();
		}

		// if already computed return that value
		if ($mem[$i][$j]) {
			return $mem[$i][$j] - 1;
		}

		// othewise compute it recursively
		$best = min(
				dist($a, $b, $i - 1, $j, $mem) + 1,
				dist($a, $b, $i, $j - 1, $mem) + 1,
				dist($a, $b, $i - 1, $j - 1, $mem)
				   + ($a[$i] == $b[$j] ? 0 : 1));
		// store the computed value
		$mem[$i][$j] = $best + 1;
		return $best;
	}

	/**
	 * Match the vote target string to one of the PlayerSlots in this matcher using
	 * clever heuristics.
	 */
	public function matchTarget($str) {
		$exact = $this->matchExact ( $str );
		if ($exact) {
			return $exact;
		}
		// use some heuristics here.
	}
}
