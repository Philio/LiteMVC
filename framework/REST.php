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

class REST
{
	
	/**
	 * Base URL of the REST server
	 *
	 * @var string
	 */
	protected $_baseUrl;
	
	/**
	 * Parser to handle responses
	 * 
	 * @var REST\Parser
	 */
	protected $_parser;
	
	/**
	 * Default cURL options
	 * 
	 * @var array
	 */
	protected $_options = array(
		CURLOPT_RETURNTRANSFER => true
	);
	
	/**
	 * HTTP errors that are likely to be returned by a REST service
	 * 
	 * @var array
	 */
	protected $_errors = array(
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		503 => 'Service Unavailable'
	);
	
	/**
	 * Set REST server URL and response parser
	 * 
	 * @param string $url
	 */
	public function __construct($baseUrl, REST\Parser $parser = null)
	{
		$this->_baseUrl = $baseUrl;
		$this->_parser = $parser;
	}
	
	/**
	 * Perform a GET request
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function get($path)
	{
		return $this->_request($this->_baseUrl . $path);
	}
	
	/**
	 * Perform a POST request
	 * 
	 * @param string $path
	 * @param string | array $data
	 * @return mixed
	 */
	public function post($path, $data = null)
	{
		$options = array(CURLOPT_POST => true);
		if ($data) {
			$options[CURLOPT_POSTFIELDS] = $data;
		}
		return $this->_request($this->_baseUrl . $path, $options);
	}
	
	/**
	 * Perform a PUT request
	 * 
	 * @param string $path
	 * @param string | array $data
	 * @return mixed
	 */
	public function put($path, $data = null)
	{
		$options = array(CURLOPT_PUT => true);
		if ($data) {
			$options[CURLOPT_POSTFIELDS] = $data;
		}
		return $this->_request($this->_baseUrl . $path, $options);
	}
	
	/**
	 * Perform a DELETE request
	 * 
	 * @param string $path
	 * @return mixed
	 */
	public function delete($path)
	{
		return $this->_request($this->_baseUrl . $path, array(CURLOPT_CUSTOMREQUEST => 'DELETE'));
	}
	
	/**
	 * Send request
	 * 
	 * @param string $url
	 * @param array $options 
	 */
	protected function _request($url, $options = array())
	{
		// Merge options
		$options += $this->_options;
		
		// Send cURL request
		$curl = curl_init($url);
		curl_setopt_array($curl, $options);
		$res = curl_exec($curl);
		$meta = curl_getinfo($curl);
		curl_close($curl);
    	
    	// Check for errors
    	$this->_checkResult($meta);
		
		// Return response
		if ($this->_parser) {
			return $this->_parser->parse($res);
		}
		return $res;
	}
	
	/**
	 * Check result for errors
	 * 
	 * @param array $meta
	 * @throws Exception 
	 */
	protected function _checkResult($meta)
	{
		if (array_key_exists($meta['http_code'], $this->_errors)) {
			throw new REST\Exception($this->_errors[$meta['http_code']], $meta['http_code']);
		}
	}
	
}