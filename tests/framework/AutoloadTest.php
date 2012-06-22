<?php
class AutoloadTest extends PHPUnit_Framework_TestCase
{

	public function testLoader()
	{
		$autoload = new \LiteMVC\Autoload();
		$reflection = new ReflectionObject($autoload);
		$classMap = $reflection->getProperty('_classMap');
		$classMap->setAccessible(true);

		foreach (array_keys($classMap->getValue($autoload)) as $class) {
			$this->assertTrue(class_exists($class) || interface_exists($class));
		}
	}

}