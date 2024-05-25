<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineEmailTest extends TestCase
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

	public function testEmailCreate(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$this->assertIsInt($bulk->delivery_id());
		$email = new Blastengine\Email($bulk->delivery_id());
		$email
			->address('atsushi@moongift.jp', ['name' => 'Atsushi Nakatsugawa'])
			->save();
		$this->assertIsInt($email->email_id());
		$bulk->delete();
	}

	public function testEmailGet(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$this->assertIsInt($bulk->delivery_id());
		$email = new Blastengine\Email($bulk->delivery_id());
		$email
			->address('atsushi@moongift.jp', ['name' => 'Atsushi Nakatsugawa'])
			->save();
		$this->assertIsInt($email->email_id());
		$email2 = new Blastengine\Email();
		$email2->email_id($email->email_id());
		$email2->get();
		$this->assertEquals($email->email, $email2->email);
		$bulk->delete();
	}

	public function testEmailUpdate(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$this->assertIsInt($bulk->delivery_id());
		$email = new Blastengine\Email($bulk->delivery_id());
		$email
			->address('atsushi@moongift.jp', ['name' => 'Atsushi Nakatsugawa'])
			->save();
		$email
			->address('atsushi2@moongift.jp', ['name' => 'Atsushi Nakatsugawa'])
			->save();
		$this->assertIsInt($email->email_id());
		$email2 = new Blastengine\Email();
		$email2->email_id($email->email_id());
		$email2->get();
		$this->assertEquals($email->email, $email2->email);
		$bulk->delete();
	}

	public function testEmailDelete(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$this->assertIsInt($bulk->delivery_id());
		$email = new Blastengine\Email($bulk->delivery_id());
		$email
			->address('atsushi@moongift.jp', ['name' => 'Atsushi Nakatsugawa'])
			->save();
		$bol = $email->delete();
		$this->assertTrue($bol);
		$bulk->delete();
	}
}
