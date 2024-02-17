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

  public function testMailFind(): void
	{
		$params = array(
			"list_unsubscribe_mailto" => "moongift",
			"size" => 10,
		);
		$data = Blastengine\Mail::find($params);
		$this->assertIsString($data[0]->status);
	}

  public function testMailAll(): void
	{
		$params = array(
			"delivery_type" => ['SMTP'],
			"size" => 10,
		);
		$data = Blastengine\Mail::all($params);
		var_dump($data);
	}

  public function testLogFind(): void
	{
		$params = array(
			"delivery_type" => ['BULK'],
			"size" => 10,
		);
		$data = Blastengine\Log::find($params);
		var_dump($data);
	}
}