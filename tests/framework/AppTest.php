<?php
class AppTest extends PHPUnit_Framework_TestCase
{

	public function testInstantiateApp()
	{
		$app = new \LiteMVC\App();
		$this->assertTrue($app instanceof \LiteMVC\App);
	}

	public function testEmptyConfig()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/empty.ini');
		$this->assertTrue($app->getResource(\LiteMVC\App::RES_CONFIG) instanceof \LiteMVC\Config);
	}

	public function testSimpleConfig()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/simple.ini');
		$this->assertTrue($app->getResource(\LiteMVC\App::RES_CONFIG) instanceof \LiteMVC\Config);
	}

}