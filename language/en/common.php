<?php
/**
*
* @package phpBB Extension - MafiaScum REST API
* @copyright (c) 2017 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    "ERROR_NOT_FOUND" => "The requested resource was not found.",
    "VALIDATION_ERROR_REQUIRED" => "%s is a required field.",
));
