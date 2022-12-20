<?php

namespace Blastengine;
use Blastengine\Client as BEClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use \Exception;

class ApiClient
{
	public string $_end_point = "https://app.engn.jp/api/v1";

	public function get(
		string $request_path,
		bool $binary = false
	): array
	{
		return $this->_send("GET", $request_path, null, true, [], $binary);
	}

	public function post(
		string $request_path,
		array $body = null,
		bool $json = true,
		array $attachments = [],
	): array
	{
		return $this->_send("POST", $request_path, $body, $json, $attachments);
	}

	public function put(
		string $request_path,
		array $body = null,
		bool $json = true,
		array $attachments = [],
	): array
	{
		return $this->_send("PUT", $request_path, $body, $json, $attachments);
	}

	public function patch(
		string $request_path,
		array $body = null,
		bool $json = true,
		array $attachments = [],
	): array
	{
		return $this->_send("PATCH", $request_path, $body, $json, $attachments);
	}

	public function delete(string $request_path): array
	{
		return $this->_send("DELETE", $request_path);
	}

	private function _send(
		string $method,
		string $request_path,
		?array $body = null,
		bool $json = true,
		array $attachments = [],
		bool $binary = false
	): array
	{
		$url = $this->_end_point . $request_path;
		$token = BEClient::token();
		$headers = [
			"Authorization" => "Bearer $token"
		];
		$options = [
			'http_errors' => false,
			'headers' => $headers,
		];
		if ($json)
		{
			$options["headers"]["Content-Type"] = "application/json";
			$options["body"] = json_encode($body);
		} else
		{
			$params = [
				[
					'name' => 'data',
          'contents' => json_encode($body),
          'headers'  => ['Content-Type' => 'application/json']
        ]
			];
			foreach ($attachments as $path)
			{
				$param = [
					'name' => 'file',
          'contents' => fopen($path, "rb"),
          'headers'  => ['Content-Type' => mime_content_type($path)],
          'filename' => basename($path)
				];
				array_push($params, $param);
			}
			$options["multipart"] = $params;
		}
		$client = new Client();
		if ($binary) {
			$options["sink"] = tempnam(sys_get_temp_dir(), 'be');
		}
		$res = $client->request($method, $url, $options);
		if ($binary) {
			$zip = new \ZipArchive();
			$zip->open($options["sink"]);
			$content = $zip->getFromIndex(0);
			return [
				"raw" => $content
			];
		}
		return $this->handle_result($res, $binary);
	}

	function handle_result(Response $res, $binary): array
	{
		if ($res->getStatusCode() <> 200 && $res->getStatusCode() <> 201) {
			$message = json_decode($res->getBody()->getContents(), true);
			$error_messages = [];
			foreach( $message["error_messages"] as $key => $value) {
				$e = "Error in $key: " . implode(", ", $value);
				array_push($error_messages, $e);
			}
			throw new Exception(implode("\n", $error_messages));
		}
		$content = $res->getBody()->getContents();
		if ($binary) {
			return [
				"raw" => $content
			];
		} else {
			return json_decode($content, true);
		}
	}
}