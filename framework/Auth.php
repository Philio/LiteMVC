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

class Auth
{
	
	/**
	 * Store auth data in session
	 * 
	 * @var bool 
	 */
	protected $_session;

	/**
	 * User model
	 * 
	 * @var Auth\User
	 */
	protected $_userModel;

	/**
	 * Acl model
	 *
	 * @var Auth\Acl
	 */
	protected $_aclModel;

	/**
	 * Policy (allow or deny)
	 *
	 * @var string
	 */
	protected $_policy = self::POLICY_ALLOW;

	/**
	 * List of pages that are always allowed
	 *
	 * @var array
	 */
	protected $_allow = array();

	/**
	 * List of pages that are always denied
	 *
	 * @var array
	 */
	protected $_deny = array();

	/**
	 * Namespace in session
	 *
	 * @var string
	 */
	const SESS_NS		= 'Auth';
	const SESS_NS_USER	= 'User';

	/**
	 * Security policy types
	 *
	 * @var string
	 */
	const POLICY_ALLOW	= 'allow';
	const POLICY_DENY	= 'deny';

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_SESSION	= 'session';
	const CONF_MODEL	= 'model';
	const CONF_USER		= 'user';
	const CONF_ACL		= 'acl';
	const CONF_POLICY	= 'policy';
	const CONF_ALLOW	= 'allow';
	const CONF_DENY		= 'deny';
	const CONF_CACHE	= 'cache';
	const CONF_MODULE	= 'module';
	const CONF_LIFETIME	= 'lifetime';

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_CONFIG = 'Config';
	const RES_DATABASE = 'Database';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app = null)
	{
		// Check config
		$config = $app->getResource(self::RES_CONFIG)->auth;
		if (is_null($config)) {
			throw new Auth\Exception('No database configuration has been specified.');
		}
		
		// Check for session setting
		if (!isset($config[self::CONF_SESSION])) {
			$config[self::CONF_SESSION] = true;
		}
		$this->_session = $config[self::CONF_SESSION];

		// Check that a user model has been specified
		if (isset($config[self::CONF_MODEL][self::CONF_USER])) {
			// Check session
			if ($this->_session && isset($_SESSION[self::SESS_NS][self::SESS_NS_USER])) {
				$this->_userModel = $_SESSION[self::SESS_NS][self::SESS_NS_USER];
			// Instantiate new class
			} elseif (class_exists($config[self::CONF_MODEL][self::CONF_USER])) {
				$this->_userModel = new $config[self::CONF_MODEL][self::CONF_USER]($app->getResource(self::RES_DATABASE));
			// Invalid configuration
			} else {
				throw new Auth\Exception(
					'Unable to load user model, class ' . $config[self::CONF_MODEL][self::CONF_USER] . ' not found.'
				);
			}
		}

		// If an ACL model has been specified instanciate it
		if (isset($config[self::CONF_MODEL][self::CONF_ACL])) {
			$this->_aclModel = new $config[self::CONF_MODEL][self::CONF_ACL]($app->getResource(self::RES_DATABASE));
			// Setup ACL caching
			if (isset($config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_MODULE],
					$config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_LIFETIME])) {
				// Cache object
				$this->_aclModel->setCache(
					$app->getResource($config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_MODULE])
				);
				// Cache lifetime
				$this->_aclModel->setCacheLifetime(
					$config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_LIFETIME]
				);
			}
		}

		// Set allow policy
		if (isset($config[self::CONF_ACL][self::CONF_POLICY])) {
			$this->_policy = $config[self::CONF_ACL][self::CONF_POLICY];
		}

		// Add allowed pages
		if (isset($config[self::CONF_ACL][self::CONF_ALLOW]) && is_array($config[self::CONF_ACL][self::CONF_ALLOW])) {
			$this->_allow = $config[self::CONF_ACL][self::CONF_ALLOW];
		}

		// Add denied pages
		if (isset($config[self::CONF_ACL][self::CONF_DENY]) && is_array($config[self::CONF_ACL][self::CONF_DENY])) {
			$this->_deny = $config[self::CONF_ACL][self::CONF_DENY];
		}
	}

	/**
	 * Check if user control is enabled
	 *
	 * @return bool
	 */
	public function hasUserModel()
	{
		return $this->_userModel instanceof Auth\User;
	}

	/**
	 * Check if access control is enabled
	 *
	 * @return bool
	 */
	public function hasAclModel()
	{
		return $this->_aclModel instanceof Auth\Acl;
	}

	/**
	 * Login a user
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function login($username, $password)
	{
		if ($this->hasUserModel()) {
			// Call model login function
			if ($this->_userModel->login($username, $password)) {
				// Store login in session
				if ($this->_session) {
					$_SESSION[self::SESS_NS][self::SESS_NS_USER] = $this->_userModel;
				}
				// Login ok return true
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a user is logged in
	 * 
	 * @return bool
	 */
	public function isLoggedIn()
	{
		if ($this->hasUserModel()) {
			return !is_null($this->_userModel->getUserId());
		}
		return false;
	}

	/**
	 * Check if current user is allowed to view the page
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed($module, $controller, $action)
	{
		// Check logged in user privs
		if ($this->isLoggedIn() && $this->hasAclModel()) {
			return $this->_aclModel->isAllowed($this->_userModel->getUserId(), $module, $controller, $action);
		}

		// Check allowed pages
		if (in_array($controller . '.' . $action, $this->_allow) || in_array($controller . '.*', $this->_allow)) {
			return true;
		}

		// Check denied pages
		if (in_array($controller . '.' . $action, $this->_deny) || in_array($controller . '.*', $this->_deny)) {
			return false;
		}

		// Check default policy
		if ($this->_policy == self::POLICY_ALLOW) {
			return true;
		}

		// Return false if all previous checks fail
		return false;
	}

	/**
	 * Get the model for the current user
	 *
	 * @return object
	 */
	public function getUser()
	{
		// If logged in return the user model
		if ($this->isLoggedIn()) {
			return $this->_userModel;
		}
		return null;
	}

}