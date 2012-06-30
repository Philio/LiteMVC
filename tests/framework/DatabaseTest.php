<?php
class DatabaseTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Instance of test config
	 *
	 * @var \LiteMVC\Config
	 */
	protected $_config;

	/**
	 * Setup
	 */
	public function setUp()
	{
		$this->_config = new \LiteMVC\Config\Ini();
		$this->_config->load(\PATH . '/tests/configs/test.ini', 'production');
	}

	/**
	 * Test app config
	 */
	public function testAppConfig()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/test.ini');
		$database = new \LiteMVC\Database($app);
		$config = $database->getConfig();
		$this->assertTrue(is_array($config));
		$this->assertArrayHasKey('test', $config);
		$this->assertArrayHasKey('setup', $config);
	}

	/**
	 * Test set config
	 */
	public function testSetConfig()
	{
		// Configure database
		$database = new \LiteMVC\Database();
		$this->assertInstanceOf('\LiteMVC\Database', $database->setConfig($this->_config));
		$config = $database->getConfig();
		$this->assertTrue(is_array($config));
		$this->assertArrayHasKey('test', $config);
	}

	/**
	 * Test set connection
	 */
	public function testSetConnection()
	{
		$mysql = $this->getMockBuilder('\LiteMVC\Database\MySQL')
				->disableOriginalConstructor()
				->getMock();
		$database = new \LiteMVC\Database();
		$database->setConnection('test', $mysql);
		$reflection = new ReflectionObject($database);
		$connections = $reflection->getProperty('_connections');
		$connections->setAccessible(true);
		$this->assertArrayHasKey('test', $connections->getValue($database));
	}

	/**
	 * Test get connection
	 */
	public function testGetConnection()
	{
		$mysql = $this->getMockBuilder('\LiteMVC\Database\MySQL')
				->disableOriginalConstructor()
				->getMock();
		$database = new \LiteMVC\Database();
		$database->setConfig($this->_config);
		$database->setConnection('test', $mysql);
		$this->assertInstanceOf('\LiteMVC\Database\MySQL', $database->getConnection('test'));
	}

	/**
	 * Test without config
	 */
	public function testNoConfig()
	{
		$this->setExpectedException('\LiteMVC\Database\Exception', 'No database configuration has been specified.');
		$database = new \LiteMVC\Database();
		$database->getConnection('test');
	}

	/**
	 * Test invalid config
	 */
	public function testInvalidConfig()
	{
		$this->setExpectedException('\LiteMVC\Database\Exception', 'Configuration for database \'invalid\' is invalid.');
		$database = new \LiteMVC\Database();
		$database->setConfig($this->_config);
		$database->getConnection('invalid');
	}

	/**
	 * Test non-existant config
	 */
	public function testNonExistantConfig()
	{
		$this->setExpectedException('\LiteMVC\Database\Exception', 'Database \'nonexistant\' is not defined in the configuration.');
		$database = new \LiteMVC\Database();
		$database->setConfig($this->_config);
		$database->getConnection('nonexistant');
	}

}
