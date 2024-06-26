<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineMailTest extends TestCase
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

  public function testMailTransaction(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->to($this->config["to"], array("name1" => "Test"))
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name1__');
		$mail->send();
		$this->assertIsInt($mail->delivery_id());
	}
	
  public function testMailTransactionWithAttachment(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->to($this->config["to"], array("name1" => "Test"))
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email')
			->attachment('./README.md')
			->attachment('./tests/logo.png');
		$mail->send();
		$this->assertIsInt($mail->delivery_id());
	}

  public function testMailBulk(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email __name1__');
		for ($i = 0; $i < 60; $i++) {
			$mail->to("be$i@moongift.co.jp", array("name1" => "Test $i"));
		}
		$mail->send();
		$this->assertIsInt($mail->delivery_id());
	}

  public function testMailBulkCancel(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email __name1__');
		$mail->to("be@moongift.co.jp", array("name1" => "Test"));
		// 30 sec later
		$delivery_date = new DateTime();
		$delivery_date->add(new DateInterval('PT30S'));
		$mail->send($delivery_date);
		$this->assertIsInt($mail->delivery_id());
		$mail->get();
		$this->assertTrue($mail->status == "RESERVE");
		$mail->cancel();
		$mail->get();
		$this->assertTrue($mail->status == "EDIT");
	}

  public function testMailUnsubscribeUrl(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->from($this->config["from"]["email"])
			->subject('Test unsuscribe subject url')
			->text_part('This is test email __name1__')
			->unsubscribe(array("url" => "http://example.com/unsubscribe/__hash__"));
		for ($i = 0; $i < 2; $i++) {
			$mail->to("atsushi+$i@moongift.co.jp", array("name1" => "Test $i", "hash" => "hash_$i"));
		}
		$mail->send();
		$this->assertIsInt($mail->delivery_id());
	}

  public function testMailUnsubscribeEmail(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->from($this->config["from"]["email"])
			->subject('Test unsuscribe subject email')
			->text_part('This is test email __name1__')
			->unsubscribe(array("email" => "unsubscribe+__hash__@moongift.co.jp"));
		for ($i = 0; $i < 2; $i++) {
			$mail->to("atsushi+$i@moongift.co.jp", array("name1" => "Test $i", "hash" => "hash_$i"));
		}
		$mail->send();
		$this->assertIsInt($mail->delivery_id());
	}

  public function testMailUnsubscribeBoth(): void
	{
		$mail = new Blastengine\Mail();
		$mail
			->from($this->config["from"]["email"])
			->subject('Test unsuscribe subject both')
			->text_part('This is test email __name1__')
			->unsubscribe(array("email" => "unsubscribe+__hash__@moongift.co.jp", "url" => "http://example.com/unsubscribe/__hash__"));
		for ($i = 0; $i < 2; $i++) {
			$mail->to("atsushi+$i@moongift.co.jp", array("name1" => "Test $i", "hash" => "hash_$i"));
		}
		$mail->send();
		$this->assertIsInt($mail->delivery_id());
	}
}