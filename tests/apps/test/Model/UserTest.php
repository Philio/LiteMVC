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
	 * Test login method
	 *
	 * @large
	 */
	public function testLogin()
	{
		$model = $this->getModel();
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
		$id = $model->id;

		$model = $this->getModel();
		$this->assertTrue($model->login('test', 'test'));
	}

	/**
	 * Test getUserId method
	 *
	 * @large
	 */
	public function testGetUserId()
	{
		$model = $this->getModel();
		$model->username = 'test';
		$model->password = sha1('test');
		$this->assertTrue($model->save());
		$id = $model->id;

		$model = $this->getModel();
		$model->login('test', 'test');
		$this->assertEquals($model->getUserId(), $id);
	}

}