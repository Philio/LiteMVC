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
namespace LiteMVC\Resource;

// Namespace aliases
use LiteMVC\Resource as Resource;

abstract class Loadable extends Resource
{

	/**
	 * Initialise the resource
	 *
	 * @return void
	 */
	abstract public function init();

}