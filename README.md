# PHP SDK for blastengine

PHP SDK for blastengine is SDK for sending email using blastengine.

https://blastengine.jp/

# Usage

## Initialize

```php
Blastengine\Client::initialize($your_user_id, $your_api_key);
```

## Send transaction email

```php
$transaction = new Blastengine\Transaction();
$transaction
	->to($this->config["to"])
	->from($this->config["from"]["email"])
	->subject('Test subject')
	->text_part('This is test email');
try {
	$transaction->send();
} catch ( Exception $ex ) {
	// Error
}
```

# License

MIT.


