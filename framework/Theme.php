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

class Theme
{

	/**
	 * The id of the current site/theme
	 *
	 * @var int
	 */
	protected $_siteId;

	/**
	 * Site model
	 *
	 * @var Model
	 */
	protected $_siteModel;

	/**
	 * File handlers
	 *
	 * @var array
	 */
	protected $_fileHandlers = array();

	/**
	 * Data handlers
	 *
	 * @var array
	 */
	protected $_dataHandlers = array();

	/**
	 * File path
	 *
	 * @var string
	 */
	protected $_filePath = App::PATH_CACHE;

	/**
	 * File prefix
	 *
	 * @var string
	 */
	const FILE_PREFIX = 'Theme';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Get config
		$config = $app->getResource('Config')->theme;
		// Instanciate site model
		if (isset($config['model']['site'])) {
			$this->_siteModel = new $config['model']['site']($app->getResource('Database'));
			// Enable caching
			if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
				$this->_siteModel->setCache($app->getResource($config['model']['cache']['module']));
				$this->_siteModel->setCacheLifetime($config['model']['cache']['lifetime']);
			}
			// Host lookup
			if ($this->_siteModel->lookup($_SERVER['HTTP_HOST'])) {
				$this->_siteId = $this->_siteModel->getSiteId();
			}
		}
		// Fallback to config
		if (is_null($this->_siteId)) {
			if (isset($config['default'])) {
				$this->_siteId = $config['default'];
			} else {
				throw new Theme\Exception('Unable to determine site id.');
			}
		}
		// Register file handlers
		if (is_array($config['model']['file'])) {
			foreach ($config['model']['file'] as $name => $model) {
				// Instanciate model
				$this->_fileHandlers[$name] = new $model($app->getResource('Database'));
				// Enable caching
				if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
					$this->_fileHandlers[$name]->setCache($app->getResource($config['model']['cache']['module']));
					$this->_fileHandlers[$name]->setCacheLifetime($config['model']['cache']['lifetime']);
				}
			}
		}
		// Register data handlers
		if (is_array($config['model']['data'])) {
			foreach ($config['model']['data'] as $name => $model) {
				// Instanciate model
				$this->_dataHandlers[$name] = new $model($app->getResource('Database'));
				// Enable caching
				if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
					$this->_dataHandlers[$name]->setCache($app->getResource($config['model']['cache']['module']));
					$this->_dataHandlers[$name]->setCacheLifetime($config['model']['cache']['lifetime']);
				}
			}
		}
	}

	/**
	 * Call function
	 *
	 * @param string $func
	 * @param array $params
	 * @return mixed
	 */
	public function __call($func, $params)
	{
		// Check file handlers
		if (isset($this->_fileHandlers[$func])) {
			return $this->_getFilename($this->_fileHandlers[$func], current($params));
		// Check data handlers
		} elseif (isset($this->_dataHandlers[$func])) {
			return $this->_getData($this->_dataHandlers[$func], current($params));
		// Otherwise just return the param
		} else {
			return current($params);
		}
	}

	/**
	 * Gets the temporary filename of the themed version of a file
	 *
	 * @param Theme\Item $model
	 * @param string $filename
	 * @return string
	 */
	protected function _getFilename($model, $filename)
	{
		if ($model instanceof Theme\Item && $model->lookup($this->_siteId, $filename)) {
			// Check if temporary file exists and is current
			$tmpFile = \PATH . rtrim($this->_filePath, '/') . '/' . self::FILE_PREFIX .
				'_' . $this->_siteId . '_' . md5($layout);
			if (file_exists($tmpFile) && filemtime($tmpFile) >= $model->getUpdateTime()) {
				return $tmpFile;
			}
			// Create or update temporary file
			$f = fopen($tmpFile, 'w');
			if ($f === false) {
				throw new Theme\Exception('Unable to open the output file.');
			}
			$res = fwrite($f, $model->getData());
			if ($res === false) {
				throw new File\Exception('Unable to write to the output file.');
			}
			fclose($f);
			return $tmpFile;
		}
		return $filename;
	}

	/**
	 * Get a data item
	 *
	 * @param Theme\Item $model
	 * @param string $key
	 * @return array
	 */
	protected function _getData($model, $key)
	{
		if ($model instanceof Theme\Item && $model->lookup($this->_siteId, $key)) {
			return $model->getData();
		}
		return null;
	}

}
?>
