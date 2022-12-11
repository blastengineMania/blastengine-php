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
		$request_path
	): array
	{
		return $this->_send("GET", $request_path);
	}

	public function post(
		$request_path,
		$body = null,
		$json = true,
		$attachments = [],
	): array
	{
		return $this->_send("POST", $request_path, $body, $json, $attachments);
	}

	private function _send(
		$method,
		$request_path,
		$body = null,
		$json = true,
		$attachments = [],
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
		$res = $client->request($method, $url, $options);
		return $this->handle_result($res);
	}

	function handle_result(Response $res): array
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
		return json_decode($res->getBody()->getContents(), true);
	}
}