# Laravel-ShurjoPay 💳

> A package for handling ShurjoPay payment gateway in Laravel applications

Laravel-ShurjoPay is a package for handling ShurjoPay payment gateway easily in Laravel applications. It has some advantages over the official package provided by ShurjoPay—like auto-discovery for **Laravel 5.5+**—and is much more configurable and well-structured.

Installation
---

To install the package run

```
composer require sowrensen/laravel-shurjopay
```

Register (for Laravel < 5.5)
---

Register the service provider in config/app.php

```php
'providers' => [
    //...
    Sowren\ShurjoPay\ShurjoPayServiceProvider::class,
    //...
];
```

Publish
---

To publish the config file, run the following command

```
php artisan vendor:publish --tag=ls-config
```

Environment Variables (Optional)
---

ShurjoPay would provide you some credentials, define them in your `.env` file:

```dotenv
SHURJOPAY_SERVER_URL=
MERCHANT_USERNAME=
MERCHANT_PASSWORD=
MERCHANT_KEY_PREFIX=
```

Now if you like to keep your secret credentials somewhere else, there's nothing to worry about. You can load them on the fly. 😁 Check the following section.

Usage
---

The usage of the package is simple. First import the `Sowren\ShurjoPay\ShurjoPayService` class.

```php
use Sowren\ShurjoPay\ShurjoPayService;
```

If you have defined your credentials in `.env` file, then just create an object,

```php
$client = new ShurjoPayService(500, route('home'));
```

If you want to load your ShurjoPay configuration in runtime, pass them to the constructor,

```php
$client = new ShurjoPayService(
            500, 
            route('home'),
            'serverUrl', 
            'merchantUsername',
            'merchantPassword',
            'merchantKeyPrefix'
        );
```

...and call the `generateTxnId` and `makePayment` method.

```php
$txnId = $client->generateTxnId(); // Pass any string to set your own unique id
$client->makePayment();
```

That's it! After successful or failed attempt it will redirect to the route you provided along with ShurjoPay response parameters.
