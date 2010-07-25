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
	 * Page layout model
	 *
	 * @var Model
	 */
	protected $_layoutModel;

	/**
	 * Page content model
	 *
	 * @var Model
	 */
	protected $_pageModel;

	/**
	 * CSS styles model
	 *
	 * @var Model
	 */
	protected $_styleModel;

	/**
	 * File path
	 *
	 * @var string
	 */
	protected $_filePath = App::Path_Cache;

	/**
	 * File prefix
	 *
	 * @var string
	 */
	const File_Prefix = 'Theme';

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
		// Layout model
		if (isset($config['model']['layout'])) {
			$this->_layoutModel = new $config['model']['layout']($app->getResource('Database'));
			// Enable caching
			if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
				$this->_layoutModel->setCache($app->getResource($config['model']['cache']['module']));
				$this->_layoutModel->setCacheLifetime($config['model']['cache']['lifetime']);
			}
		}
		// Page model
		if (isset($config['model']['page'])) {
			$this->_pageModel = new $config['model']['page']($app->getResource('Database'));
			// Enable caching
			if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
				$this->_pageModel->setCache($app->getResource($config['model']['cache']['module']));
				$this->_pageModel->setCacheLifetime($config['model']['cache']['lifetime']);
			}
		}
		// Style model
		if (isset($config['model']['style'])) {
			$this->_styleModel = new $config['model']['style']($app->getResource('Database'));
			// Enable caching
			if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
				$this->_styleModel->setCache($app->getResource($config['model']['cache']['module']));
				$this->_styleModel->setCacheLifetime($config['model']['cache']['lifetime']);
			}
		}
	}

	/**
	 * Get filename of the layout template
	 *
	 * @param string $layout
	 * @return string
	 */
	public function getLayout($layout)
	{
		return $this->_getFilename($this->_layoutModel, $layout);
	}

	/**
	 * Get filename of the page template
	 *
	 * @param string $page
	 * @return string
	 */
	public function getPage($page)
	{
		return $this->_getFilename($this->_pageModel, $page);
	}

	/**
	 * Get filename of the stylesheet
	 *
	 * @param string $style
	 * @return string
	 */
	public function getStyle($style)
	{
		return $this->_getFilename($this->_styleModel, $style);
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
			$tmpFile = \PATH . rtrim($this->_filePath, '/') . '/' . self::File_Prefix .
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

}
?>
