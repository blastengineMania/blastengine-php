<?php

namespace Blastengine;

class Transaction extends Base
{
	private ?string $_to;
	private array $_cc = [];
	private array $_bcc = [];
	private array $_insert_code = [];

	function __construct() {
		parent::__construct();
	}

	function to(string $email): Transaction
	{
		$this->_to = $email;
		return $this;
	}

	function cc(string $email): Transaction
	{
		array_push($this->_cc, $email);
		return $this;
	}

	function bcc(string $email): Transaction
	{
		array_push($this->_bcc, $email);
		return $this;
	}

	function insert_code(string $key, string $value): Transaction
	{
		array_push($this->_insert_code, [
			"key" => sprintf("__%s__", $key),
			"value" => $value
		]);
		return $this;
	}

	function send(): bool
	{
		$path = "/deliveries/transaction";
		$res = count($this->_attachments) == 0 ?
			$this->_apiClient->post($path, $this->_json()) :
			$this->_apiClient->post($path, $this->_json(), false, $this->_attachments);
		$this->delivery_id(intval($res["delivery_id"]));
		return true;
	}

	private function _json(): Array
	{
		$params = [
			"from" => [
				"email" => $this->_from_email
			],
			"to" => $this->_to,
			"subject" => $this->_subject,
			"encode" => $this->_encode,
			"text_part" => $this->_text_part,
		];
		if (count($this->_cc) > 0) {
			$params["cc"] = $this->_cc;
		}
		if (count($this->_bcc) > 0) {
			$params["bcc"] = $this->_bcc;
		}
		if (count($this->_insert_code) > 0) {
			$params["insert_code"] = $this->_insert_code;
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