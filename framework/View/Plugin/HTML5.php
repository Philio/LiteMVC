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

class HTML5
{

	/**
	 * Get the HTML5 document header
	 *
	 * @return string
	 */
	public function header()
	{
		return '<!DOCTYPE HTML>';
	}

}