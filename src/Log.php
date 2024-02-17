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

	function set(string $key, string | int | array | null $value): Log
	{
		switch ($key) {
		case "maillog_id":
			$this->maillog_id = $value;
			break;
		case "email":
			$this->email = $value;
			break;
		case "last_response_code":
			$this->last_response_code = $value;
			break;
		case "last_response_message":
			$this->last_response_message = $value;
			break;
		case "open_time":
			if ($value != null) {
				$this->open_time = date_create_from_format('Y-m-d\TH:i:sP', $value);
			}
			break;
		default:
			parent::set($key, $value);
			break;
		}
		return $this;
	}
}