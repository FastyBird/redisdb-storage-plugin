<?php declare(strict_types = 1);

use Ramsey\Uuid;

$id = Uuid\Uuid::uuid4();

return [
	'one' => [
		$id,
		[
			'value' => 'keyValue',
		],
		[
			'id'      => $id->toString(),
			'value'   => 'keyValue',
			'created' => null,
		],
		[
			'id'      => $id->toString(),
			'value'   => 'keyValue',
			'created' => null,
			'updated' => null,
		],
	],
	'two' => [
		$id,
		[
			'id'    => $id->toString(),
			'value' => null,
		],
		[
			'id'      => $id->toString(),
			'value'   => null,
			'created' => null,
		],
		[
			'id'      => $id->toString(),
			'value'   => null,
			'created' => null,
			'updated' => null,
		],
	],
];
