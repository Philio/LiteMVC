<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Theme
 * @version 0.1.0
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
