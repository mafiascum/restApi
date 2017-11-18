<?php

use PHPUnit\Framework\TestCase;
use mafiascum\restApi\voting\RawVoteParser;
use mafiascum\restApi\voting\RawVoteTarget;

class RawVoteParserTest extends TestCase {
	public function testParseAllRawVoteTargets() {
		$post = "[b] Not a vote [/b]"; 
		$post .= "HI! [b] Vote: ToTo [/b] die scum"; 
		$post .= "[b] Unvote [/b]";
		$post .= "[b] VOTE: Kison [/b]";
		$post .= "LOL Toto is obtown. [uv] Toto [/uv]";
		$post .= "[unvote] Toto [/unvote]";	
		$post .= "[quote] [vote]ignored_nested_vote[/vote][/quote]";
		$post .= "In case you can't see: [size=100000][vote] Toto [/vote][/size]";

		$actual = RawVoteParser::parseAllRawVoteTargetsFromPost($post);
		$this->assertEquals(array(
			new RawVoteTarget("toto", "[b] Vote: ToTo [/b]"),
		    new RawVoteTarget(NULL, "[b] Unvote [/b]"),
			new RawVoteTarget("kison", "[b] VOTE: Kison [/b]"),
		    new RawVoteTarget(NULL, "[uv] Toto [/uv]"),
			new RawVoteTarget(NULL, "[unvote] Toto [/unvote]"),
			new RawVoteTarget("toto", "[vote] Toto [/vote]"),
		),
		    $actual
		);
	}
}
