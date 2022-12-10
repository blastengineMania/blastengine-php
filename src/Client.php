<?php
namespace Blastengine;

class Client
{
	private static ?string $_user_id = null;
	private static ?string $_api_key = null;
	private static ?string $_token = null;

	public static function initialize(string $user_id, string $api_key)
  {
		self::$_user_id = $user_id;
		self::$_api_key = $api_key;
		self::generate_token();
	}

	public static function user_id(): string {
		return self::$_user_id;
	}

	public static function api_key(): string {
		return self::$_api_key;
	}

	public static function token(): string {
		return self::$_token;
	}

	public static function generate_token(): void {
		$str = self::$_user_id . self::$_api_key;
		self::$_token = base64_encode(strtolower(hash('sha256', $str)));
	}
}