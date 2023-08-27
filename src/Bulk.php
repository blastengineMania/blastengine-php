<?php

namespace Blastengine;

class Bulk extends Base
{
	private array $_to = [];

	function __construct() {
		parent::__construct();
	}

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

	public function save(): bool
	{
		$path = "/deliveries/bulk/begin";
		$res = count($this->_attachments) == 0 ?
			$this->_apiClient->post($path, $this->_json()) :
			$this->_apiClient->post($path, $this->_json(), false, $this->_attachments);
		$this->delivery_id(intval($res["delivery_id"]));
		return true;
	}

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

	public function _get_csv(): string
	{
		$csv_file = tempnam(sys_get_temp_dir(), "blastengine");
		rename($csv_file, "$csv_file.csv");
		$fp = fopen("$csv_file.csv", "w");
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
	
	public function send(\DateTime $deliveryDate = null): bool
	{
		$path = is_null($deliveryDate) ? "/deliveries/bulk/commit/%s/immediate" : "/deliveries/bulk/commit/%s";
		$params = is_null($deliveryDate) ? null : [
			"reservation_time" => $deliveryDate->format(\DateTime::ATOM)
		];
		$this->_apiClient->patch(sprintf($path, $this->_delivery_id), $params);
		return true;
	}

	public function cancel(): bool
	{
		$path = sprintf("/deliveries/%s/cancel", $this->_delivery_id);
		$res = $this->_apiClient->patch($path);
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

	private function _json(): array
	{
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
		return $params;
	}
}
