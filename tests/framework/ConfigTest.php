<?php
class ConfigTest extends PHPUnit_Framework_TestCase
{

	public function testIni()
	{
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/tests/configs/test.ini', 'test');
		$this->assertEquals($config['autoload']['test'], '/apps/test');
		$this->assertEquals($config['init']['load'], array('Request'));
		$this->assertEquals($config['init']['test']['test']['load'], array('View\HTML'));
		$this->assertEquals($config['request']['default']['module'], 'test');
		$this->assertEquals($config['request']['test']['default']['controller'], 'test');
		$this->assertEquals($config['request']['test']['default']['action'], 'index');
		$this->assertEquals($config['request']['test']['test']['layout'], 'test');
		$this->assertNull($config['invalid']);
	}

	public function testIniExtend()
	{
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/tests/configs/test.ini', 'extend');
		$this->assertEquals($config['autoload']['test'], '/apps/test');
		$this->assertEquals($config['init']['load'], array('Request'));
		$this->assertEquals($config['init']['test']['test']['load'], array('View\HTML'));
		$this->assertEquals($config['request']['default']['module'], 'extend');
		$this->assertEquals($config['request']['test']['default']['controller'], 'extend');
		$this->assertEquals($config['request']['test']['default']['action'], 'index');
		$this->assertEquals($config['request']['test']['test']['layout'], 'extend');
		$this->assertNull($config['invalid']);
	}

}