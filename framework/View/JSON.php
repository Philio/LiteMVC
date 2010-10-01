<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\View
 * @version 0.1.0
 */
namespace LiteMVC\View;

use LiteMVC\View as View;

class JSON extends View implements View\View
{

	/**
	 * HTTP header codes
	 * 
	 * @var int
	 */
	const HEADER_OK				= 200;
	const HEADER_ACCEPTED		= 202;
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
	 * Set response HTTP header
	 *
	 * @param int $code
	 */
	public function setHeader($code)
	{
		if (array_key_exists($code, $this->_headers)) {
			header($code . ' ' . $this->_headers[$code]);
		}
	}

	/**
	 * Render as JSON format
	 *
	 * @return string
	 */
	public function render()
	{
		// Encode page data as JSON
		$this->_rendered = json_encode($this->_data);
	}

}