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
		$app->init('../tests/configs/empty.ini');
		$this->assertTrue($app->isResource(\LiteMVC\App::RES_CONFIG));
	}

	public function testSimpleConfig()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/simple.ini');
		$this->assertTrue($app->isResource(\LiteMVC\App::RES_CONFIG));
		$this->assertTrue($app->isResource(\LiteMVC\App::RES_ERROR));
		$this->assertTrue($app->isResource(\LiteMVC\App::RES_SESSION));
		$this->assertTrue($app->isResource(\LiteMVC\App::RES_HTML));
	}

	public function testLoadResource()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/empty.ini');

		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();

		// Relect autoloader to get classmap list
		$reflection = new ReflectionObject($autoload);
		$classMap = $reflection->getProperty('_classMap');
		$classMap->setAccessible(true);

		// Check that all classes in the map appear valid
		foreach (array_keys($classMap->getValue($autoload)) as $class) {
			if ($class == 'LiteMVC\App') {
				continue;
			}
			$matches = array();
			preg_match('/^LiteMVC\\\([\w]+)$/', $class, $matches);
			if (count($matches)) {
				$reflection = new ReflectionClass($class);
				if (!$reflection->isAbstract()) {
					$this->assertTrue($app->loadResource($matches[1]));
				}
			}
		}

		// Check that an invalid class name returns false
		$this->assertFalse($app->loadResource('Invalid'));
	}

	public function testGetResource()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/empty.ini');

		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();

		// Relect autoloader to get classmap list
		$reflection = new ReflectionObject($autoload);
		$classMap = $reflection->getProperty('_classMap');
		$classMap->setAccessible(true);

		// Check that all classes in the map appear valid
		foreach (array_keys($classMap->getValue($autoload)) as $class) {
			if ($class == 'LiteMVC\App') {
				continue;
			}
			$matches = array();
			preg_match('/^LiteMVC\\\([\w]+)$/', $class, $matches);
			if (count($matches)) {
				$reflection = new ReflectionClass($class);
				if (!$reflection->isAbstract()) {
					$this->assertInstanceOf($class, $app->getResource($matches[1]));
				}
			}
		}

		// Check that and invalid class name returns null
		$this->assertNull($app->getResource('Invalid'));
	}

	public function testIsResource()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/empty.ini');

		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();

		// Relect autoloader to get classmap list
		$reflection = new ReflectionObject($autoload);
		$classMap = $reflection->getProperty('_classMap');
		$classMap->setAccessible(true);

		// Check that all classes in the map return true once loaded
		foreach (array_keys($classMap->getValue($autoload)) as $class) {
			if ($class == 'LiteMVC\App') {
				continue;
			}
			$matches = array();
			preg_match('/^LiteMVC\\\([\w]+)$/', $class, $matches);
			if (count($matches)) {
				$reflection = new ReflectionClass($class);
				if (!$reflection->isAbstract()) {
					$app->loadResource($matches[1]);
					$this->assertTrue($app->isResource($matches[1]));
				}
			}
		}

		// Check that an invalid/unset class returns false
		$this->assertFalse($app->isResource('Invalid'));
	}

	public function testUnloadResource()
	{
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/empty.ini');

		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();

		// Relect autoloader to get classmap list
		$reflection = new ReflectionObject($autoload);
		$classMap = $reflection->getProperty('_classMap');
		$classMap->setAccessible(true);

		// Check that all classes in the map return true once loaded
		foreach (array_keys($classMap->getValue($autoload)) as $class) {
			if ($class == 'LiteMVC\App') {
				continue;
			}
			$matches = array();
			preg_match('/^LiteMVC\\\([\w]+)$/', $class, $matches);
			if (count($matches)) {
				$reflection = new ReflectionClass($class);
				if (!$reflection->isAbstract()) {
					$app->loadResource($matches[1]);
					$this->assertTrue($app->unloadResource($matches[1]));
				}
			}
		}

		// Check that an invalid/unset class returns false
		$this->assertFalse($app->unloadResource('Invalid'));
	}

	public function testRun()
	{
		$this->expectOutputString('<p>test</p>' . PHP_EOL);
		$app = new \LiteMVC\App();
		$app->init('../tests/configs/test.ini');
		$app->run();
	}

}