<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineMailFetchTest extends TestCase
{
	/**
	 * @var array{userId: string, apiKey: string, from: array{email: string, name: string}, to: string, to1: string, to2: string}
	 */
	private array $config;

	protected function setUp(): void
	{
		$content = file_get_contents(__DIR__ . "/config.json");
		if (!$content) {
			$this->markTestSkipped("config.json not found");
		}
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

  public function testMailDelete(): void
	{
		$params = array(
			"delivery_type" => ['BULK'],
			"status" => ['EDIT'],
			"size" => 100,
		);
		$ary = Blastengine\Mail::all($params);
		foreach ($ary as $mail) {
			$mail->delete();
		}
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