<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Form\Validator
 * @version 0.1.0
 */
namespace LiteMVC\Form\Validator;

interface Validator {

	/**
	 * Validate the value
	 *
	 * @param mixed $value
	 * @param mixed $params
	 */
	public function validate($value, $params);

}