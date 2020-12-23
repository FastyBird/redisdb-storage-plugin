<?php declare(strict_types = 1);

/**
 * IClient.php
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

/**
 * Redis database client interface
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Client
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IClient
{

	/**
	 * @param string $key
	 * @param string $content
	 *
	 * @return bool
	 */
	public function writeKey(string $key, string $content): bool;

	/**
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function readKey(string $key): ?string;

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function removeKey(string $key): bool;

}
