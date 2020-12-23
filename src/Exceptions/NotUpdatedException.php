<?php declare(strict_types = 1);

/**
 * NotUpdatedException.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           22.12.20
 */

namespace FastyBird\RedisDbStoragePlugin\Exceptions;

class NotUpdatedException extends InvalidStateException implements IException
{

}
