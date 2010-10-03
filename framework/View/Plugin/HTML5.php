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