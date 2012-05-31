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

abstract class Validator
{
	
	/**
	 * Params
	 * 
	 * @var array 
	 */
	protected $_params;
	
	/**
	 * Last error
	 * 
	 * @var string 
	 */
	protected $_lastError;
	
	/**
	 * Human readable version of the last error
	 * 
	 * @var string 
	 */
	protected $_lastErrorDesc;
	
	/**
	 * Set params array
	 * 
	 * @param array $params 
	 */
	public function __construct(array $params = null)
	{
		$this->_params = $params;
	}
	
	/**
	 * Check if params are valid
	 * 
	 * @return bool 
	 */
	abstract public function validate();
	
	/**
	 * Return the last error message
	 * 
	 * @return string 
	 */
	public function getLastError()
	{
		return $this->_lastError;
	}
	
	/**
	 * Return the last error description
	 * 
	 * @return string 
	 */
	public function getLastErrorDesc()
	{
		return $this->_lastErrorDesc;
	}
	
}