<?php

namespace Blastengine;

class Base
{
	protected ApiClient $_apiClient;
	protected ?int $_delivery_id;
	protected ?string $_from_name;
	protected ?string $_from_email;
	protected ?string $_subject;
	protected ?string $_text_part;
	protected ?string $_html_part;
	protected string $_encode = "UTF-8";
	protected array $_attachments = [];

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

	function __construct()
	{
		$this->_apiClient = new ApiClient();
	}

	function delivery_id(int $delivery_id = null): ?int
	{
		if (isset($delivery_id)) {
			$this->_delivery_id = $delivery_id;
		}
		return $this->_delivery_id;
	}

	function from(string $email, ?string $name = null): Base
	{
		if (!is_null($name)) {
			$this->_from_name = $name;
		}
		$this->_from_email = $email;
		return $this;
	}

	function subject(string $subject): Base
	{
		$this->_subject = $subject;
		return $this;
	}

	function text_part(string $text_part): Base
	{
		$this->_text_part = $text_part;
		return $this;
	}

	function html_part(string $html_part): Base
	{
		$this->_html_part = $html_part;
		return $this;
	}

	function encode(string $encode): Base
	{
		$this->_encode = $encode;
		return $this;
	}

	function attachment(string $path): Transaction
	{
		array_push($this->_attachments, $path);
		return $this;
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

	function delete(): bool
	{
		$path = sprintf("/deliveries/%s", $this->_delivery_id);
		$res = $this->_apiClient->delete($path);
		return true;
	}
}