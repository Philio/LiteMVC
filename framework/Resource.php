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
namespace LiteMVC;

abstract class Resource
{

	/**
	 * Current version of the framework
	 *
	 * @var string
	 */
	const VERSION = '0.2.1';

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_AUTH		= 'Auth';
	const RES_LOADER	= 'Autoload';
	const RES_FILE		= 'Cache\File';
	const RES_MEMCACHE	= 'Cache\Memcache';
	const RES_CONFIG	= 'Config';
	const RES_CONF_INI	= 'Config\Ini';
	const RES_DATABASE	= 'Database';
	const RES_DISPATCH	= 'Dispatcher';
	const RES_ERROR		= 'Error';
	const RES_REQUEST	= 'Request';
	const RES_SESSION	= 'Session';
	const RES_HTML		= 'View\HTML';
	const RES_JSON		= 'View\JSON';

}