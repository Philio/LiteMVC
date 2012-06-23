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
namespace LiteMVC\View;

class JSON extends \LiteMVC\View
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
