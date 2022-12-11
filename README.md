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

## Send transaction email with attachments

```php
$transaction = new Blastengine\Transaction();
$transaction
	->to($this->config["to"])
	->from($this->config["from"]["email"])
	->subject('Test subject')
	->text_part('This is test email')
	->attachment('/path/to/image')
	->attachment('/path/to/another');
try {
	$transaction->send();
} catch ( Exception $ex ) {
	// Error
}
```

## Get email info

```php
$transaction = new Blastengine\Transaction();
$transaction->delivery_id(100);
$transaction->get();
echo $transaction->delivery_type // => TRANSACTION
```

You can access other information below.

https://blastengine.jp/documents/#operation/delivery-detail-get

# License

MIT.
