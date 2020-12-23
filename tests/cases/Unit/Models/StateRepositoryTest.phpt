<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\RedisDbStoragePlugin\Client;
use FastyBird\RedisDbStoragePlugin\Models;
use FastyBird\RedisDbStoragePlugin\States;
use Mockery;
use Nette\Utils;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Ramsey\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class StateRepositoryTest extends BaseMockeryTestCase
{

	public function testFetchEntity(): void
	{
		$id = Uuid\Uuid::uuid4();

		$data = [
			'id'       => $id->toString(),
			'datatype' => null,
		];

		$redisClient = $this->mockRedisWithData($id, $data);

		$repository = $this->createRepository($redisClient);

		$state = $repository->findOne($id);

		Assert::type(States\State::class, $state);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param mixed[] $data
	 *
	 * @return Mockery\MockInterface|Client\IClient
	 */
	private function mockRedisWithData(
		Uuid\UuidInterface $id,
		array $data
	): Mockery\MockInterface {
		$data['_id'] = $data['id'];

		$redisClient = Mockery::mock(Client\Client::class);
		$redisClient
			->shouldReceive('readKey')
			->with($id->toString())
			->andReturn(Utils\Json::encode($data))
			->times(1);

		return $redisClient;
	}

	/**
	 * @param Mockery\MockInterface|Client\IClient $redisClient
	 *
	 * @return Models\StateRepository
	 */
	private function createRepository(
		Mockery\MockInterface $redisClient
	): Models\StateRepository {
		return new Models\StateRepository($redisClient);
	}

}

$test_case = new StateRepositoryTest();
$test_case->run();
