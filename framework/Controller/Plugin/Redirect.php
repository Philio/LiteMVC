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