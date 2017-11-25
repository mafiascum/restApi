<?php

namespace mafiascum\restApi\model\voting;

use PHPUnit\Framework\TestCase;
use mafiascum\restApi\model\voting\VoteConfigPostParser;
use mafiascum\restApi\model\voting\PlayerSlot;
use mafiascum\restApi\model\voting\VoteChange;
use mafiascum\restApi\model\voting\VoteCount;

require_once (dirname ( __FILE__ ) . "/../../../model/voting/VoteConfigPostParser.php");
require_once (dirname ( __FILE__ ) . "/../../../model/voting/PlayerSlot.php");
require_once (dirname ( __FILE__ ) . "/../../../model/voting/VoteChange.php");
require_once (dirname ( __FILE__ ) . "/../../../model/voting/VoteCount.php");
class VoteCounterTest extends TestCase {
	public function testSimpleVoteCounter() {
		$a = new PlayerSlot ( 'a', "aa" );
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
				new VoteChange ( 7, $f, $d )
		);
		$config = <<<XML
		[voteconfig]
			<config version="1.0">
				<slot name="a">
					<alias name="a_replaced"/>
					<alias name="a_replaced_alt"/>
				</slot>
				<slot name="b">
					<alias name="b_alt"/>
				</slot>
				<slot name="c"/>
				<slot name="d"/>
				<slot name="e"/>
				<slot name="f"/>
				<color>#FF0000</color>
				<daystart>1</daystart>
				<announcement>Deadline is in three days</announcement>
				<announcement>Player A is on V/LA until saturday</announcement>
			</config>
		[/voteconfig]
XML;
		$expected = '';
		$expected .= "[color=#FF0000]\n";
		$expected .= "[area=\"Auto-Generated Vote Count\"]\n";
		$expected .= "d (2): [post=4]c[/post] [post=7]f[/post]\n";
		$expected .= "b (1): [post=1]a[/post]\n";
		$expected .= "Not Voting (3): [post=2]b[/post] [post=1]d[/post] [post=1]e[/post]\n";
		$expected .= "[/area]";
		$expected .= "[/color]\n";
		$voteConfig = VoteConfigPostParser::parseFromString ($config);
		$voteCount = VoteCount::generateWagons ( $voteConfig, $voteChangeArray );
		$this->assertEquals ( $expected, $voteCount->toBbcode ($voteConfig) );
	}
}
