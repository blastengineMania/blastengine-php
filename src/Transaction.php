<?php

namespace Blastengine;

class Transaction extends Base
{
	public ?string $_to;
	public ?string $_from_name;
	public ?string $_from_email;
	public ?string $_subject;
	public ?string $_text_part;
	public ?string $_html_part;
	public string $_encode = "UTF-8";
	public ?int $_delivery_id;

	function __construct() {
	}

	function to(string $email): Transaction
	{
		$this->_to = $email;
		return $this;
	}

	function delivery_id(int $delivery_id = null): ?int
	{
		if (isset($delivery_id)) {
			$this->_delivery_id = $delivery_id;
		}
		return $this->_delivery_id;
	}

	function from(string $email, ?string $name = null): Transaction
	{
		if (!is_null($name)) {
			$this->_from_name = $name;
		}
		$this->_from_email = $email;
		return $this;
	}

	function subject(string $subject): Transaction
	{
		$this->_subject = $subject;
		return $this;
	}

	function text_part(string $text_part): Transaction
	{
		$this->_text_part = $text_part;
		return $this;
	}

	function html_part(string $html_part): Transaction
	{
		$this->_html_part = $html_part;
		return $this;
	}

	function encode(string $encode): Transaction
	{
		$this->_encode = $encode;
		return $this;
	}

	function send(): bool {
		$apiClient = new ApiClient();
		$res = $apiClient->send("POST", "/deliveries/transaction", $this->_json());
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
		if (isset($this->_from_name)) {
			$params["from"]["name"] = $this->_from_name;
		}
		if (isset($this->_html_part)) {
			$params["html_part"] = $this->_html_part;
		}
		return $params;
	}
}