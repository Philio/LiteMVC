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

use LiteMVC\OAuth2;

class Server
{

	/**
	 * Client implementation
	 *
	 * @var Client
	 */
	protected $_client;

	/**
	 * Code implementation
	 *
	 * @var Code
	 */
	protected $_code;

	/**
	 * Token implementation
	 *
	 * @var Token
	 */
	protected $_token;

	/**
	 * Set models
	 *
	 * @param Client $client
	 * @param Code $code
	 * @param Token $token
	 */
	public function __construct(Client $client, Code $code, Token $token)
	{
		$this->_client = $client;
		$this->_code = $code;
		$this->_token = $token;
	}

	/**
	 * Start client Authentication
	 *
	 * Validate the request, authenticate the client, return valid params.
	 * Resource owner authentication/approval should happen externally to this
	 * library
	 *
	 * @param type $clientId
	 * @param type $clientSecret
	 */
	public function startAuthentication()
	{
		// Filter GET input
		$params = filter_input_array(INPUT_GET, array(
			OAuth2::AUTH_RESPONSE_TYPE	=> array('filter' => FILTER_VALIDATE_REGEXP, 'options' => array('regexp' => '/^code$/')),
			OAuth2::AUTH_CLIENT_ID		=> array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_SCALAR),
			OAuth2::AUTH_CLIENT_SECRET	=> array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_SCALAR),
			OAuth2::AUTH_REDIRECT_URI	=> FILTER_VALIDATE_URL,
			OAuth2::AUTH_SCOPE			=> array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_SCALAR),
			OAuth2::AUTH_STATE			=> array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_SCALAR)
		));

		// If client id is null check other supported authentication methods
		if (is_null($params[OAuth2::AUTH_CLIENT_ID])) {
			// Check for basic auth
			if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
				$params[OAuth2::AUTH_CLIENT_ID] = $_SERVER['PHP_AUTH_USER'];
				$params[OAuth2::AUTH_CLIENT_SECRET] = $_SERVER['PHP_AUTH_PW'];
			}
		}

		// Start request validation
		$validator = new Validator\Authenticate($params);

		// Validity check (all params are set and look valid)
		if (!$validator->validate()) {
			if (isset($params[OAuth2::AUTH_REDIRECT_URI]) && !is_null($params[OAuth2::AUTH_REDIRECT_URI])) {
				$this->_errorRedirect($params[OAuth2::AUTH_REDIRECT_URI], $validator->getLastError(), $validator->getLastErrorDesc(), null, isset($params[OAuth2::AUTH_STATE]) ? $params[OAuth2::AUTH_STATE] : null);
			} else {
				$this->_errorJson(OAuth2::HTTP_BAD_REQUEST, $validator->getLastError(), $validator->getLastErrorDesc(), null, isset($params[OAuth2::AUTH_STATE]) ? $params[OAuth2::AUTH_STATE] : null);
			}
		}

		// Authenticate the client
		if (!$this->_client->authenticate($params[OAuth2::AUTH_CLIENT_ID], $params[OAuth2::AUTH_CLIENT_SECRET])) {
			if ($params[OAuth2::AUTH_REDIRECT_URI]) {
				$this->_errorRedirect($params[OAuth2::AUTH_REDIRECT_URI], OAuth2::ERROR_UNAUTHORIZED_CLIENT, null, null, $params[OAuth2::AUTH_STATE]);
			} else {
				$this->_errorJson(OAuth2::HTTP_UNAUTHORIZED, OAuth2::ERROR_UNAUTHORIZED_CLIENT, null, null, $params[OAuth2::AUTH_STATE]);
			}
		}

		// Get the redirect URI
		$redirectUri = $validator->checkRedirectUri($this->_client->getRedirectUri());
		if (is_null($redirectUri)) {
			// Only redirect to pre-defined redirect URI
			if ($this->_client->getRedirectUri()) {
				$this->_errorRedirect($this->_client->getRedirectUri(), $validator->getLastError(), $validator->getLastErrorDesc(), null, isset($params[OAuth2::AUTH_STATE]) ? $params[OAuth2::AUTH_STATE] : null);
			} else {
				$this->_errorJson(OAuth2::HTTP_BAD_REQUEST, $validator->getLastError(), $validator->getLastErrorDesc(), null, $params[OAuth2::AUTH_STATE]);
			}
		}
		$params[OAuth2::AUTH_REDIRECT_URI] = $redirectUri;

		print_r($params);
		return $params;
	}

	/**
	 * Finish client authentication
	 *
	 * Redirect back to client depending on authorisation state
	 *
	 * @param type $params
	 * @param type $userId
	 * @param type $authorised
	 */
	public function finishAuthentication($params, $authorised = false, $userId = null)
	{
		// @todo
	}

	/**
	 * Redirect to uri and append error info
	 *
	 * @param type $redirectUri
	 * @param type $error
	 * @param type $desc
	 * @param type $uri
	 * @param type $state
	 */
	protected function _errorRedirect($redirectUri, $error, $desc = null, $uri = null, $state = null)
	{
		// Send header
		header('HTTP/1.1 ' . OAuth2::HTTP_FOUND);

		// Reconstruct uri with additional params
		$error = $this->_parseError($error, $desc, $uri, $state);
		$redirectUri = $this->_injectQueryParams($redirectUri, $error);

		// Redirect
		header('Location: ' . $redirectUri);
		exit;
	}

	/**
	 * Display error information in JSON and exit
	 *
	 * @param string $httpCode
	 * @param string $error
	 * @param string $desc
	 * @param string $uri
	 * @param string $state
	 */
	protected function _errorJson($httpCode, $error, $desc = null, $uri = null, $state = null)
	{
		// Send header
		header('HTTP/1.1 ' . $httpCode);
		header("Content-Type: application/json");

		// Output error information
		echo json_encode($this->_parseError($error, $desc, $uri, $state));
		exit;
	}

	/**
	 * Parse error into an array
	 *
	 * @param string $error
	 * @param string $desc
	 * @param string $uri
	 * @param string $state
	 */
	protected function _parseError($error, $desc = null, $uri = null, $state = null)
	{
		$error = array(OAuth2::RES_ERROR => $error);
		if (!is_null($desc)) {
			$error[OAuth2::RES_ERROR_DESCRIPTION] = $desc;
		}
		if (!is_null($uri)) {
			$error[OAuth2::RES_ERROR_URI] = $uri;
		}
		if (!is_null($state)) {
			$error[OAuth2::AUTH_STATE] = $state;
		}
		return $error;
	}

	/**
	 * Inject params into query string of a URI
	 *
	 * @param string $uri
	 * @param array $params
	 * @return string
	 */
	protected function _injectQueryParams($uri, array $params)
	{
		// Get URI parts
		$parts = parse_url($uri);

		// Merge query params
		if (!isset($parts['query'])) {
			$parts['query'] = http_build_query($params);
		} else {
			$query = array();
			parse_str($parts['query'], $query);
			$parts['query'] = http_build_query(array_merge($query, $params));
		}

		// Rebuild URI
		$uri = isset($parts['scheme']) ? $parts['scheme'] . '://' : null;
		$uri .= isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : null ) . '@' : null;
		$uri .= isset($parts['host']) ? $parts['host'] : null;
		$uri .= isset($parts['port']) ? ':' . $parts['port'] : null;
		$uri .= isset($parts['path']) ? $parts['path'] : null;
		$uri .= isset($parts['query']) ? '?' . $parts['query'] : null;
		$uri .= isset($parts['fragment']) ? '#' . $parts['fragment'] : null;
		return $uri;
	}

}