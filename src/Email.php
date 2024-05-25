<?php

namespace Blastengine;

// Emailクラスは、Baseクラスを継承しています。
class Email extends Base
{
	public string $email;
	/**
	 * @var array{ key: string, value: string }[] = []
	 */
	private array $insert_code = [];
	private ?int $_email_id = null;
	public ?\DateTime $created_time;
	public ?\DateTime $updated_time;

	// コンストラクタ
	function __construct(int $delivery_id = null)
	{
		parent::__construct();
		if (!is_null($delivery_id)) {
			$this->delivery_id($delivery_id);
		}
	}

	// email_idのgetterとsetter
	function email_id(int $email_id = null): int
	{
		if (is_null($email_id)) {
			return $this->_email_id;
		}
		$this->_email_id = $email_id;
		return $this->_email_id;
	}

	/**
	 * アドレスと挿入コードを設定するメソッド
	 * @param array<string, string>|null $insert_code
	 */
	function address(string $email, array|null $insert_code = null): Email
	{
		$this->email = $email;
		if (!is_null($insert_code) && is_iterable($insert_code)) {
			$this->insert_code = [];
			foreach ($insert_code as $key => $value) {
				array_push($this->insert_code, [
					"key" => $key,
					"value" => $value
				]);
			}
		}
		return $this;
	}

	/**
	 * データを保存するメソッド (新規作成または更新)
	 * @return bool
	 */
	function save(): bool
	{
		if ($this->_email_id) {
			return $this->update();
		} else {
			return $this->create();
		}
	}

	/**
	 * データを作成するメソッド
	 * @return bool
	 */
	function create(): bool
	{
		$delivery_id = $this->delivery_id();
		$path = "/deliveries/$delivery_id/emails";
		$res = $this->_apiClient->post($path, $this->_json());
		$this->email_id(intval($res["email_id"]));
		return true;
	}

	// 更新メソッド
	function update(): bool
	{
		$email_id = $this->email_id();
		$path = "/deliveries/-/emails/$email_id";
		$res = $this->_apiClient->put($path, $this->_json());
		$this->email_id(intval($res["email_id"]));
		return true;
	}

	// 削除メソッド
	function delete(): bool
	{
		$email_id = $this->email_id();
		$path = "/deliveries/-/emails/$email_id";
		$res = $this->_apiClient->delete($path);
		return $res["email_id"] == $this->email_id();
	}

	// データ取得メソッド
	function get(): bool
	{
		$email_id = $this->email_id();
		$path = "/deliveries/-/emails/$email_id";
		$res = $this->_apiClient->get($path);
		$this->address($res["email"]);
		foreach ($res["insert_code"] as $insert_code) {
			array_push($this->insert_code, [
				"key" => preg_replace("/__(.*)__/", "$1", $insert_code["key"]),
				"value" => $insert_code["value"]
			]);
		}
		$this->delivery_id(intval($res["delivery_id"]));
		$this->email_id(intval($res["email_id"]));
		$this->created_time = new \DateTime($res["created_time"]);
		$this->updated_time = new \DateTime($res["updated_time"]);
		return true;
	}
	
	/**
	 * JSON形式でデータを返すプライベートメソッド
	 * @return array{email: string, insert_code: array{key: string, value: string}[]}
	 */
	function _json(): array
	{
		/**
		 * @var array{key: string, value: string}[]
		 */
		$insert_code = [];
		foreach ($this->insert_code as $code) {
			array_push($insert_code, [
				"key" => sprintf("__%s__", $code["key"]),
				"value" => $code["value"]
			]);
		}
		return [
			"email" => $this->email,
			"insert_code" => $insert_code
		];
	}
}
