<?php declare(strict_types = 1);

/**
 * StateRepositoryFactory.php
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

use FastyBird\RedisDbStoragePlugin\Client;
use FastyBird\RedisDbStoragePlugin\Exceptions;
use FastyBird\RedisDbStoragePlugin\States;
use Nette;
use Psr\Log;

/**
 * State repository factory
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateRepositoryFactory
{

	use Nette\SmartObject;

	/** @var Client\IClient */
	private Client\IClient $client;

	/** @var Log\LoggerInterface|null */
	private ?Log\LoggerInterface $logger;

	public function __construct(
		Client\IClient $client,
		?Log\LoggerInterface $logger = null
	) {
		$this->client = $client;
		$this->logger = $logger;
	}

	/**
	 * @param string $entity
	 *
	 * @return StateRepository
	 */
	public function create(string $entity = States\State::class): StateRepository
	{
		if (!class_exists($entity)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided entity class %s does not exists', $entity));
		}

		return new StateRepository($this->client, $entity, $this->logger);
	}

}
