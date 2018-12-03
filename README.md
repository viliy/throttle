# Throttle  阀值中间件

## usage

> composer require viliy/throttle dev-master

> vim config/throttle.php

```php
<?php
    return []; // see to .throttle.yml

```

> cp vendor/viliy/throttle/.throttle.yml .throttle.yml

> vim config/app.php

```php

    'services' => [
        \FastD\ServiceProvider\CacheServiceProvider::class,
        \FastD\ServiceProvider\LoggerServiceProvider::class,
        \FastD\ServiceProvider\RouteServiceProvider::class,
        
        // added the end
        \Viliy\Throttle\Provider\ThrottleRequestProvider::class,
    ],

```
