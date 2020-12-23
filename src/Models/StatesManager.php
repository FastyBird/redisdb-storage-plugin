<?php declare(strict_types = 1);

/**
 * StatesManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           03.03.20
 */

namespace FastyBird\RedisDbStoragePlugin\Models;

use Closure;
use Consistence;
use DateTimeInterface;
use FastyBird\RedisDbStoragePlugin\Client;
use FastyBird\RedisDbStoragePlugin\Exceptions;
use FastyBird\RedisDbStoragePlugin\States;
use Nette;
use Nette\Utils;
use Psr\Log;
use Ramsey\Uuid;
use stdClass;
use Throwable;

/**
 * States manager
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @method onAfterCreate(States\IState $state)
 * @method onAfterUpdate(States\IState $state, States\IState $old)
 * @method onAfterDelete(States\IState $state)
 */
class StatesManager implements IStatesManager
{

	use Nette\SmartObject;

	/** @var Closure[] */
	public array $onAfterCreate = [];

	/** @var Closure[] */
	public array $onAfterUpdate = [];

	/** @var Closure[] */
	public array $onAfterDelete = [];

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
	public function create(
		Uuid\UuidInterface $id,
		Utils\ArrayHash $values
	): States\IState {
		try {
			$raw = $this->createKey($id, $values, $this->entity::CREATE_FIELDS);

			$state = States\StateFactory::create($this->entity, $raw);

		} catch (Throwable $ex) {
			var_dump($ex->getMessage());
			$this->logger->error('[FB:PLUGIN:REDISDB] Key could not be created', [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
				'data'      => [
					'state' => $id->toString(),
				],
			]);

			throw new Exceptions\InvalidStateException('State could not be created', $ex->getCode(), $ex);
		}

		$this->onAfterCreate($state);

		return $state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(
		States\IState $state,
		Utils\ArrayHash $values
	): States\IState {
		try {
			$raw = $this->updateKey($state, $values, $state::UPDATE_FIELDS);

			$updatedState = States\StateFactory::create(get_class($state), $raw);

		} catch (Exceptions\NotUpdatedException $ex) {
			return $state;

		} catch (Throwable $ex) {
			$this->logger->error('[FB:PLUGIN:REDISDB] Key could not be updated', [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
				'data'      => [
					'state' => $state->getId()->toString(),
				],
			]);

			throw new Exceptions\InvalidStateException('State could not be updated', $ex->getCode(), $ex);
		}

		$this->onAfterUpdate($updatedState, $state);

		return $updatedState;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(
		States\IState $state
	): bool {
		$result = $this->deleteKey($state->getId());

		if ($result === false) {
			return false;
		}

		$this->onAfterDelete($state);

		return true;
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Utils\ArrayHash $values
	 * @param mixed[] $fields
	 *
	 * @return string
	 */
	protected function createKey(
		Uuid\UuidInterface $id,
		Utils\ArrayHash $values,
		array $fields
	): string {
		try {
			// Initialize structure
			$data = new stdClass();

			$values->offsetSet('id', $id->toString());

			foreach ($fields as $field => $default) {
				$value = $default;

				if (is_numeric($field)) {
					$field = $default;

					// If default is not defined => field is required
					if (!$values->offsetExists($field)) {
						throw new Exceptions\InvalidArgumentException(sprintf('Value for key "%s" is required', $field));
					}

					$value = $values->offsetGet($field);

				} elseif ($values->offsetExists($field)) {
					if ($values->offsetGet($field) !== null) {
						$value = $values->offsetGet($field);

						if ($value instanceof DateTimeInterface) {
							$value = $value->format(DATE_ATOM);

						} elseif ($value instanceof Utils\ArrayHash) {
							$value = (array) $value;

						} elseif ($value instanceof Consistence\Enum\Enum) {
							$value = $value->getValue();

						} elseif (is_object($value)) {
							$value = (string) $value;
						}

					} else {
						$value = null;
					}
				}

				$data->{$field} = $value;
			}

			$this->client->writeKey($id->toString(), Utils\Json::encode($data));

			$raw = $this->client->readKey($id->toString());

			if ($raw === null) {
				throw new Exceptions\NotUpdatedException('Created state could not be loaded from database');
			}

			return $raw;

		} catch (Throwable $ex) {
			$this->logger->error('[FB:PLUGIN:REDISDB] Key could not be created', [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new Exceptions\InvalidStateException('State could not be created', $ex->getCode(), $ex);
		}
	}

	/**
	 * @param States\IState $state
	 * @param Utils\ArrayHash $values
	 * @param string[] $fields
	 *
	 * @return string
	 */
	protected function updateKey(
		States\IState $state,
		Utils\ArrayHash $values,
		array $fields
	): string {
		$raw = $state->getRaw();

		try {
			$data = Utils\Json::decode($raw);

			$isUpdated = false;

			foreach ($fields as $field) {
				if ($values->offsetExists($field)) {
					$value = $values->offsetGet($field);

					if ($value instanceof DateTimeInterface) {
						$value = $value->format(DATE_ATOM);

					} elseif ($value instanceof Utils\ArrayHash) {
						$value = (array) $value;

					} elseif ($value instanceof Consistence\Enum\Enum) {
						$value = $value->getValue();

					} elseif (is_object($value)) {
						$value = (string) $value;
					}

					/** @phpstan-ignore-next-line */
					if (!in_array($field, array_keys(get_object_vars($data)), true) || $data->{$field} !== $value) {
						/** @phpstan-ignore-next-line */
						$data->{$field} = $value;

						$isUpdated = true;
					}
				}
			}

			// Save data only if is updated
			if (!$isUpdated) {
				throw new Exceptions\NotUpdatedException('Stored state is same as update');
			}

			$this->client->writeKey($state->getId()->toString(), Utils\Json::encode($data));

			$raw = $this->client->readKey($state->getId()->toString());

			if ($raw === null) {
				throw new Exceptions\NotUpdatedException('Updated state could not be loaded from database');
			}

			return $raw;

		} catch (Exceptions\NotUpdatedException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			$this->logger->error('[FB:PLUGIN:REDISDB] Key could not be updated', [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
				'document'  => [
					'id' => $state->getId()->toString(),
				],
			]);

			throw new Exceptions\InvalidStateException('State could not be updated', $ex->getCode(), $ex);
		}
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return bool
	 */
	protected function deleteKey(
		Uuid\UuidInterface $id
	): bool {
		try {
			return $this->client->removeKey($id->toString());

		} catch (Throwable $ex) {
			$this->logger->error('[FB:PLUGIN:REDISDB] Key could not be deleted', [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
				'document'  => [
					'id' => $id->toString(),
				],
			]);
		}

		return false;
	}

}
