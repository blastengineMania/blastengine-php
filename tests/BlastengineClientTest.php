<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineClientTest extends TestCase
{
  public function testInitialize(): void
	{
		Blastengine\Client::initialize('aaa', 'bbb');
		$this->assertEquals(Blastengine\Client::user_id(), 'aaa');
		$this->assertEquals(Blastengine\Client::api_key(), 'bbb');
		$this->assertEquals(Blastengine\Client::token(), 'MmNlMTA5ZTlkMGZhZjgyMGIyNDM0ZTE2NjI5NzkzNGU2MTc3YjY1YWI5OTUxZGJjM2UyMDRjYWQ0Njg5YjM5Yw==');
	}

}