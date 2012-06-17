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

interface Validator {

	/**
	 * Validate the value
	 *
	 * @param mixed $value
	 * @param mixed $params
	 */
	public function validate($value, $params);

}