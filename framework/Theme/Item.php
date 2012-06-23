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
namespace LiteMVC\Theme;

interface Item {

	/**
	 * Lookup key
	 *
	 * @param int $siteId
	 * @param string $key
	 * @return bool
	 */
	public function lookup($siteId, $key);

	/**
	 * Get data
	 *
	 * @return string
	 */
	public function getData();

	/**
	 * Get update time
	 *
	 * @return int
	 */
	public function getUpdateTime();

}
?>
