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
	 * Site model
	 *
	 * @var Model
	 */
	protected $_siteModel;

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
			if (isset($config['model']['cache']['module']) && isset($config['model']['cache']['lifetime'])) {
				$this->_siteModel->setCache($app->getResource($config['model']['cache']['module']));
				$this->_siteModel->setCacheLifetime($config['model']['cache']['lifetime']);
			}
			// Host lookup
			if ($this->_siteModel->lookup($_SERVER['HTTP_HOST'])) {
				
			}
		} else {
			throw new Theme\Exception('A model to load site data is required');
		}
	}

}
?>
