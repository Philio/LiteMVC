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
namespace LiteMVC;

class Email {

	/**
	 * Recipient addresses
	 *
	 * @var array
	 */
	protected $_to = array();

	/**
	 * Carbon copy addresses
	 *
	 * @var array
	 */
	protected $_cc = array();

	/**
	 * Blind carbon copy addresses
	 *
	 * @var array
	 */
	protected $_bcc = array();

	/**
	 * Sender address
	 *
	 * @var string
	 */
	protected $_from;

	/**
	 * Message subject
	 *
	 * @var string
	 */
	protected $_subject;

	/**
	 * Message template
	 *
	 * @var string
	 */
	protected $_template;

	/**
	 * Message data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Address type constants
	 *
	 * @var string
	 */
	const ADDR_TO	= 'to';
	const ADDR_CC	= 'cc';
	const ADDR_BCC	= 'bcc';

	/**
	 * Email new line constant
	 */
	const NL = "\r\n";

	/**
	 * Set a value
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function __set($name, $value) {
		$this->_data[$name] = $value;
	}

	/**
	 * Get a value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		return null;
	}

	/**
	 * Set a value
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function set($name, $value) {
		$this->_data[$name] = $value;
	}

	/**
	 * Get a value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &get($name)
	{
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		return null;
	}

	/**
	 * Add a recipient address
	 *
	 * @param mixed $address
	 * @param string $type
	 * @return void
	 */
	public function setAddress($address, $type = self::ADDR_TO)
	{
		if (!is_array($address)) {
			$address = array($address);
		}
		switch ($type) {
			case self::ADDR_TO:
				$this->_to = array_merge($this->_to, $address);
				break;
			case self::ADDR_CC:
				$this->_cc = array_merge($this->_cc, $address);
				break;
			case self::ADDR_BCC:
				$this->_bcc = array_merge($this->_bcc, $address);
				break;
		}
	}

	/**
	 * Set the sender address
	 *
	 * @param string $sender
	 * @return void
	 */
	public function setSender($sender)
	{
		$this->_from = $sender;
	}

	/**
	 * Set the message subject
	 *
	 * @param string $subject
	 * @return void
	 */
	public function setSubject($subject)
	{
		$this->_subject = $subject;
	}

	/**
	 * Set the message template
	 *
	 * @param <type> $template
	 * @return void
	 */
	public function setTemplate($template)
	{
		$this->_template = $template;
	}

	/**
	 * Send message
	 *
	 * @return void
	 */
	public function send()
	{
		// Generate body
		$body = $this->_template;
		foreach ($this->_data as $key => $value) {
			$body = str_ireplace('{' . $key . '}', $value, $body);
		}
		// Compile headers
		$headers = array();
		if ($this->_from) {
			$headers[] = 'From: ' . $this->_from;
		}
		if ($this->_cc) {
			foreach ($this->_cc as $cc) {
				$headers[] = 'Cc: ' . $cc;
			}
		}
		if ($this->_bcc) {
			foreach ($this->_bcc as $bcc) {
				$headers[] = 'Bcc: ' . $bcc;
			}
		}
		// Send message
		mail(implode(', ', $this->_to), $this->_subject, $body, implode(self::NL, $headers));
	}

}