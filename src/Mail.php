<?php

namespace Blastengine;
use \Exception;

class Mail extends Base
{
	/**
	 * @var array{ email: string, insert_code?: array{ key: string, value: string }[] }[]
	 */
	private array $_to = [];
	/**
	 * @var string[]
	 */
	private array $_cc = [];
	/**
	 * @var string[]
	 */
	private array $_bcc = [];

	function __construct() {
		parent::__construct();
	}

	/**
	 * @param array{text_part?: string, html_part?: string, subject?: string, from?: string, list_unsubscribe_mailto?: string, list_unsubscribe_url?: string, status?: string[], delivery_type?: string[], delivery_start?: string, delivery_end?: string, size?: int, page?: int, sort?: string} $params
	 * @return (Transaction|Bulk)[]
	 */
	static function find(array $params = []): array {
		$api_client = new ApiClient();
		$query_string = http_build_query($params);
		$query_string = preg_replace('/%5B[0-9]+%5D/', '%5B%5D', $query_string);
		$path = "/deliveries?$query_string";
		$data = $api_client->get($path);
		$ary = [];

		foreach ($data["data"] as $options) {
			$mail = $options['delivery_type'] == 'TRANSACTION' ? new Transaction() : new Bulk();
			$mail->sets($options);
			array_push($ary, $mail);
		}
		return $ary;
	}

	/**
	 * @param array{text_part?: string, html_part?: string, subject?: string, from?: string, list_unsubscribe_mailto?: string, list_unsubscribe_url?: string, status?: string[], delivery_type?: string[], delivery_start?: string, delivery_end?: string, size?: int, page?: int, sort?: string} $params
	 * @return (Transaction|Bulk)[]
	 */
	static function all(array $params = []): array {
		$api_client = new ApiClient();
		$query_string = http_build_query($params);
		$query_string = preg_replace('/%5B[0-9]+%5D/', '%5B%5D', $query_string);
		$path = "/deliveries/all?$query_string";
		$data = $api_client->get($path);
		$ary = [];
		foreach ($data["data"] as $options) {
			$mail = $options['delivery_type'] == 'TRANSACTION' ? new Transaction() : new Bulk();
			$mail->sets($options);
			array_push($ary, $mail);
		}
		return $ary;
	}

	/**
	 * @param array<string, string> $insert_code
	 * @return Mail
	 */
	function to(string $email, array $insert_code = []): Mail
	{
		$params = [
			"email" => $email,
			"insert_code" => []
		];
		foreach ($insert_code as $key => $value) {
			array_push($params["insert_code"], [
				"key" => $key,
				"value" => $value
			]);
		}
		array_push($this->_to, $params);
		return $this;
	}

	function cc(string $email): Mail
	{
		array_push($this->_cc, $email);
		return $this;
	}

	function bcc(string $email): Mail
	{
		array_push($this->_bcc, $email);
		return $this;
	}

	function send(\DateTime $delivery_date = null): bool
	{
		if (count($this->_cc) > 0 || count($this->_bcc) > 0) {
			if ($delivery_date != null) {
				throw new \Exception("You can't send a transaction email with CC or BCC at a later date");
			}
			if (count($this->_to) > 1) {
				throw new \Exception("You can't send a transaction email with CC or BCC to more than one recipient");
			}
		}
		if ($delivery_date == null && count($this->_to) == 1) {
			return $this->send_transaction();
		}
		return $this->send_bulk($delivery_date);
	}

	/**
	 * @return bool
	 */
	function send_transaction(): bool
	{
		$transaction = new Transaction();
		$to = $this->_to[0];
		$transaction
			->to($to["email"])
			->from($this->_from_email, $this->_from_name)
			->subject($this->_subject)
			->text_part($this->_text_part)
			->encode($this->_encode);
		if ($this->_html_part) {
			$transaction->html_part($this->_html_part);
		}
		if ($this->_unsubscribe) {
			$transaction->unsubscribe($this->_unsubscribe);
		}
		// Check if there are any insert codes
		if (array_key_exists("insert_code", $to)) {
			$insert_code = $to["insert_code"];
			if (is_iterable($insert_code)) {
				foreach ($insert_code as $code) {
					$transaction->insert_code($code["key"], $code["value"]);
				}
			}
		}
		if (count($this->_attachments) > 0) {
			foreach ($this->_attachments as $attachment) {
				$transaction->attachment($attachment);
			}
		}
		$transaction->send();
		$delivery_id = $transaction->delivery_id();
		$this->delivery_id($delivery_id);
		return $delivery_id > 0;
	}

	/**
	 * @return bool
	 */
	function send_bulk(\DateTime $delivery_date = null): bool
	{
		$bulk = new Bulk();
		$bulk
			->from($this->_from_email, $this->_from_name)
			->subject($this->_subject)
			->text_part($this->_text_part)
			->encode($this->_encode);
		if ($this->_html_part) {
			$bulk->html_part($this->_html_part);
		}
		if ($this->_unsubscribe) {
			$bulk->unsubscribe($this->_unsubscribe);
		}
		$bulk->save();
		foreach ($this->_to as $to) {
			$insert_code = [];
			if (array_key_exists("insert_code", $to) && is_iterable($to["insert_code"])) {
				foreach ($to["insert_code"] as $code) {
					$insert_code[$code["key"]] = $code["value"];
				}
			}
			$bulk->to($to["email"], $insert_code);
		}
		$bulk->update();
		$bulk->send($delivery_date);
		$delivery_id = $bulk->delivery_id();
		$this->delivery_id($delivery_id);
		return $delivery_id > 0;
	}

}