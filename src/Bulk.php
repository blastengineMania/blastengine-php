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
		if (count($this->_to) == 50) {
			throw new \Exception("Bulk email can only have 50 recipients");
		}
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
		$path = sprintf("/deliveries/bulk/update/%s", $this->_delivery_id);
		$this->_apiClient->put($path, $this->_json());
		$this->_to = []; // Reset
		return true;
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
