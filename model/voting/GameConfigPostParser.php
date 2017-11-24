<?php

namespace mafiascum\restApi\model\voting;

require_once dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'vendor/jbbcode/jbbcode/JBBCode/Parser.php';

require_once ('GameConfig.php');

/**
 * Game configuration to hold information about players and their roles.
 */
class GameConfigPostParser {
	private /*array of PlayerSlot*/ $playerSlotsArray;
	public function __construct($playerSlotsArray) {
		$this->playerSlotsArray = $playerSlotsArray;
	}

	/**
	 * Parses the game config from a BBCode encoded post.
	 */
	public static function parseFromString($bb_code_str) {
		$parser = new \JBBCode\Parser ();

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'gameconfig', '{param}' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		// Ignore votes inside the following tags: (quote, spoilers)
		$builder = new \JBBCode\CodeDefinitionBuilder ( 'quote', '' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'spoiler', '' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$parser->parse ( $bb_code_str );
		$visitor = new GameConfigNodeVisitor ();
		$parser->accept ( $visitor );
		return $visitor->getGameConfig ();
	}
	public function getPlayerSlotsArray() {
		return $this->playerSlotsArray;
	}
	public function newVoteHistory() {
		return new VoteHistory ( $this->playerSlotsArray );
	}
}

class GameConfigNodeVisitor implements \JBBCode\NodeVisitor {
	private $gameConfig;
	public function getGameConfig() {
		return $this->gameConfig;
	}
	public function visitDocumentElement(\JBBCode\DocumentElement $documentElement) {
		foreach ( $documentElement->getChildren () as $child ) {
			$child->accept ( $this );
		}
	}
	public function visitTextNode(\JBBCode\TextNode $textNode) {
		// DO NOTHING
	}
	public function visitElementNode(\JBBCode\ElementNode $elementNode) {
		if ('gameconfig' == strtolower ( $elementNode->getTagName () )) {
			$content = trim ( $elementNode->getAsHTML () );
			$this->gameConfig = GameConfig::parseFromString ($content);
		}
	}
}

