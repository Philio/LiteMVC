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

class Captcha implements Validator {

	/**
	 * Validate value
	 *
	 * @return string
	 */
	public function validate($value, $params)
	{
		$captcha = new \LiteMVC\Captcha();
		if (!$captcha->checkCode($value)) {
			return 'The CAPTCHA is incorrect';
		}
		return null;
	}

}

namespace A\B;
class C{}

namespace A;
new B\C();