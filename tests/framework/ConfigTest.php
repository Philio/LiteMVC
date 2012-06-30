<?php
class ConfigTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Test loading the production settings from test.ini
	 */
	public function testIni()
	{
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/tests/configs/test.ini', 'production');
		$this->assertEquals($config['autoload']['test'], '/apps/test');
		$this->assertEquals($config['init']['load'], array('Error', 'Session', 'Request'));
		$this->assertEquals($config['init']['test']['test']['load'], array('View\HTML'));
		$this->assertEquals($config['error']['display'], false);
		$this->assertEquals($config['session']['handler'], array('file'));
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

	/**
	 * Test loading the test settings from test.ini
	 */
	public function testIniExtend()
	{
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/tests/configs/test.ini', 'test');
		$this->assertEquals($config['autoload']['test'], '/apps/test');
		$this->assertEquals($config['init']['load'], array('Error', 'Session', 'Request'));
		$this->assertEquals($config['init']['test']['test']['load'], array('View\HTML'));
		$this->assertEquals($config['error']['display'], true);
		$this->assertEquals($config['session']['handler'], array('file'));
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

	/**
	 * Test cache write
	 */
	public function testCacheWrite()
	{
		$cache = $this->getMockBuilder('\LiteMVC\Cache\File')
				->disableOriginalConstructor()
				->getMock();
		$cache->expects($this->once())
				->method('set')
				->will($this->returnValue(true));

		$config = new \LiteMVC\Config\Ini();
		$config->setCache($cache);
		$config->load(\PATH . '/tests/configs/test.ini', 'production');
	}

	/**
	 * Test cache read
	 */
	public function testCacheRead()
	{
		$cache = $this->getMockBuilder('\LiteMVC\Cache\File')
				->disableOriginalConstructor()
				->getMock();
		$cache->expects($this->once())
				->method('get')
				->will($this->returnValue(
						array(
							'fmt' => filemtime(\PATH . '/tests/configs/test.ini'),
							'contents' => array('autoload' => array('test' => '/apps/test'))
						)));

		$config = new \LiteMVC\Config\Ini();
		$config->setCache($cache);
		$config->load(\PATH . '/tests/configs/test.ini', 'production');
		$this->assertThat($config->autoload, $this->logicalNot($this->equalTo(null)));
		$this->assertArrayHasKey('test', $config->autoload);
	}

	/**
	 * Test bad file
	 */
	public function testBadFile()
	{
		$this->setExpectedException('\LiteMVC\Config\Exception', 'File \'bad.ini\' not found.');
		$config = new \LiteMVC\Config\Ini();
		$config->load('bad.ini', 'production');
	}

	/**
	 * Test bad section
	 */
	public function testBadSection()
	{
		$this->setExpectedException('\LiteMVC\Config\Exception', 'A section can not extend multiple sections.');
		$config = new \LiteMVC\Config\Ini();
		$config->load(\PATH . '/tests/configs/test.ini', 'bad');
	}

}