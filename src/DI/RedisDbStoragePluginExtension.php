<?php declare(strict_types = 1);

/**
 * RedisDbStoragePluginExtension.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           23.12.20
 */

namespace FastyBird\RedisDbStoragePlugin\DI;

use FastyBird\RedisDbStoragePlugin\Client;
use FastyBird\RedisDbStoragePlugin\Connections;
use FastyBird\RedisDbStoragePlugin\Models;
use Nette;
use Nette\DI;
use Nette\Schema;
use stdClass;

/**
 * Redis state storage extension container
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class RedisDbStoragePluginExtension extends DI\CompilerExtension
{

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'fbRedisDbStoragePlugin'
	): void {
		$config->onCompile[] = function (
			Nette\Configurator $config,
			DI\Compiler $compiler
		) use ($extensionName): void {
			$compiler->addExtension($extensionName, new RedisDbStoragePluginExtension());
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema\Schema
	{
		return Schema\Expect::structure([
			'connection' => Schema\Expect::structure([
				'host'     => Schema\Expect::string()->default('127.0.0.1'),
				'port'     => Schema\Expect::int(6379),
				'username' => Schema\Expect::string(null)->nullable(),
				'password' => Schema\Expect::string(null)->nullable(),
			]),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var stdClass $configuration */
		$configuration = $this->getConfig();

		$builder->addDefinition($this->prefix('connection'))
			->setType(Connections\Connection::class)
			->setArguments([
				'host'     => $configuration->connection->host,
				'port'     => $configuration->connection->port,
				'username' => $configuration->connection->username,
				'password' => $configuration->connection->password,
			]);

		$builder->addDefinition($this->prefix('client'))
			->setType(Client\Client::class);

		$builder->addDefinition($this->prefix('model.statesManagerFactory'))
			->setType(Models\StatesManagerFactory::class);

		$builder->addDefinition($this->prefix('model.stateRepositoryFactory'))
			->setType(Models\StateRepositoryFactory::class);
	}

}
