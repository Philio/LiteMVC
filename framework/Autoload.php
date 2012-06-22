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
namespace LiteMVC;

class Autoload
{

	/**
	 * Class map for fast loading of framework classes
	 *
	 * @var array
	 */
	private $_classMap = array(
		'LiteMVC\App' => '/App.php',
		'LiteMVC\App\Exception' => '/App/Exception.php',
		'LiteMVC\Auth' => '/Auth.php',
		'LiteMVC\Auth\Acl' => '/Auth/Acl.php',
		'LiteMVC\Auth\Exception' => '/Auth/Exception.php',
		'LiteMVC\Auth\User' => '/Auth/User.php',
		'LiteMVC\Cache\File' => '/Cache/File.php',
		'LiteMVC\Cache\File\Exception' => '/Cache/File/Exception.php',
		'LiteMVC\Cache\Memcache' => '/Cache/Memcache.php',
		'LiteMVC\Cache\Memcache\Exception' => '/Cache/Memcache/Exception.php',
		'LiteMVC\Captcha' => '/Captcha.php',
		'LiteMVC\Config' => '/Config.php',
		'LiteMVC\Config\Exception' => '/Config/Exception.php',
		'LiteMVC\Config\Ini' => '/Config/Ini.php',
		'LiteMVC\Controller' => '/Controller.php',
		'LiteMVC\Controller\Plugin\Header' => '/Controller/Plugin/Header.php',
		'LiteMVC\Controller\Plugin\Redirect' => '/Controller/Plugin/Redirect.php',
		'LiteMVC\Database' => '/Database.php',
		'LiteMVC\Database\Exception' => '/Database/Exception.php',
		'LiteMVC\Database\MySQL' => '/Database/MySQL.php',
		'LiteMVC\Dispatcher' => '/Dispatcher.php',
		'LiteMVC\Email' => '/Email.php',
		'LiteMVC\Error' => '/Error.php',
		'LiteMVC\Form' => '/Form.php',
		'LiteMVC\Form\Exception' => '/Form/Exception.php',
		'LiteMVC\Form\Validator\Captcha' => '/Form/Validator/Captcha.php',
		'LiteMVC\Form\Validator\Length' => '/Form/Validator/Length.php',
		'LiteMVC\Form\Validator\Required' => '/Form/Validator/Required.php',
		'LiteMVC\Form\Validator\Validator' => '/Form/Validator/Validator.php',
		'LiteMVC\Model' => '/Model.php',
		'LiteMVC\Model\Exception' => '/Model/Exception.php',
		'LiteMVC\OAuth2' => '/OAuth2.php',
		'LiteMVC\OAuth2\Client' => '/OAuth2/Client.php',
		'LiteMVC\OAuth2\Code' => '/OAuth2/Code.php',
		'LiteMVC\OAuth2\Exception' => '/OAuth2/Exception.php',
		'LiteMVC\OAuth2\Server' => '/OAuth2/Server.php',
		'LiteMVC\OAuth2\Token' => '/OAuth2/Token.php',
		'LiteMVC\OAuth2\Validator' => '/OAuth2/Validator.php',
		'LiteMVC\OAuth2\Validator\Authenticate' => '/OAuth2/Validator/Authenticate.php',
		'LiteMVC\REST' => '/REST.php',
		'LiteMVC\REST\Exception' => '/REST/Exception.php',
		'LiteMVC\REST\Parser' => '/REST/Parser.php',
		'LiteMVC\Request' => '/Request.php',
		'LiteMVC\Resource' => '/Resource.php',
		'LiteMVC\Resource\Dataset' => '/Resource/Dataset.php',
		'LiteMVC\Resource\Loadable' => '/Resource/Loadable.php',
		'LiteMVC\Session' => '/Session.php',
		'LiteMVC\Session\Database' => '/Session/Database.php',
		'LiteMVC\Session\Database\Database' => '/Session/Database/Database.php',
		'LiteMVC\Session\Exception' => '/Session/Exception.php',
		'LiteMVC\Session\File' => '/Session/File.php',
		'LiteMVC\Session\Memcache' => '/Session/Memcache.php',
		'LiteMVC\Session\Session' => '/Session/Session.php',
		'LiteMVC\Session\Store' => '/Session/Store.php',
		'LiteMVC\Theme' => '/Theme.php',
		'LiteMVC\Theme\Exception' => '/Theme/Exception.php',
		'LiteMVC\Theme\Item' => '/Theme/Item.php',
		'LiteMVC\Theme\Site' => '/Theme/Site.php',
		'LiteMVC\View' => '/View.php',
		'LiteMVC\View\Exception' => '/View/Exception.php',
		'LiteMVC\View\HTML' => '/View/HTML.php',
		'LiteMVC\View\JSON' => '/View/JSON.php',
		'LiteMVC\View\Plugin\HTML' => '/View/Plugin/HTML.php',
		'LiteMVC\View\Plugin\HTML5' => '/View/Plugin/HTML5.php',
	);

	/**
	 * List of autoload namespace/paths
	 *
	 * @var array
	 */
	private $_paths = array();

	/**
	 * Set the path to the framework namespace
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->_paths[__NAMESPACE__] = realpath(__DIR__);
	}

	/**
	 * Autoload a class
	 *
	 * @param string $class
	 * @return void
	 */
	public function loader($class)
	{
		// Check classmap
		if (isset($this->_classMap[$class])) {
			require_once $this->_paths[__NAMESPACE__] . $this->_classMap[$class];
			return;
		}

		// Check that a path has been set
		if (!count($this->_paths)) {
			return;
		}

		// Check paths against class name
		foreach ($this->_paths as $ns => $path) {
			if (stripos($class, $ns) === 0) {
				$file = preg_replace('/' . $ns . '/i', $path, $class, 1);
				$file = str_replace('\\', '/', $file) . '.php';
				if (file_exists($file)) {
					require_once $file;
				}
				break;
			}
		}
	}

	/**
	 * Register autoloader
	 *
	 * @return void
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loader'));
	}

	/**
	 * Unregister autoloader
	 *
	 * @return void
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loader'));
	}

	/**
	 * Set an autoload path
	 *
	 * @param string $namespace
	 * @param string $path
	 */
	public function setPath($namespace, $path)
	{
		$this->_paths[$namespace] = $path;
	}

}
