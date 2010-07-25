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
