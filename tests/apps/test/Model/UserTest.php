<?php
class UserTest extends ModelTestCase
{

	/**
	 * The application config file to load
	 *
	 * @var string
	 */
	protected $_config = 'test.ini';

	/**
	 * The schema file to load to the database to test
	 *
	 * @var string
	 */
	protected $_schema = 'test.sql';

	/**
	 * The connection to use to load the schema
	 *
	 * @var type
	 */
	protected $_connection = 'setup';

	/**
	 * The model class to test
	 *
	 * @var string
	 */
	protected $_modelClass = '\Test\Model\User';

	/**
	 * Test inserting a record into the database
	 *
	 * @large
	 */
	public function testInsert()
	{
		$model = $this->getModel();
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
	}

	/**
	 * Test inserting a record into the database
	 *
	 * @large
	 */
	public function testInsertTransaction()
	{
		$model = $this->getModel();
		$this->assertTrue($model->autocommit(false));
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
		$this->assertTrue($model->commit());
	}

	/**
	 * Test inserting a record into the database
	 *
	 * @large
	 */
	public function testInsertTransactionRollback()
	{
		$model = $this->getModel();
		$this->assertTrue($model->autocommit(false));
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
		$this->assertTrue($model->rollback());
	}

	/**
	 * Test inserting several records into the database
	 *
	 * @large
	 */
	public function testInsertMany()
	{
		for ($i = 0; $i < 100; $i ++) {
			$model = $this->getModel();
			$model->username = microtime(true);
			$model->password = sha1(microtime(true));
			$this->assertTrue($model->save());
		}
	}

	/**
	 * Test selecting first row inserted
	 *
	 * @large
	 */
	public function testSelect()
	{
		// Insert a row first
		$model = $this->getModel();
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
		$id = $model->id;

		// Load the inserted row
		$model = $this->getModel();
		$model->load($id);
		$this->assertEquals($model->id, $id);
		$this->assertEquals($model->username, 'test');
		$this->assertEquals($model->password, sha1('test'));
		$data = $model->getData();
		$this->assertEquals($data['id'], $id);
		$this->assertEquals($data['username'], 'test');
		$this->assertEquals($data['password'], sha1('test'));
	}

	/**
	 * Test selecting multiple rows inserted
	 *
	 * @large
	 */
	public function testSelectMany()
	{
		// Insert a rows first
		for ($i = 0; $i < 100; $i ++) {
			$model = $this->getModel();
			$username = mt_rand(0, 1) ? 'foo' : 'bar';
			$model->username = $username;
			$model->password = sha1($username);
			$this->assertTrue($model->save());
		}

		// Load the inserted row
		$model = $this->getModel();
		$foos = $model->find('username = "foo"');
		foreach ($foos as $foo) {
			$this->assertEquals($foo->username, 'foo');
			$this->assertEquals($foo->password, sha1('foo'));
		}
		$bars = $model->find('username = "bar"');
		foreach ($bars as $bar) {
			$this->assertEquals($bar->username, 'bar');
			$this->assertEquals($bar->password, sha1('bar'));
		}
		$this->assertEquals(count($foos) + count($bars), 100);
	}

	/**
	 * Test caching
	 *
	 * @large
	 */
	public function testSelectCache()
	{
		// Insert a row first
		$model = $this->getModel();
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
		$id = $model->id;

		$model = $this->getModel();
		$cache = $this->_app->getResource(\LiteMVC\App::RES_FILE);
		$model->setCache($cache);
		$model->setCacheLifetime(60);

		// Should save cache
		$model->load($id);
		$this->assertEquals($model->username, 'test');
		$this->assertEquals($model->password, sha1('test'));

		// Check cache exists
		$data = $cache->get('Model:Test\Model\User:1');
		$this->assertEquals($data[0]['id'], $id);
		$this->assertEquals($data[0]['username'], 'test');
		$this->assertEquals($data[0]['password'], sha1('test'));

		// Clear cache and verify that it's been deleted
		$this->assertTrue($model->clearCache($id));
		$this->assertFalse($cache->get('Model:Test\Model\User:1'));
	}

	/**
	 * Test connection handling
	 *
	 * @large
	 */
	public function testConnection()
	{
		$model = $this->getModel();
		$reflection = new ReflectionObject($model);
		$dbname = $reflection->getProperty('_database');
		$dbname->setAccessible(true);
		$namespace = $dbname->getValue($model);
		$reflection = new ReflectionObject($this->_db);
		$config = $reflection->getProperty('_config');
		$config->setAccessible(true);
		$dbConfig = $config->getValue($this->_db);
		$this->assertInstanceOf('\LiteMVC\Database\\' . $dbConfig[$namespace]['driver'], $model->getConnection());
	}

}