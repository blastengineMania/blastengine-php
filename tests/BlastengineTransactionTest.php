<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineTransactionTest extends TestCase
{
	private array $config;

	protected function setUp(): void
	{
		$content = file_get_contents(__DIR__ . "/config.json");
		$this->config = json_decode($content, true);
		Blastengine\Client::initialize($this->config["userId"], $this->config["apiKey"]);
	}

  public function testTransaction(): void
	{
		$transaction = new Blastengine\Transaction();
		$transaction
			->to($this->config["to"])
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email');
		$transaction->send();
		$this->assertIsInt($transaction->delivery_id());
	}
	
}