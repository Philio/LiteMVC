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
namespace LiteMVC\Controller\Plugin;

class Header
{

	/**
	 * HTTP header prefix
	 *
	 * @var string
	 */
	const HEADER_PREFIX = 'HTTP/1.1';

	/**
	 * HTTP header codes
	 *
	 * @var int
	 */
	const HEADER_OK				= 200;
	const HEADER_ACCEPTED		= 202;
	const HEADER_NO_CONTENT		= 204;
	const HEADER_MOVED_PERM		= 301;
	const HEADER_FOUND			= 302;
	const HEADER_NOT_MODIFIED	= 304;
	const HEADER_MOVED_TEMP		= 307;
	const HEADER_BAD_REQUEST	= 400;
	const HEADER_UNAUTHORIZED	= 401;
	const HEADER_FORBIDDEN		= 403;
	const HEADER_NOT_FOUND		= 404;
	const HEADER_NOT_ALLOWED	= 405;
	const HEADER_NOT_ACCEPTABLE	= 406;
	const HEADER_SERVER_ERROR	= 500;
	const HEADER_UNAVAILABLE	= 503;

	/**
	 * HTTP header strings
	 *
	 * @var array
	 */
	private $_headers = array(
		self::HEADER_OK				=> 'OK',
		self::HEADER_ACCEPTED		=> 'Accepted',
		self::HEADER_NO_CONTENT		=> 'No Content',
		self::HEADER_MOVED_PERM		=> 'Moved Permanently',
		self::HEADER_FOUND			=> 'Found',
		self::HEADER_NOT_MODIFIED	=> 'Not Modified',
		self::HEADER_MOVED_TEMP		=> 'Temporary Redirect',
		self::HEADER_BAD_REQUEST	=> 'Bad Request',
		self::HEADER_UNAUTHORIZED	=> 'Unauthorized',
		self::HEADER_FORBIDDEN		=> 'Forbidden',
		self::HEADER_NOT_FOUND		=> 'Not Found',
		self::HEADER_NOT_ALLOWED	=> 'Method Not Allowed',
		self::HEADER_NOT_ACCEPTABLE	=> 'Not Acceptable',
		self::HEADER_SERVER_ERROR	=> 'Internal Server Error',
		self::HEADER_UNAVAILABLE	=> 'Service Unavailable'
	);

	/**
	 * Set HTTP header
	 *
	 * @param int $code
	 */
	public function process($code)
	{
		if (array_key_exists($code, $this->_headers)) {
			header(self::HEADER_PREFIX . ' ' . $code . ' ' . $this->_headers[$code]);
		}
	}

}