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

	/**
	 * @return mixed[]
	 */
	public function get(
		string $request_path,
		bool $binary = false
	): array
	{
		return $this->_send("GET", $request_path, null, true, [], $binary);
	}

	/**
	 * @param mixed[] $body
	 * @param string[] $attachments
	 * @return mixed[]
	 */
	public function post(
		string $request_path,
		array $body = null,
		bool $json = true,
		array $attachments = [],
	): array
	{
		return $this->_send("POST", $request_path, $body, $json, $attachments);
	}

	/**
	 * @param mixed[] $body
	 * @param string[] $attachments
	 * @return mixed[]
	 */
	public function put(
		string $request_path,
		array $body = null,
		bool $json = true,
		array $attachments = [],
	): array
	{
		return $this->_send("PUT", $request_path, $body, $json, $attachments);
	}

	/**
	 * @param mixed[] $body
	 * @param string[] $attachments
	 * @return mixed[]
	 */
	public function patch(
		string $request_path,
		array $body = null,
		bool $json = true,
		array $attachments = [],
	): array
	{
		return $this->_send("PATCH", $request_path, $body, $json, $attachments);
	}

	/**
	 * @return mixed[]
	 */
	public function delete(string $request_path): array
	{
		return $this->_send("DELETE", $request_path);
	}

	/**
	 * @param string[] $attachments
	 * @param mixed[] $body
	 * @return mixed[]
	 */
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
			// Check if there are attachments and array
			if (!empty($attachments) && is_array($attachments))
			{
				// Loop through the attachments
				foreach ((array)$attachments as $path)
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
		}
		$client = new Client();
		if ($binary) {
			$options["sink"] = tempnam(sys_get_temp_dir(), 'be');
		}
		$res = $client->request($method, $url, $options);
		if ($binary) {
			$zip = new \ZipArchive();
			if (!is_string($options["sink"])) {
				throw new Exception("Sink is not a string");
			}
			$zip->open($options["sink"]);
			$content = $zip->getFromIndex(0);
			return [
				"raw" => $content
			];
		}
		return $this->handle_result($res, $binary);
	}

	/**
	 * @return mixed[]
	 */
	function handle_result(\Psr\Http\Message\ResponseInterface $res, bool $binary): array
	{
		if ($res->getStatusCode() <> 200 && $res->getStatusCode() <> 201) {
			$message = json_decode($res->getBody()->getContents(), true);
			$error_messages = [];
			if (!array_key_exists("error_messages", $message) ||
				empty($message["error_messages"]) ||
				!is_iterable((array)$message["error_messages"])) {
				throw new Exception($message);
			}
			foreach((array)$message["error_messages"] as $key => $value) {
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
		}
		return json_decode($content, true);
	}
}