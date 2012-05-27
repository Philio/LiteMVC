<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2012
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Database
 * @version 0.1.0
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