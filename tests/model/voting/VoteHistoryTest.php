<?php
namespace mafiascum\restApi\model\voting;

use PHPUnit\Framework\TestCase;
use mafiascum\restApi\model\voting\PlayerSlot;
use mafiascum\restApi\model\voting\VoteChange;
use mafiascum\restApi\model\voting\VoteHistory;

class VoteHistoryTest extends TestCase {
	public function testSimpleVoteHistory() {
		$toto = new PlayerSlot('Toto', NULL);
		$kison = new PlayerSlot('Kison', NULL);
		$voteHistory = new VoteHistory(array($toto, $kison));
		$voteHistory->maybeAddFromPost(1, "Kison", "[v]toto[/v]");
		$voteHistory->maybeAddFromPost(2, "Toto", "[b] Not a vote [/b]");
		$voteHistory->maybeAddFromPost(3, "Toto", "[vote]Toto[/vote] I'm scum");
		$voteHistory->maybeAddFromPost(4, "Toto", "[b]Unvote[/b] Just kiddin'");
		$this->assertEquals(array(
				new VoteChange(1, $kison, $toto),
				new VoteChange(3, $toto, $toto),
				new VoteChange(4, $toto, NULL),
		), $voteHistory->getHistory());
	}
}
