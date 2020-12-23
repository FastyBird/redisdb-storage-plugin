<?php declare(strict_types = 1);

/**
 * Client.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Client
 * @since          0.1.0
 *
 * @date           23.12.20
 */

namespace FastyBird\RedisDbStoragePlugin\Client;

use FastyBird\RedisDbStoragePlugin\Connections;
use Nette;
use Predis;
use Predis\Response;

/**
 * Redis database client
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Client
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Client implements IClient
{

	use Nette\SmartObject;

	/** @var Connections\IConnection */
	private Connections\IConnection $connection;

	/** @var Predis\Client<mixed> */
	private Predis\Client $redis;

	public function __construct(
		Connections\IConnection $connection
	) {
		$this->connection = $connection;

		$options = [
			'scheme' => 'tcp',
			'host'   => $connection->getHost(),
			'port'   => $connection->getPort(),
		];

		if ($connection->getUsername() !== null) {
			$options['username'] = $connection->getUsername();
		}

		if ($connection->getPassword() !== null) {
			$options['password'] = $connection->getPassword();
		}

		$this->redis = new Predis\Client($options);
	}

	/**
	 * {@inheritDoc}
	 */
	public function writeKey(string $key, string $content): bool
	{
		/** @var Response\Status $response */
		$response = $this->redis->set($key, $content);

		return $response->getPayload() === 'OK';
	}

	/**
	 * {@inheritDoc}
	 */
	public function readKey(string $key): ?string
	{
		return $this->redis->get($key);
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeKey(string $key): bool
	{
		if ($this->redis->get($key) !== null) {
			$response = $this->redis->del($key);

			return $response === 1;
		}

		return true;
	}

}
