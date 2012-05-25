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
namespace LiteMVC\Auth;

interface Acl
{
	
	/**
	 * Check if user has permission to view a page
	 * 
	 * @param int $userId
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return array
	 */
	public function isAllowed($userId, $module, $controller, $action);

}