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
			->insert_code('name', 'Test')
			->text_part('This is test email to __name__');
		$transaction->send();
		$this->assertIsInt($transaction->delivery_id());
	}
	
  public function testTransactionWithAttachment(): void
	{
		$transaction = new Blastengine\Transaction();
		$transaction
			->to($this->config["to"])
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email')
			->attachment('./README.md')
			->attachment('./tests/logo.png');
		$transaction->send();
		$this->assertIsInt($transaction->delivery_id());
	}

  public function testTransactionInfo(): void
	{
		$transaction = new Blastengine\Transaction();
		$transaction
			->to($this->config["to"])
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email');
		$transaction->send();
		$transaction->get();
		$this->assertIsInt($transaction->delivery_id());
		$this->assertEquals($transaction->delivery_type, "TRANSACTION");
	}
}