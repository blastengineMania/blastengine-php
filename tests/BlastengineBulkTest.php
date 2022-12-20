<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../vendor/autoload.php');

class BlastengineBulkTest extends TestCase
{
	private array $config;

	protected function setUp(): void
	{
		$content = file_get_contents(__DIR__ . "/config.json");
		$this->config = json_decode($content, true);
		Blastengine\Client::initialize($this->config["userId"], $this->config["apiKey"]);
		
		for ($i = 1095; $i < 1110; $i++) {
			$bulk = new Blastengine\Bulk();
			$bulk->delivery_id($i);
			try {
				$bulk->get();
				if ($bulk->delivery_type == "BULK" && $bulk->status == "EDIT") {
					$bulk->delete();
					echo "delete $id";
					sleep(1);
				}
			} catch (\Exception $e) {
			}
		}
	}

  public function testBulk(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$bulk
			->to($this->config["to1"], ["name" => "Name 1"])
			->to($this->config["to2"], ["name" => "Name 2"])
			->update();
		// $bulk->send();
		$bulk->delete();
		$this->assertIsInt($bulk->delivery_id());
	}

  public function testBulkWithTime(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$bulk
			->to($this->config["to1"], ["name" => "Name 3"])
			->to($this->config["to2"], ["name" => "Name 4"])
			->update();
		$date = date("c", strtotime("+2 minutes"));
		$bulk->delete();
		// $bulk->send(new \DateTime($date));
		$this->assertIsInt($bulk->delivery_id());
	}

	public function testBulkCancel(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		$bulk
			->to($this->config["to1"], ["name" => "Name 3"])
			->to($this->config["to2"], ["name" => "Name 4"])
			->update();
		$date = date("c", strtotime("+2 minutes"));
		$bulk->send(new \DateTime($date));
		$bulk->get();
		$this->assertEquals("RESERVE", $bulk->status);
		$bulk->cancel();
		sleep(1);
		$bulk->get();
		$this->assertEquals("EDIT", $bulk->status);
		$bulk->delete();
	}

	public function testBulkImport(): void
	{
		$bulk = new Blastengine\Bulk();
		$bulk
			->from($this->config["from"]["email"])
			->subject('Test subject')
			->text_part('This is test email to __name__')
			->save();
		try {
			$job = $bulk->import(__DIR__ . "/test.csv");
			while ($job->finished() == false) {
				sleep(1);
			}
			$this->assertEquals($job->percentage, 100);
			if ($job->error) {
				// エラーがある場合
				$report = $job->error_report();
				$this->assertIsString($report);
			}
		} catch (\Exception $e) {
			$this->assertEquals("Import file is not valid", $e->getMessage());
		}
		$bulk->delete();
	}
}
