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
namespace LiteMVC;

class Auth extends Resource\Loadable
{

	/**
	 * App instance
	 *
	 * @var App
	 */
	protected $_app;

	/**
	 * Config data
	 *
	 * @var array
	 */
	protected $_config = array();

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
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 * @throws Auth\Exception
	 */
	public function __construct(App $app = null)
	{
		// Set app and config
		if ($app) {
			$this->_app = $app;
			$this->_config = $app->getResource(self::RES_CONFIG)->auth;
		}
	}

	/**
	 * Set app
	 *
	 * @param App $app
	 * @return Auth
	 */
	public function setApp(App $app)
	{
		$this->_app = $app;
		return $this;
	}

	/**
	 * Get app
	 *
	 * @return App
	 */
	public function getApp()
	{
		return $this->_app;
	}

	/**
	 * Set config
	 *
	 * @param Config $config
	 * @return Auth
	 */
	public function setConfig(Config $config)
	{
		$this->_config = $config;
		return $this;
	}

	/**
	 * Get config
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Init auth
	 *
	 * @return void
	 * @throws Auth\Exception
	 */
	public function init()
	{
		// Check config
		if (is_null($this->_config)) {
			throw new Auth\Exception('No authentication configuration has been specified.');
		}

		// Check for session setting
		if (!isset($this->_config[self::CONF_SESSION])) {
			$this->_config[self::CONF_SESSION] = true;
		}
		$this->_session = $this->_config[self::CONF_SESSION];

		// Check that a user model has been specified
		if (isset($this->_config[self::CONF_MODEL][self::CONF_USER])) {
			// Check session
			if ($this->_session && isset($_SESSION[self::SESS_NS][self::SESS_NS_USER])) {
				$this->_userModel = $_SESSION[self::SESS_NS][self::SESS_NS_USER];
			// Instantiate new class
			} elseif (class_exists($this->_config[self::CONF_MODEL][self::CONF_USER])) {
				$this->_userModel = new $this->_config[self::CONF_MODEL][self::CONF_USER]($this->_app->getResource(self::RES_DATABASE));
			// Invalid configuration
			} else {
				throw new Auth\Exception(
					'Unable to load user model, class ' . $this->_config[self::CONF_MODEL][self::CONF_USER] . ' not found.'
				);
			}
		}

		// If an ACL model has been specified instanciate it
		if (isset($this->_config[self::CONF_MODEL][self::CONF_ACL])) {
			$this->_aclModel = new $this->_config[self::CONF_MODEL][self::CONF_ACL]($this->_app->getResource(self::RES_DATABASE));
			// Setup ACL caching
			if (isset($this->_config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_MODULE],
					$this->_config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_LIFETIME])) {
				// Cache object
				$this->_aclModel->setCache(
					$this->_app->getResource($this->_config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_MODULE])
				);
				// Cache lifetime
				$this->_aclModel->setCacheLifetime(
					$this->_config[self::CONF_MODEL][self::CONF_CACHE][self::CONF_LIFETIME]
				);
			}
		}

		// Set allow policy
		if (isset($this->_config[self::CONF_ACL][self::CONF_POLICY])) {
			$this->_policy = $this->_config[self::CONF_ACL][self::CONF_POLICY];
		}

		// Add allowed pages
		if (isset($this->_config[self::CONF_ACL][self::CONF_ALLOW]) && is_array($this->_config[self::CONF_ACL][self::CONF_ALLOW])) {
			$this->_allow = $this->_config[self::CONF_ACL][self::CONF_ALLOW];
		}

		// Add denied pages
		if (isset($this->_config[self::CONF_ACL][self::CONF_DENY]) && is_array($this->_config[self::CONF_ACL][self::CONF_DENY])) {
			$this->_deny = $this->_config[self::CONF_ACL][self::CONF_DENY];
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