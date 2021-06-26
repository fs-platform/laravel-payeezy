# payeezy

* laravel 集成 payeezy

## Installation

```phpregexp
composer require smbear/payeezy
```


## Using this package

```phpregexp
php artisan vendor:publish --provider=Smbear\Payeezy\Providers\PayeezyServiceProvider
```

```phpregexp
use Smbear\Payeezy\Facades\Payeezy;
```

## Contributing

* token 初始化token
* integration 集成3ds
* payment 支付
* jwt 生成jwt的token
* valid 验证jwt 是否有效
