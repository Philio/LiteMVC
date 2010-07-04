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
	 * Policy (allow or deny)
	 *
	 * @var string
	 */
	protected $_policy = 'allow';

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
		// Check config
		$config = $app->getResource('Config')->Authenticate;
		if (!is_null($config)) {
			// Check that a user model has been specified
			if (isset($config['model']['user'])) {
				if (isset($_SESSION[self::Sess_Namespace]['User'])) {
					$this->_userModel = $_SESSION[self::Sess_Namespace]['User'];
				} else {
					$this->_userModel = new $config['model']['user']($app->getResource('Database'));
				}
			}
			// If an ACL model has been specified instanciate it
			if (isset($config['model']['acl'])) {
				$this->_aclModel = new $config['model']['acl']($app->getResource('Database'));
			}
			// Set allow policy
			if (isset($config['policy'])) {
				$this->_policy = $config['policy'];
			}
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

	/**
	 * Check if user control is enabled
	 *
	 * @return bool
	 */
	public function hasUserModel()
	{
		return $this->_userModel instanceof Authenticate\User;
	}

	/**
	 * Check if access control is enabled
	 *
	 * @return bool
	 */
	public function hasAclModel()
	{
		return $this->_aclModel instanceof Authenticate\Acl;
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
				$_SESSION[self::Sess_Namespace]['User'] = $this->_userModel;
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
		if ($this->isLoggedIn() && $this->hasAclModel()) {
			return $this->_aclModel->isAllowed(
				$$this->_userModel->getUserId(),
				$module,
				$controller,
				$action
			);
		} elseif (in_array($controller . '.' . $action, $this->_allow) ||
			in_array($controller . '.*', $this->_allow)) {
			return true;
		} elseif (in_array($controller . '.' . $action, $this->_deny) ||
			in_array($controller . '.*', $this->_deny)) {
			return false;
		} elseif ($this->_policy == 'allow') {
			return true;
		}
		return false;
	}

}