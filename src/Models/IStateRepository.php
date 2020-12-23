<?php declare(strict_types = 1);

/**
 * IStateRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           02.03.20
 */

namespace FastyBird\RedisDbStoragePlugin\Models;

use FastyBird\RedisDbStoragePlugin\States;
use Ramsey\Uuid;

/**
 * State repository interface
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IStateRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IState|null
	 */
	public function findOne(
		Uuid\UuidInterface $id
	): ?States\IState;

}
