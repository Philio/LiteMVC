<?php
abstract class ModelTestCase extends PHPUnit_Framework_TestCase
{

	/**
	 * The application config file to load
	 *
	 * @var string
	 */
	protected $_config;

	/**
	 * The schema file to load to the database to test
	 *
	 * @var string
	 */
	protected $_schema;

	/**
	 * The connection to use to load the schema
	 *
	 * @var type
	 */
	protected $_connection;

	/**
	 * The model class to test
	 *
	 * @var string
	 */
	protected $_modelClass;

	/**
	 * Instance of the application
	 *
	 * @var \LiteMVC\App
	 */
	protected $_app;

	/**
	 * Instance of the database
	 *
	 * @var \LiteMVC\Database
	 */
	protected $_db;

	/**
	 * Setup application, create test schema in the database
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->_app = new \LiteMVC\App();
		$this->_app->init($this->_config);
		$this->_db = $this->_app->getResource(\LiteMVC\App::RES_DATABASE);
		if ($this->_schema) {
			if ($this->_connection) {
				$conn = $this->_db->getConnection($this->_connection);
			} else {
				$model = new $this->_modelClass($this->_db);
				$conn = $model->getConnection();
			}
			$conn->multiQuery(file_get_contents(\PATH . '/schema/' . $this->_schema));
		}
	}

	/**
	 * Get an instance of the model
	 *
	 * @return \LiteMVC\Model
	 */
	protected function getModel()
	{
		return new $this->_modelClass($this->_db);
	}

}