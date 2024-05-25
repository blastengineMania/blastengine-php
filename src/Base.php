<?php

namespace Blastengine;
use \Exception;

class Base
{
	protected ApiClient $_apiClient;
	protected ?int $_delivery_id = null;
	protected string $_from_name = "";
	protected ?string $_from_email = null;
	protected ?string $_subject = null;
	protected ?string $_text_part = null;
	protected ?string $_html_part = null;
	protected string $_encode = "UTF-8";
	/**
	 * @var string[]
	 */
	protected array $_attachments = [];
	/**
	 * @var array{ email: string } | array{ url: string } | array{ email: string, url: string } | array{} | null
	 */
	protected ?array $_unsubscribe = null;

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

	/** @return static */
	function sets(mixed $params = []): Base
	{
		foreach ($params as $key => $value) {
			$this->set($key, $value);
		}
		return $this;
	}

	/**
	 * @param string | int | array{email: string, name:string} | null $value
	 * @return static
	 */
	function set(string $key, string | int | array | null $value): Base
	{
		switch ($key) {
			case "delivery_id":
				if ($value != null && is_int($value)) {
					$this->delivery_id($value);
				}
				break;
			case "updated_time":
				if ($value != null && is_string($value)) {
					$date = date_create_from_format('Y-m-d\TH:i:sP', $value);
					if ($date) {
						$this->updated_time = $date;
					}
				}
				break;
			case "created_time":
				if ($value != null && is_string($value)) {
					$date = date_create_from_format('Y-m-d\TH:i:sP', $value);
					if ($date) {
						$this->created_time = $date;
					}
				}
				break;
			case "delivery_type":
				if ($value != null && is_string($value)) {
					$this->delivery_type = $value;
				}
				break;
			case "subject":
				if ($value != null && is_string($value)) {
					$this->subject($value);
				}
				break;
			case "from":
				if ($value != null && is_array($value)) {
					$this->from($value["email"], $value["name"]);
				}
				break;
			case "reservation_time":
				if ($value != null && is_string($value)) {
					$date = date_create_from_format('Y-m-d\TH:i:sP', $value);
					if ($date) {
						$this->reservation_time = $date;
					}
				}
				break;
			case "delivery_time":
				if ($value != null && is_string($value)) {
					$date = date_create_from_format('Y-m-d\TH:i:sP', $value);
					if ($date) {
						$this->delivery_time = $date;
					}
				}
				break;
			case "status":
				if ($value != null && is_string($value)) {
					$this->status = $value;
				}
				break;
		};
		return $this;
	}

	function delivery_id(int $delivery_id = null): ?int
	{
		if (isset($delivery_id)) {
			$this->_delivery_id = $delivery_id;
		}
		return $this->_delivery_id;
	}

	/**
	 * @param string $email
	 * @param string | null $name
	 * @return static
	 */
	function from(string $email, ?string $name = null): Base
	{
		if (!is_null($name)) {
			$this->_from_name = $name;
		}
		$this->_from_email = $email;
		return $this;
	}

	/**
	 * @param string $subject
	 * @return static
	 */
	function subject(string $subject): Base
	{
		$this->_subject = $subject;
		return $this;
	}

	/**
	 * @param string $text_part
	 * @return static
	 */
	function text_part(string $text_part): Base
	{
		$this->_text_part = $text_part;
		return $this;
	}

	/**
	 * @param string $html_part
	 * @return static
	 */
	function html_part(string $html_part): Base
	{
		$this->_html_part = $html_part;
		return $this;
	}

	/**
	 * @param string $encode
	 * @return static
	 */
	function encode(string $encode): Base
	{
		$this->_encode = $encode;
		return $this;
	}

	/**
	 * @param array{ email: string } | array{ url: string } |  array{ email: string, url: string } | array{} $list_unsubscribe
	 * @return static
	 */
	function unsubscribe(array $list_unsubscribe): Base
	{
		if (count($list_unsubscribe) > 0) {
			$this->_unsubscribe = [];
		}
		if (isset($list_unsubscribe["url"])) {
			$this->_unsubscribe["url"] = $list_unsubscribe["url"];
		}
		if (isset($list_unsubscribe["email"])) {
			$this->_unsubscribe["email"] = $list_unsubscribe["email"];
		}
		return $this;
	}

	/**
	 * @param string $path
	 * @return static
	 */
	function attachment(string $path): Base
	{
		array_push($this->_attachments, $path);
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function cancel(): bool
	{
		$path = sprintf("/deliveries/%s/cancel", $this->_delivery_id);
		$res = $this->_apiClient->patch($path);
		return true;
	}

	/**
	 * @return bool
	 */
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

	/**
	 * @return bool
	 */
	function delete(): bool
	{
		$path = sprintf("/deliveries/%s", $this->_delivery_id);
		$res = $this->_apiClient->delete($path);
		return true;
	}
}