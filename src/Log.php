<?php
namespace Blastengine;

class Log extends Base
{
	public int $maillog_id;
	public string $email;
	public string $last_response_code;
	public string $last_response_message;
	public ?\DateTime $open_time;

	function __construct() {
		parent::__construct();
	}

	/**
	 * @param array{anchor?: int, count?: int, email?: string, delivery_type?: string[], delivery_id?: int, status?: string[], response_code?: string[], delivery_start?: string, delivery_end?: string} $params
	 * @return Log[]
	 */
	static function find(array $params = []): array {
		$api_client = new ApiClient();
		$query_string = http_build_query($params);
		$query_string = preg_replace('/%5B[0-9]+%5D/', '%5B%5D', $query_string);
		$path = "/logs/mails/results?$query_string";
		$data = $api_client->get($path);
		$ary = [];
		foreach ($data["data"] as $options)
		{
			$log = new Log();
			$log->sets($options);
			array_push($ary, $log);
		}
		return $ary;
	}

	function set(string $key, string | array | int | null $value): Log
	{
		switch ($key) {
		case "maillog_id":
			if ($value != null && is_int($value)) {
				$this->maillog_id = $value;
			}
			break;
		case "email":
			if ($value != null && is_string($value)) {
				$this->email = $value;
			}
			break;
		case "last_response_code":
			if ($value != null && is_string($value)) {
				$this->last_response_code = $value;
			}
			break;
		case "last_response_message":
			if ($value != null && is_string($value)) {
				$this->last_response_message = $value;
			}
			break;
		case "open_time":
			if ($value != null && is_string($value)) {
				$date = date_create_from_format('Y-m-d\TH:i:sP', $value);
				if ($date) {
					$this->open_time = $date;
				}
			}
			break;
		default:
			parent::set($key, $value);
			break;
		}
		return $this;
	}
}