<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2012
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 * @version 0.1.0
 */
namespace LiteMVC;

class OAuth2
{
	
	/**
	 * App object
	 *
	 * @var App
	 */
	protected $_app;
	
	/**
	 * Config data
	 * 
	 * @var array
	 */
	protected $_config = array();

	/**
	 * HTTP response codes 
	 * 
	 * @var string
	 */
	const HTTP_FOUND		= '302 Found';
	const HTTP_BAD_REQUEST	= '400 Bad Request';
	const HTTP_UNAUTHORIZED	= '401 Unauthorized';
	const HTTP_FORBIDDEN	= '403 Forbidden';
	
	/**
	 * Authenticate params
	 * 
	 * @var string 
	 */
	const AUTH_RESPONSE_TYPE	= 'response_type';
	const AUTH_CLIENT_ID		= 'client_id';
	const AUTH_CLIENT_SECRET	= 'client_secret';
	const AUTH_REDIRECT_URI		= 'redirect_uri';
	const AUTH_SCOPE			= 'scope';
	const AUTH_STATE			= 'state';
	
	/**
	 * Error params
	 * 
	 * @var string 
	 */
	const ERROR_INVALID_REQUEST				= 'invalid_request';
	const ERROR_UNAUTHORIZED_CLIENT			= 'unauthorized_client';
	const ERROR_ACCESS_DENIED				= 'access_denied';
	const ERROR_UNSUPPORTED_RESPONSE_TYPE	= 'unsupported_response_type';
	const ERROR_INVALID_SCOPE				= 'invalid_scope';
	const ERROR_SERVER_ERROR				= 'server_error';
	const ERROR_TEMPORARILY_UNAVAILABLE		= 'temporarily_unavailable';
	
	/**
	 * Error response params
	 * 
	 * @var string 
	 */
	const RES_ERROR				= 'error';
	const RES_ERROR_DESCRIPTION	= 'error_description';
	const RES_ERROR_URI			= 'error_uri';
	
	/**
	 * Constructor
	 *
	 * @param App $app
	 */
	public function __construct(App $app)
	{
		// Reference App object for resource loading
		$this->_app = $app;
		
		// Check config
		$config = $app->getResource('Config')->oauth2;
		if (!is_null($config)) {
			$this->_config = $config;
		}
	}
	
	/**
	 * Get an OAuth2 instance based on config namespace
	 * 
	 * @param string $namespace 
	 */
	public function getFromConfig($namespace)
	{
		// Check that the config namespace exists
		if (!array_key_exists($namespace, $this->_config)) {
			throw new OAuth2\Exception('Unknown OAuth2 config');
		}
		
		// Check that a role is defined
		if (!isset($this->_config[$namespace]['role'])) {
			throw new OAuth2\Exception('OAuth2 role is undefined');
		}
		
		// Return new OAuth instance based on role
		switch ($this->_config[$namespace]['role']) {
			case 'client':
				return null;
			case 'server':
				// Check client is set in config
				if (!isset($this->_config[$namespace]['client'])) {
					throw new OAuth2\Exception('OAuth2 server requires a client');
				}
				
				// Check token is set in config
				if (!isset($this->_config[$namespace]['token'])) {
					throw new OAuth2\Exception('OAuth2 server requires a token');
				}
				
				// Instantiate client object
				if (isset($this->_config[$namespace]['client']['model'])) {
					$client = new $this->_config[$namespace]['client']['model']($this->_app->getResource('Database'));
				} elseif (isset($this->_config[$namespace]['client']['class'])) {
					$client = new $this->_config[$namespace]['client']['class']();
				} else {
					throw new OAuth2\Exception('OAuth2 server requires a client class or model to be defined');
				}
				
				// Instantiate token object
				if (isset($this->_config[$namespace]['token']['model'])) {
					$token = new $this->_config[$namespace]['token']['model']($this->_app->getResource('Database'));
				} elseif (isset($this->_config[$namespace]['token']['class'])) {
					$token = new $this->_config[$namespace]['token']['class']();
				} else {
					throw new OAuth2\Exception('OAuth2 server requires a token class or model to be defined');
				}
				
				// Return new server isntance
				return new OAuth2\Server($client, $token);
		}
	}
	
}