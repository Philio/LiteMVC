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
namespace LiteMVC;

class Email extends Resource\Dataset implements \Countable
{

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