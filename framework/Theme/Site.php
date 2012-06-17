<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.0
 */
namespace LiteMVC\Theme;

interface Site {

	/**
	 * Lookup hostname
	 *
	 * @param string $host
	 * @return bool
	 */
	public function lookup($host);

	/**
	 * Get site id
	 *
	 * @return int
	 */
	public function getSiteId();

}
?>
