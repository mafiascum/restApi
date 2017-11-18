<?php

namespace mafiascum\restApi\voting;

/**
 * Represents a parsed raw vote target (or unvote).
 */
class RawVoteTarget {
	private $targetOrNullIfUnvote;
	private $debugBbCode;
	public function __construct($targetOrNullIfUnvote, $debugBbCode) {
		$this->targetOrNullIfUnvote = $targetOrNullIfUnvote;
		$this->debugBbCode = $debugBbCode;		
	}
	
	/**
	 * Returns the target vote string, or NULL if this is an unvote.
	 */
	public function getTargetOrNullIfUnvote() {
		return $this->targetOrNullIfUnvote;
	}
	
	/**
	 * Returns the original bbcode from which this vote was parsed form
	 */
	public function getDebugBbCode() {
		return $this->debugBbCode;
	}
	
	public function __toString() {
		if ($this->targetOrNullIfUnvote != NULL) {
			return "VOTE: " . $this->targetOrNullIfUnvote;
		} else {
			return "UNVOTE";
		}
	}

	/**
	 * Takes an array of PlayerSlot and returns a matching PlayerSlot for this raw target 
	 * or NULL if none of the targets match.
	 */
	// TODO(mathblade): make this smarter
	public function matchPlayer($playerSlotArray) {
		if ($targetOrNullIfUnvote == NULL) {
			throw new Exception ( "Trying to match a player on an unvote" );
		}
		foreach ( $playerSlotArray as $playerSlot ) {
			if (strtolower ( $playerSlot->getMainName () ) == $targetOrNullIfUnvote) {
				return $playerSlot;
			}
			foreach ($playerSlot->getAliases() as $alias) 
			{
				if (strtolower ( $alias ) == $targetOrNullIfUnvote) {
					return $playerSlot;
				}
			}
		}
		return NULL;
	}
}
