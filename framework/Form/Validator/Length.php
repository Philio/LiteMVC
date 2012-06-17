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
namespace LiteMVC\Form\Validator;

class Length implements Validator {

	/**
	 * Validate value
	 *
	 * @return string
	 */
	public function validate($value, $params)
	{
		// If param is a number assume exact length required
		if (is_numeric($params) && strlen($value) != $params) {
			return 'Value should be ' . $params . ' characters long';
		}
		// If 2 params assume they are min and max values
		if (is_array($params) && count($params) == 2) {
			if (strlen($value) < $params[0]) {
				return 'Value should be at least ' . $params[0] . ' characters long';
			}
			if (strlen($value) > $params[1]) {
				return 'Value must be less than ' . $params[1] . ' characters long';
			}
		}
		return null;
	}

}