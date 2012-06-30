<?php
class AutoloadTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Test register
	 */
	public function testRegister()
	{
		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();
		$autoload->register();

		// Strict check
		$inArray = false;
		foreach (spl_autoload_functions() as $func) {
			if ($func === array($autoload, 'loader')) {
				$inArray = true;
			}
		}
		$this->assertTrue($inArray);
	}

	/**
	 * Test unregister
	 */
	public function testUnregister()
	{
		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();
		$autoload->register();

		// Unregister autoloader
		$autoload->unregister();

		// Strict check
		$inArray = false;
		foreach (spl_autoload_functions() as $func) {
			if ($func === array($autoload, 'loader')) {
				$inArray = true;
			}
		}
		$this->assertFalse($inArray);
	}

	/**
	 * Test that all classes in the class map load
	 */
	public function testClassMapLoader()
	{
		// Instantiate new autoloader
		$autoload = new \LiteMVC\Autoload();

		// Relect autoloader to get classmap list
		$reflection = new ReflectionObject($autoload);
		$classMap = $reflection->getProperty('_classMap');
		$classMap->setAccessible(true);

		// Check that all classes in the map appear valid
		foreach (array_keys($classMap->getValue($autoload)) as $class) {
			$this->assertTrue(class_exists($class) || interface_exists($class));
		}
	}

}