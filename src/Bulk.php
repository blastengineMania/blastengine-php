<?php

namespace Blastengine;

class Bulk extends Base
{
	/**
	 * @var array{ email: string, insert_code: array{key: string, value: string}[] }[] = []
	 */
	private array $_to = [];

	/**
	 * return Bulk
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param array<string, string> $insert_code
	 * @return Bulk
	 */
	function to(string $email, array $insert_code = []): Bulk
	{
		$params = [
			"email" => $email,
			"insert_code" => []
		];
		foreach ($insert_code as $key => $value) {
			array_push($params["insert_code"], [
				"key" => sprintf("__%s__", $key),
				"value" => $value
			]);
		}
		array_push($this->_to, $params);
		return $this;
	}

	/**
	 * @return bool
	 */
	public function save(): bool
	{
		$path = "/deliveries/bulk/begin";
		$res = count($this->_attachments) == 0 ?
			$this->_apiClient->post($path, $this->_json()) :
			$this->_apiClient->post($path, $this->_json(), false, $this->_attachments);
		$this->delivery_id(intval($res["delivery_id"]));
		return true;
	}

	/**
	 * @return bool
	 */
	public function update(): bool
	{
		if (count($this->_to) > 50) {
			$csv_file = $this->_get_csv();
			$job = $this->import($csv_file, true);
			while ($job->finished() == false) {
				sleep(1);
			}
			// reset
			$this->_to = [];
		} else {
			$path = sprintf("/deliveries/bulk/update/%s", $this->_delivery_id);
			$this->_apiClient->put($path, $this->_json());
			$this->_to = []; // Reset
		}
		return true;
	}

	/**
	 * @return string
	 */
	public function _get_csv(): string
	{
		$csv_file = tempnam(sys_get_temp_dir(), "blastengine");
		if (!$csv_file) {
			throw new \Exception("Failed to create temporary file");
		}
		rename($csv_file, "$csv_file.csv");
		$fp = fopen("$csv_file.csv", "w");
		if (!$fp) {
			throw new \Exception("Failed to open temporary file");
		}
		// make header
		$headers = ["email"];
		foreach ($this->_to as $to) {
			foreach ($to["insert_code"] as $insert_code) {
				if (!in_array($insert_code["key"], $headers)) {
					array_push($headers, $insert_code["key"]);
				}
			}
		}
		fputcsv($fp, $headers);
		foreach ($this->_to as $to) {
			$row = [$to["email"]];
			$insert_code = [];
			foreach ($to["insert_code"] as $code) {
				$insert_code[$code["key"]] = $code["value"];
			}
			foreach ($headers as $header) {
				if ($header == "email") {
					continue;
				}
				if (array_key_exists($header, $insert_code)) {
					array_push($row, $insert_code[$header]);
				} else {	
					array_push($row, "");
				}
			}
			fputcsv($fp, $row);
		}
		fclose($fp);
		return "$csv_file.csv";
	}
	
	/**
	 * @return bool
	 */
	public function send(\DateTime $deliveryDate = null): bool
	{
		$path = is_null($deliveryDate) ? "/deliveries/bulk/commit/%s/immediate" : "/deliveries/bulk/commit/%s";
		$params = is_null($deliveryDate) ? null : [
			"reservation_time" => $deliveryDate->format(\DateTime::ATOM)
		];
		$this->_apiClient->patch(sprintf($path, $this->_delivery_id), $params);
		return true;
	}


	public function import(string $file_path, bool $ignore_errors = false): Job
	{
		$path = sprintf("/deliveries/%s/emails/import", $this->_delivery_id);
		$params = $ignore_errors ? ["ignore_errors" => true] : null;
		$res = $this->_apiClient->post($path, $params, false, [$file_path]);
		$job = new Job(intval($res["job_id"]));
		return $job;
	}

	/**
	 * @return array{ from: array{ email: string }, subject: string, text_part: string, to: array{ email: string, insert_code: array{ key: string, value: string }[] }[], encode: string, html_part: string, list_unsubscribe: array{ mailto?: string, url?: string } }
	 */
	private function _json(): array
	{
		/**
		 * @var array{ from: array{ email: string }, subject: string, text_part: string, to: array{ email: string, insert_code: array{ key: string, value: string }[] }[], encode: string, html_part: string, list_unsubscribe: array{ mailto?: string, url?: string } }
		 */
		$params = [
			"from" => [
				"email" => $this->_from_email
			],
			"subject" => $this->_subject,
			"text_part" => $this->_text_part,
		];
		if (isset($this->_delivery_id)) {
			$params["to"] = $this->_to;
		} else {
			$params["encode"] = $this->_encode;
		}
		if (isset($this->_from_name)) {
			$params["from"]["name"] = $this->_from_name;
		}
		if (isset($this->_html_part)) {
			$params["html_part"] = $this->_html_part;
		}
		if (isset($this->_unsubscribe)) {
			$params["list_unsubscribe"] = [];
			if (isset($this->_unsubscribe["url"])) {
				$params["list_unsubscribe"]["url"] = $this->_unsubscribe["url"];
			}
			if (isset($this->_unsubscribe["email"])) {
				$params["list_unsubscribe"]["mailto"] = "mailto:" . $this->_unsubscribe["email"];
			}
		}
		return $params;
	}
}
