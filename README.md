# Redis Lite for PHP

This is a very lightweight (minimalist) Redis client for PHP, as well as a mockup Redis client for doing tests without having to have to set up a working Redis server

## License

BSD 3 clause

## RedisClient usage example

```
require __DIR__ . '/RedisClient.php';

$redis = new RedisClient('tcp://127.0.0.1:6379');

$redis->set('pizza', json_encode([
	'price' => 42.00,
	'ingredients' => ['cheese', 'tomato', 'ananas']
]));

$value = $redis->get('pizza');

if (null === $value) {
        echo "Pizza not found\n";
        exit(1);
}

var_dump(json_decode($value));
```
