<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.1
 */
namespace LiteMVC\View\Plugin;

class HTML
{

	/**
	 * HTML Strict
	 *
	 * @var string
	 */
	const HEADER_STRICT = 'strict';

	/**
	 * HTML Transitional
	 *
	 * @var string
	 */
	const HEADER_TRANSITIONAL = 'transitional';

	/**
	 * HTML Frameset
	 *
	 * @var string
	 */
	const HEADER_FRAMESET = 'frameset';

	/**
	 * Get a HTML header
	 *
	 * @param string $type
	 * @return string
	 */
	public function header($type)
	{
		switch ($type) {
			default:
			case self::HEADER_STRICT:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . \PHP_EOL;
				break;
			case self::HEADER_TRANSITIONAL:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . \PHP_EOL;
				break;
			case self::HEADER_FRAMESET:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">' . \PHP_EOL;
				break;
		}
	}

}