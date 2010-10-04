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

class Required implements Validator {

	/**
	 * Validate value
	 *
	 * @return string
	 */
	public function validate($value, $params)
	{
		if ($params === true) {
			if (is_null($value) || empty($value)) {
				return 'A value is required';
			}
		}
		return null;
	}

}