<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\RedisDbStoragePlugin\Connections;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class RedisTest extends BaseMockeryTestCase
{

	public function testDefaultValues(): void
	{
		$config = new Connections\Connection('127.0.0.1', 1234, null, null);

		Assert::same('127.0.0.1', $config->getHost());
		Assert::same(1234, $config->getPort());
		Assert::null($config->getUsername());
		Assert::null($config->getPassword());
	}

}

$test_case = new RedisTest();
$test_case->run();
