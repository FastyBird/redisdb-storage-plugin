<?php declare(strict_types = 1);

/**
 * IState.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     States
 * @since          0.1.0
 *
 * @date           22.12.20
 */

namespace FastyBird\RedisDbStoragePlugin\States;

use Ramsey\Uuid;

/**
 * Base state interface
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IState
{

	/**
	 * @return string[]
	 */
	public static function getCreateFields(): array;

	/**
	 * @return string[]
	 */
	public static function getUpdateFields(): array;

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getId(): Uuid\UuidInterface;

	/**
	 * @return string
	 */
	public function getRaw(): string;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
