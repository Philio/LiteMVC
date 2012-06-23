<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.1
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