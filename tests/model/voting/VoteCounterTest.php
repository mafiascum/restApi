<?php

namespace mafiascum\restApi\model\voting;

use PHPUnit\Framework\TestCase;
use mafiascum\restApi\model\voting\GameConfigPostParser;
use mafiascum\restApi\model\voting\PlayerSlot;
use mafiascum\restApi\model\voting\VoteChange;
use mafiascum\restApi\model\voting\VoteCount;

require_once(dirname(__FILE__) . "/../../../model/voting/GameConfigPostParser.php");
require_once(dirname(__FILE__) . "/../../../model/voting/PlayerSlot.php");
require_once(dirname(__FILE__) . "/../../../model/voting/VoteChange.php");
require_once(dirname(__FILE__) . "/../../../model/voting/VoteCount.php");

class VoteCounterTest extends TestCase {

	public function testSimpleVoteCounter() {
		$a = new PlayerSlot ( 'a', NULL );
		$b = new PlayerSlot ( 'b', NULL );
		$c = new PlayerSlot ( 'c', NULL );
		$d = new PlayerSlot ( 'd', NULL );
		$e = new PlayerSlot ( 'e', NULL );
		$f = new PlayerSlot ( 'f', NULL );

		$voteChangeArray = array (
				new VoteChange ( 1, $a, $b ),
				new VoteChange ( 2, $b, $b ),
				new VoteChange ( 2, $b, NULL ),
				new VoteChange ( 3, $c, $b ),
				new VoteChange ( 4, $c, $d ),
				new VoteChange ( 5, $c, $d ),
				new VoteChange ( 6, $d, NULL ),
				new VoteChange ( 7, $f,  $d),
		);

		$expected = '';
        $expected .= "d (2): [post=4]c[/post] [post=7]f[/post]\n";
        $expected .= "b (1): [post=1]a[/post]\n";
        $expected .= "Not Voting (3): [post=2]b[/post] [post=0]d[/post] [post=0]e[/post]\n";
		$gameConfig = GameConfigPostParser::parseFromString(
			"[gameconfig]a,b,c,d,e,f[/gameconfig]"
		);

		$voteCount = VoteCount::generateWagons ($gameConfig, $voteChangeArray);
		$this->assertEquals ($expected, $voteCount->toBbcode());
	}
}
