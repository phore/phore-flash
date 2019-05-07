# Phore flash

## Install

```
composer requre phore/flash
```

## Basic usage

```php
$flash = new Flash("redis://redisHost");

$key = $flash->withQuickHash("some value")->withTTL(30);

$key->get()
$key->set("some value");
$key->del();
```


