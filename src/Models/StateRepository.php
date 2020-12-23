<?php declare(strict_types = 1);

/**
 * StateRepository.php
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
use Ramsey\Uuid;
use Throwable;

/**
 * State repository
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateRepository implements IStateRepository
{

	use Nette\SmartObject;

	/** @var Client\IClient */
	private Client\IClient $client;

	/** @var string */
	private string $entity;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	public function __construct(
		Client\IClient $client,
		string $entity = States\State::class,
		?Log\LoggerInterface $logger = null
	) {
		$this->client = $client;
		$this->entity = $entity;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOne(
		Uuid\UuidInterface $id
	): ?States\IState {
		$raw = $this->getRaw($id);

		if ($raw === null) {
			return null;
		}

		return States\StateFactory::create($this->entity, $raw);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return string|null
	 */
	private function getRaw(
		Uuid\UuidInterface $id
	): ?string {
		try {
			return $this->client->readKey($id->toString());

		} catch (Throwable $ex) {
			$this->logger->error('[FB:PLUGIN:REDISDB] Content could not be loaded', [
				'type'      => 'repository',
				'action'    => 'find_record',
				'property'  => $id->toString(),
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new Exceptions\InvalidStateException('Content could not be loaded from database', 0, $ex);
		}
	}

}
