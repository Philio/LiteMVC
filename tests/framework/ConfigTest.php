<?php
class ConfigTest extends PHPUnit_Framework_TestCase
{

	public function testIni()
	{
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/configs/test.ini', 'production');
		$this->assertEquals($config['autoload']['test'], '/apps/test');
		$this->assertEquals($config['init']['load'], array('Error', 'Session', 'Auth', 'Request'));
		$this->assertEquals($config['init']['test']['test']['load'], array('View\HTML'));
		$this->assertEquals($config['error']['display'], false);
		$this->assertEquals($config['session']['handler'], array('file'));
		$this->assertEquals($config['auth']['model']['user'], '\Test\Model\User');
		$this->assertEquals($config['request']['default']['module'], 'test');
		$this->assertEquals($config['request']['test']['default']['controller'], 'test');
		$this->assertEquals($config['request']['test']['default']['action'], 'index');
		$this->assertEquals($config['request']['test']['test']['layout'], 'test');
		$this->assertEquals($config['database']['test']['driver'], 'MySQL');
		$this->assertEquals($config['database']['test']['host'], 'localhost');
		$this->assertEquals($config['database']['test']['username'], 'root');
		$this->assertEquals($config['database']['test']['password'], '');
		$this->assertEquals($config['database']['test']['database'], 'litemvc_test');
		$this->assertNull($config['invalid']);
	}

	public function testIniExtend()
	{
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/configs/test.ini', 'test');
		$this->assertEquals($config['autoload']['test'], '/apps/test');
		$this->assertEquals($config['init']['load'], array('Error', 'Session', 'Auth', 'Request'));
		$this->assertEquals($config['init']['test']['test']['load'], array('View\HTML'));
		$this->assertEquals($config['error']['display'], true);
		$this->assertEquals($config['session']['handler'], array('file'));
		$this->assertEquals($config['auth']['model']['user'], '\Test\Model\User');
		$this->assertEquals($config['request']['default']['module'], 'test');
		$this->assertEquals($config['request']['test']['default']['controller'], 'test');
		$this->assertEquals($config['request']['test']['default']['action'], 'index');
		$this->assertEquals($config['request']['test']['test']['layout'], 'test');
		$this->assertEquals($config['database']['test']['driver'], 'MySQL');
		$this->assertEquals($config['database']['test']['host'], 'localhost');
		$this->assertEquals($config['database']['test']['username'], 'root');
		$this->assertEquals($config['database']['test']['password'], '');
		$this->assertEquals($config['database']['test']['database'], 'litemvc_test');
		$this->assertEquals($config['database']['setup']['driver'], 'MySQL');
		$this->assertEquals($config['database']['setup']['host'], 'localhost');
		$this->assertEquals($config['database']['setup']['username'], 'root');
		$this->assertEquals($config['database']['setup']['password'], '');
		$this->assertNull($config['invalid']);
	}

}