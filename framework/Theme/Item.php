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
	 * Lookup filename
	 * 
	 * @param int $siteId
	 * @param string $filename
	 * @return bool
	 */
	public function lookup($siteId, $filename);

	/**
	 * Get file data
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
