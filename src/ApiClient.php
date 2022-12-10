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

	public function send(
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
		if ($json) {
			$headers["Content-Type"] = "application/json";
		}
		$options = [
			'http_errors' => false,
			'headers' => $headers,
			'body' => json_encode($body)
		];
		$client = new Client();
		$res = $client->request($method, $url, $options);
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