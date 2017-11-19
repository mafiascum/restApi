<?php

namespace mafiascum\restApi\model\voting;

class VoteHistory {
	private $voteChangeArray = array ();

	function __construct() {
	}

	public static function newVoteHistory($playerSlotArray, $dayStartPostNumber) {
		$voteHistory = new VoteHistory ();
		foreach ( $playerSlotArray as $playerSlot ) {
			$voteHistory->addVoteChange($dayStartPostNumber, $playerSlot, NULL);
		}
		return $voteHistory;
	}

	public function addVoteChange($postNumber, $voterPlayerSlot, $targetPlayerSlot) {
      $this->voteChangeArray[] = new VoteChange($postNumber, $voterPlayerSlot, $targetPlayerSlot);
	}
}

class VoteChange {
	private $postNumber;
	private $voterPlayerSlot;
	private $targetPlayerSlot;

	public function __construct($postNumber, $voterPlayerSlot, $targetPlayerSlot) {
      $this->postNumber = $postNumber;
      $this->voterPlayerSlot = $voterPlayerSlot;
      $this->$targetPlayerSlot = $targetPlayerSlot;
	}
}
