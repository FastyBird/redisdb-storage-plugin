<?php declare(strict_types = 1);

use FastyBird\RedisDbStoragePlugin\States;

return [
	'one' => [
		States\State::class,
		[],
	],
	'two' => [
		States\State::class,
		[
			'id' => 'invalid-string',
		],
	],
];
