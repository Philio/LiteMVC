<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 */
namespace LiteMVC;

abstract class Form {

	/**
	 * Instance of App
	 *
	 * @var object
	 */
	protected $_app;

	/**
	 * Form id
	 *
	 * @var string
	 */
	protected $_id;

	/**
	 * CSS class name
	 *
	 * @var string
	 */
	protected $_class;

	/**
	 * Form action
	 *
	 * @var string
	 */
	protected $_action;

	/**
	 * Form method
	 *
	 * @var string
	 */
	protected $_method;

	/**
	 * Form fields
	 *
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * Form errors
	 *
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * Show form errors
	 *
	 * @var bool
	 */
	protected $_displayErrors = true;

	/**
	 * Array of validator objects
	 *
	 * @var array
	 */
	protected $_validators = array();

	/**
	 * Form submission methods
	 *
	 * @var string
	 */
	const METHOD_GET	= 'GET';
	const METHOD_POST	= 'POST';

	/**
	 * Form input data types
	 *
	 * @var string
	 */
	const TYPE_TEXT		= 'text';
	const TYPE_PASSWORD	= 'password';
	const TYPE_RADIO	= 'radio';
	const TYPE_CHECKBOX	= 'checkbox';
	const TYPE_HIDDEN	= 'hidden';
	const TYPE_SELECT	= 'select';
	const TYPE_TEXTAREA	= 'textarea';
	const TYPE_SUBMIT	= 'submit';
	const TYPE_CAPTCHA  = 'captcha';

	/**
	 * Default namespace prefix
	 *
	 * @var stirng
	 */
	const NAMESPACE_PREFIX = 'LiteMVC\\Form\\Validator\\';

	/**
	 * Constructor
	 *
	 * @param App $app 
	 */
	public function __construct($app = null)
	{
		$this->_app = $app;
	}

	/**
	 * Set a value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->_fields[$key]['value'] = $value;
	}

	/**
	 * Get a value
	 *
	 * @return mixed;
	 */
	public function &__get($key)
	{
		if (isset($this->_fields[$key]['value'])) {
			return $this->_fields[$key]['value'];
		}
		return null;
	}

	/**
	 * Convert form object to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		// Form opening tag
		$html = '<form';
		if (!is_null($this->_id) && !empty($this->_id)) {
			$html .= ' id="' . $this->_id . '"';
		}
		if (!is_null($this->_class) && !empty($this->_class)) {
			$html .= ' class="' . $this->_class . '"';
		}
		if (!is_null($this->_action) && !empty($this->_action)) {
			$html .= ' action="' . $this->_action . '"';
		}
		if (!is_null($this->_method) && !empty($this->_method)) {
			$html .= ' method="' . $this->_method . '"';
		}
		$html .= '>' . PHP_EOL;
		// Form fields
		foreach ($this->_fields as $name => $data) {
			// Add label
			if (array_key_exists('label', $data)) {
				$html .= '<label for="' . $name . '">' . $data['label'] . '</label>' . PHP_EOL;
			}
			// Add form element
			switch ($data['type']) {
				// All standard input methods
				case self::TYPE_TEXT:
				case self::TYPE_PASSWORD:
				case self::TYPE_CHECKBOX:
				case self::TYPE_RADIO:
				case self::TYPE_HIDDEN:
				case self::TYPE_SUBMIT:
					// Build input element
					$html .= '<input id="' . $name . '" name="' . $name . '"';
					// Add properties
					foreach ($data as $key => $value) {
						if ($key == 'label' || $key == 'validate') {
							continue;
						}
						$html .= ' ' . $key . '="' . $value . '"';
					}
					// Closing tag
					$html .= ' />' . PHP_EOL;
					break;
				// Text area
				case self::TYPE_TEXTAREA:
					// Build text area
					$html .= '<textarea id="' . $name . '" name="' . $name . '"';
					// Add properties
					foreach ($data as $key => $value) {
						if ($key == 'label' || $key == 'validate' || $key == 'value') {
							continue;
						}
						$html .= ' ' . $key . '="' . $value . '"';
					}
					$html = '>';
					if (isset($data['value'])) {
						$html .= $data['value'];
					}
					$html .= '</textarea>' . PHP_EOL;
					break;
				// Select
				case self::TYPE_SELECT:
					break;
				// Captcha
				case self::TYPE_CAPTCHA:
					$html .= $this->_app->getResource('Captcha');
					break;
			}
			// Display errors
			if ($this->_displayErrors) {
				if (isset($this->_errors[$name])) {
					$html .= '<span class="error">';
					if (is_array($this->_errors[$name])) {
						$html .= implode(', ', $this->_errors[$name]);
					} else {
						$html .= $this->_errors[$name];
					}
				}
			}
		}
		// Form closing tag
		$html .= '</form>' . PHP_EOL;
		return $html;
	}

	/**
	 * Set options for a select
	 *
	 * @param string $field
	 * @param array $options 
	 */
	public function setOptions($field, $options)
	{
		if (isset($this->_fields[$field], $this->_fields[$field]['type']) &&
			$this->_fields[$field]['type'] == self::TYPE_SELECT) {
			$this->_fields[$field]['options'] = $options;
		}
	}

	/**
	 * Populate form values
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		foreach ($data as $key => $value) {
			if (isset($this->_fields[$key])) {
				$this->_fields[$key]['value'] = $value;
			}
		}
	}

	/**
	 * Validate data
	 *
	 * @return bool
	 */
	public function validate()
	{
		// Reset errors
		$this->_errors = array();
		// Check form values
		foreach ($this->_fields as $key => $data) {
			$value = $data['value'];
			// If no validation options continue
			if (!isset($data['validate'])) {
				continue;
			}
			// Check all validation options
			foreach ($data['validate'] as $option => $params) {
				// Load validator
				$class = self::NAMESPACE_PREFIX . ucfirst($option);
				if (class_exists($class)) {
					// Check if an instance exists
					if (!isset($this->_validators[$option])) {
						$this->_validators[$option] = new $class();
					}
					$error = $this->_validators[$option]->validate($value, $params);
				} else {
					throw new Form\Exception('Unknown validator ' . $option . '.');
				}
				// Store error
				if (!is_null($error)) {
					if (!isset($this->_errors[$key])) {
						$this->_errors[$key] = $error;
					} elseif (!is_array($this->_errors[$key])) {
						$this->_errors[$key] = array($this->_errors[$key]);
						$this->_errors[$key][] = $error;
					} else {
						$this->_errors[$key][] = $error;
					}
				}
			}
		}
		if (count($this->_errors)) {
			return false;
		}
		return true;
	}

	/**
	 * Get a list of form errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Should errors be displayed
	 *
	 * @param bool $display
	 */
	public function displayErrors($display)
	{
		$this->_displayErrors = $display;
	}

}