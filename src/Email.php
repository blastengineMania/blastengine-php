<?php

namespace Blastengine;

// Emailクラスは、Baseクラスを継承しています。
class Email extends Base
{
	public string $email;
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

	// アドレスと挿入コードを設定するメソッド
	function address(string $email, array $insert_code = []): Email
	{
		$this->email = $email;
		$this->insert_code = $insert_code;
		return $this;
	}

	// データを保存するメソッド (新規作成または更新)
	function save(): bool
	{
		if ($this->_email_id) {
			return $this->update();
		} else {
			return $this->create();
		}
	}

	// 新規作成メソッド
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
	
	// JSON形式でデータを返すプライベートメソッド
	function _json(): array
	{
		$insert_code = [];
		foreach ($this->insert_code as $key => $value) {
			array_push($insert_code, [
				"key" => sprintf("__%s__", $key),
				"value" => $value
			]);
		}
		return [
			"email" => $this->email,
			"insert_code" => $insert_code
		];
	}
}
