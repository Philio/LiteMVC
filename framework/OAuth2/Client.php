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
namespace LiteMVC\OAuth2;

interface Client
{

	/**
	 * Authenticate a client
	 *
	 * @param string $clientId
	 * @param string $clientSecret
	 * @return bool
	 */
	public function authenticate($clientId, $clientSecret = null);

	/**
	 * Get redirect uri for the client
	 *
	 * @return string
	 */
	public function getRedirectUri();

}