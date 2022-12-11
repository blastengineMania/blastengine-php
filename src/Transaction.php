<?php

namespace Blastengine;

class Transaction extends Base
{
	private ?string $_to;
	private array $_cc = [];
	private array $_bcc = [];
	private array $_insert_code = [];
	private ?string $_from_name;
	private ?string $_from_email;
	private ?string $_subject;
	private ?string $_text_part;
	private ?string $_html_part;
	private string $_encode = "UTF-8";
	private array $_attachments = [];
	private ?int $_delivery_id;

	public ?string $delivery_type;
	public ?string $status;
	public ?int $total_count;
	public ?int $sent_count;
	public ?int $drop_count;
	public ?int $hard_error_count;
	public ?int $soft_error_count;
	public ?int $open_count;

	public ?\DateTime $delivery_time;
	public ?\DateTime $reservation_time;
	public ?\DateTime $created_time;
	public ?\DateTime $updated_time;

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

	function attachment(string $path): Transaction
	{
		array_push($this->_attachments, $path);
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

	function get(): bool
	{
		if (!isset($this->_delivery_id)) {
			throw new Exception("No delivery id found. You have to set it by delivery_id(id) method.");
		}
		$path = sprintf("/deliveries/%s", $this->_delivery_id);
		$res = $this->_apiClient->get($path);
		$this
			->from($res["from"]["email"], $res["from"]["name"])
			->subject($res["subject"])
			->text_part($res["text_part"])
			->html_part($res["html_part"]);
		$this->delivery_type = $res["delivery_type"];
		$this->status = $res["status"];
		$this->total_count = $res["total_count"];
		$this->sent_count = $res["sent_count"];
		$this->drop_count = $res["drop_count"];
		$this->hard_error_count = $res["hard_error_count"];
		$this->soft_error_count = $res["soft_error_count"];
		$this->open_count = $res["open_count"];
		if (!is_null($res["delivery_time"])) {
			$this->delivery_time = new \DateTime($res["delivery_time"]);
		}
		if (!is_null($res["reservation_time"])) {
			$this->reservation_time = new \DateTime($res["reservation_time"]);
		}
		$this->created_time = new \DateTime($res["created_time"]);
		$this->updated_time = new \DateTime($res["updated_time"]);
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