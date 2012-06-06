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
namespace LiteMVC\OAuth2\Validator;

use LiteMVC\OAuth2;
use LiteMVC\OAuth2\Validator;
use LiteMVC\OAuth2\Param;

class Authenticate extends Validator
{

	/**
	 * Validity check on params
	 * 
	 * @param array $params 
	 */
	public function validate()
	{
		// Clear any previous error message
		$this->_lastError = null;
		
		// Check for NULL
		if (is_null($this->_params)) {
			$this->_lastError = OAuth2::ERROR_INVALID_REQUEST;
			$this->_lastErrorDesc = 'no input parameters were found';
			return false;
		}

		// The response_type is required
		if (is_null($this->_params[OAuth2::AUTH_RESPONSE_TYPE]) || empty($this->_params[OAuth2::AUTH_RESPONSE_TYPE])) {
			$this->_lastError = OAuth2::ERROR_UNSUPPORTED_RESPONSE_TYPE;
			$this->_lastErrorDesc = 'the response_type parameter is required and should be set to code';
			return false;
		}
		
		// The client_id is required
		if (is_null($this->_params[OAuth2::AUTH_CLIENT_ID]) || empty($this->_params[OAuth2::AUTH_CLIENT_ID])) {
			$this->_lastError = OAuth2::ERROR_INVALID_REQUEST;
			$this->_lastErrorDesc = 'the client_id parameter is required';
			return false;
		}
		return true;
	}
	
	/**
	 * Check the client redirect URI and return the correct one to use
	 * 
	 * @param string $redirectUri 
	 */
	public function checkRedirectUri($redirectUri)
	{
		// No URI check
		if (!$redirectUri && !$this->_params[OAuth2::AUTH_REDIRECT_URI]) {
			$this->_lastError = OAuth2::ERROR_INVALID_REQUEST;
			$this->_lastErrorDesc = 'the redirect_uri is required when not predefined';
			return null;
		}
		
		// If client has pre-defined redirect URI check that it matches
		if ($redirectUri && $this->_params[OAuth2::AUTH_REDIRECT_URI] && strcasecmp($redirectUri, $this->_params[OAuth2::AUTH_REDIRECT_URI]) !== 0) {
			$this->_lastError = OAuth2::ERROR_INVALID_REQUEST;
			$this->_lastErrorDesc = 'the redirect_uri provided does not matched the stored redirect_uri';
			return null;
		}
		
		// Return a valid redirect URI
		if ($redirectUri) {
			return $redirectUri;
		} else {
			return $this->_params[OAuth2::AUTH_REDIRECT_URI];
		}
	}
	
	/**
	 * Get hostname from a URI
	 * 
	 * @param string $uri 
	 * @return string
	 */
	private function _getHost($uri)
	{
		$parts = parse_url($uri);
		return $parts['host'];
	}
	
}