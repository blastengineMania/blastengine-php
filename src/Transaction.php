<?php

namespace Blastengine;

class Transaction extends Base
{
	/**
	 * @var string
	 */
	private ?string $_to;
	/**
	 * @var string[]
	 */
	private array $_cc = [];
	/**
	 * @var string[]
	 */
	private array $_bcc = [];
	/**
	 * @var array{ key: string, value: string }[]
	 */
	private array $_insert_code = [];

	function __construct() {
		parent::__construct();
	}

	/**
	 * @return Transaction
	 */
	function to(string $email): Transaction
	{
		$this->_to = $email;
		return $this;
	}

	/**
	 * @return Transaction
	 */
	function cc(string $email): Transaction
	{
		array_push($this->_cc, $email);
		return $this;
	}

	/**
	 * @return Transaction
	 */
	function bcc(string $email): Transaction
	{
		array_push($this->_bcc, $email);
		return $this;
	}

	/**
	 * @return Transaction
	 */
	function insert_code(string $key, string $value): Transaction
	{
		array_push($this->_insert_code, [
			"key" => sprintf("__%s__", $key),
			"value" => $value
		]);
		return $this;
	}

	/**
	 * @return bool
	 */
	function send(): bool
	{
		$path = "/deliveries/transaction";
		$res = count($this->_attachments) == 0 ?
			$this->_apiClient->post($path, $this->_json()) :
			$this->_apiClient->post($path, $this->_json(), false, $this->_attachments);
		$this->delivery_id(intval($res["delivery_id"]));
		return true;
	}

	/**
	 * @return array{
	 *   from: array{
	 *     email: string, name?: string
	 *   }, 
	 *   to: string,
	 *   subject: string,
	 *   encode: string,
	 *   text_part: string,
	 *   cc?: string[],
	 *   bcc?: string[],
	 *   insert_code?: array{
	 *     key: string, value: string
	 *   }[],
	 *   html_part?: string,
	 *   list_unsubscribe?: array{
	 *     url?: string, mailto?: string
	 *   }
	 * }
	 */
	private function _json(): array
	{
		$params = [
			"from" => [
				"email" => (string) $this->_from_email
			],
			"to" => (string) $this->_to,
			"subject" => (string) $this->_subject,
			"encode" => $this->_encode,
			"text_part" => (string) $this->_text_part,
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