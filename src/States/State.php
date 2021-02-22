<?php declare(strict_types = 1);

/**
 * State.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     States
 * @since          0.1.0
 *
 * @date           08.03.20
 */

namespace FastyBird\RedisDbStoragePlugin\States;

use FastyBird\RedisDbStoragePlugin\Exceptions;
use Nette;
use Ramsey\Uuid;

/**
 * Base state
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class State implements IState
{

	use Nette\SmartObject;

	/** @var Uuid\UuidInterface */
	private Uuid\UuidInterface $id;

	/** @var string */
	private string $raw;

	public function __construct(
		string $id,
		string $raw
	) {
		if (!Uuid\Uuid::isValid($id)) {
			throw new Exceptions\InvalidStateException('Provided state id is not valid');
		}

		$this->id = Uuid\Uuid::fromString($id);

		$this->raw = $raw;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRaw(): string
	{
		return $this->raw;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId(): Uuid\UuidInterface
	{
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getCreateFields(): array
	{
		return [
			'id',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getUpdateFields(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->getId()->toString(),
		];
	}

}
