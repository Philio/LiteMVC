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

class Authenticate
{

	/**
	 * Database connection for model(s)
	 *
	 * @var Database 
	 */
	protected $_db;

	/**
	 * Model configuration
	 *
	 * @var array
	 */
	protected $_modelConfig = array();

	/**
	 * User model
	 * 
	 * @var Authenticate\User
	 */
	protected $_userModel;

	/**
	 * Acl model
	 *
	 * @var Authenticate\Acl
	 */
	protected $_aclModel;

	/**
	 * List of pages that are always allowed
	 *
	 * @var array
	 */
	protected $_allow = array();

	/**
	 * List of pages that are always denied
	 *
	 * @var <type>
	 */
	protected $_deny = array();

	/**
	 * Namespace in session
	 *
	 * @var string
	 */
	const Sess_Namespace = 'Auth';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Database object needed by models
		$this->_db = $app->getResource('Database');
		// Check config
		$config = $app->getResource('Config')->Authenticate;
		if (!is_null($config)) {
			// Check that a user model has been specified
			if (!isset($config['models']['user'])) {
				throw new Authenticate\Exception('No user model has been specified.');
			}
			$this->_modelConfig = $config['models'];
			// Add allowed pages
			if (isset($config['allow']) && is_array($config['allow'])) {
				$this->_allow = $config['allow'];
			}
			// Add denied pages
			if (isset($config['deny']) && is_array($config['deny'])) {
				$this->_deny = $config['deny'];
			}
		} else {
			throw new Authenticate\Exception('No database configuration has been specified.');
		}
	}

}