<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\View
 * @version 0.1.0
 */
namespace LiteMVC\View;

use LiteMVC\View as View;

class JSON extends View implements View\View
{

	/**
	 * Render as JSON format
	 *
	 * @return string
	 */
	public function render()
	{
		// Set JSON header
		header("Content-Type: application/json");
		
		// Encode page data as JSON
		$this->_rendered = json_encode($this->_data);
	}

}