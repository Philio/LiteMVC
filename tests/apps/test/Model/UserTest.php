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
	 * @return void
	 */
	public function testInsert()
	{
		$model = $this->getModel();
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
	}

	/**
	 * Test inserting several records into the database
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
		$this->assertEquals($model->username, 'test');
		$this->assertEquals($model->password, sha1('test'));
	}

	/**
	 * Test selecting multiple rows inserted
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

}