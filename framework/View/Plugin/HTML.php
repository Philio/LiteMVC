<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 * @version 0.1.0
 */
namespace LiteMVC\View\Plugin;

class HTML
{

	/**
	 * HTML Strict
	 *
	 * @var string
	 */
	const Header_Strict = 'strict';

	/**
	 * HTML Transitional
	 *
	 * @var string
	 */
	const Header_Transitional = 'transitional';

	/**
	 * HTML Frameset
	 *
	 * @var string
	 */
	const Header_Frameset = 'frameset';

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
			case self::Header_Strict:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . \PHP_EOL;
				break;
			case self::Header_Transitional:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . \PHP_EOL;
				break;
			case self::Header_Frameset:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">' . \PHP_EOL;
				break;
		}
	}

}