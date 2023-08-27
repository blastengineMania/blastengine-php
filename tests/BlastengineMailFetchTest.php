<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineMailFetchTest extends TestCase
{
	private array $config;

	protected function setUp(): void
	{
		$content = file_get_contents(__DIR__ . "/config.json");
		$this->config = json_decode($content, true);
		Blastengine\Client::initialize($this->config["userId"], $this->config["apiKey"]);
	}

  public function testMailTransaction(): void
	{
		$params = array(
			"status" => ['EDIT'],
			"size" => 10,
		);
		$data = Blastengine\Mail::find($params);
		var_dump($data);
	}
}