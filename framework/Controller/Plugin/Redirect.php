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
namespace LiteMVC\Controller\Plugin;

class Redirect
{

	/**
	 * Redirect to a URL
	 *
	 * @param int $url
	 */
	public function process($url)
	{
		header('Location: ' . $url);
		exit;
	}

}