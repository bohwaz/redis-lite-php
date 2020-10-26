<?php

/**
 * This is a Mockup client for RedisClient, storing all data in PHP arrays
 * instead of contacting a remote Redis server
 */
class RedisClientMockup extends RedisClient
{
	public $values = [];

	public function __construct()
	{
	}

	public function __call(string $method, array $args)
	{
		$method = strtoupper($method);

		switch ($method)
		{
			case 'FLUSHDB':
				$this->values = [];
				return 'OK';
			case 'HGET':
				return @$this->values[$args[0]][$args[1]];
			case 'HGETALL':
				$out = [];
				foreach ($this->values[$args[0]] as $key=>$value) {
					$out[] = $key;
					$out[] = $value;
				}
				return $out;
			case 'HSET':
				list($key, $name, $value) = $args;
				if (!array_key_exists($key, $this->values))
				{
					$this->values[$key] = [];
				}

				$this->values[$key][(string)$name] = $value;
				return count($this->values[$key]);
			case 'ZCARD':
			case 'HLEN':
				return @count($this->values[$args[0]]);
			case 'ZADD':
				list($key, $score, $member) = $args;
				if (!array_key_exists($key, $this->values))
				{
					$this->values[$key] = [];
				}

				$this->values[$key][] = [(float)$score, $member];

				usort($this->values[$key], function ($a, $b) {
					if ($a[0] === $b[0]) return strcmp($a[1], $b[1]);
					return $a[0] > $b[0] ? 1 : -1;
				});

				return count($this->values[$key]);
			// Returns the rank of member in the sorted set stored
			case 'ZRANK':
				if (!isset($this->values[$args[0]]))
				{
					return null;
				}

				$i = 0;

				foreach ($this->values[$args[0]] as $item) {
					if ($item[1] == $args[1]) {
						return $i;
					}
					$i++;
				}

				return null;
			case 'ZSCORE':
				if (!isset($this->values[$args[0]]))
				{
					return null;
				}

				foreach ($this->values[$args[0]] as $item) {
					if ($item[1] == $args[1]) {
						return $item[0];
					}
				}

				return null;
			case 'ZREM':
				if (!isset($this->values[$args[0]]))
				{
					return null;
				}

				$i = 0;

				foreach ($this->values[$args[0]] as $index => $item)
				{
					list($key, $value) = $item;
					if ($value === $args[1])
					{
						unset($this->values[$args[0]][$index]);
						$i++;
					}
				}

				return $i;
			case 'ZRANGEBYSCORE':
				$out = [];

				if (!isset($this->values[$args[0]]))
				{
					return $out;
				}

				if ($args[1] == '-inf') {
					$args[1] = -INF;
				}

				if ($args[2] == '+inf') {
					$args[2] = INF;
				}

				foreach ($this->values[$args[0]] as $item)
				{
					list($key, $value) = $item;

					if ($key >= $args[1] && $key <= $args[2])
					{
						$out[] = $value;

						if (isset($args[3]) && $args[3] == 'WITHSCORES') {
							$out[] = $key;
						}
					}
				}

				if (isset($args[4], $args[5], $args[6]) && $args[4] == 'LIMIT') {
					$out = array_slice($out, $args[5], $args[6]*2);
				}

				return $out;
			case 'ZREMRANGEBYSCORE':
				if (!isset($this->values[$args[0]]))
				{
					return 0;
				}

				if ($args[1] == '-inf') {
					$args[1] = -INF;
				}

				if ($args[2] == '+inf') {
					$args[2] = INF;
				}

				$i = 0;

				foreach ($this->values[$args[0]] as $index => $item)
				{
					list($key, $value) = $item;
					if ($key >= $args[1] && $key <= $args[2])
					{
						unset($this->values[$args[0]][$index]);
						$i++;
					}
				}

				return $i;
			case 'ZREMRANGEBYRANK':
				if (!isset($this->values[$args[0]]))
				{
					return 0;
				}

				$i = 0;

				foreach ($this->values[$args[0]] as $index => $item)
				{
					if ($index >= $args[1] && $index <= $args[2])
					{
						unset($this->values[$args[0]][$index]);
						$i++;
					}
				}

				return $i;
			case 'SET':
				$this->values[$args[0]] = $args[1];
				return 'OK';
			case 'GET':
				return $this->values[$args[0]] ?? null;
			case 'MULTI':
			case 'EXEC':
			case 'SELECT':
				return 'OK';
			case 'DEL':
				unset($this->values[$args[0]]);
				return 1;
			default:
				throw new \Exception('Unknown command: ' . $method);
		}
	}
}
