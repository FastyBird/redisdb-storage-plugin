<?php declare(strict_types = 1);

use Ramsey\Uuid;

$id = Uuid\Uuid::uuid4();
$now = new DateTimeImmutable();

return [
	'one' => [
		$id,
		[
			'id'      => $id->toString(),
			'value'   => 'value',
			'created' => $now->format(DATE_ATOM),
			'updated' => null,
		],
		[
			'updated' => $now->format(DATE_ATOM),
		],
		[
			'id'      => $id->toString(),
			'value'   => 'value',
			'created' => $now->format(DATE_ATOM),
			'updated' => $now->format(DATE_ATOM),
		],
		[
			'id'      => $id->toString(),
			'value'   => 'value',
			'created' => $now->format(DATE_ATOM),
			'updated' => $now->format(DATE_ATOM),
		],
	],
	'two' => [
		$id,
		[
			'id'      => $id->toString(),
			'value'   => 'value',
			'created' => $now->format(DATE_ATOM),
			'updated' => null,
		],
		[
			'updated' => $now->format(DATE_ATOM),
			'value'   => 'updated',
		],
		[
			'id'      => $id->toString(),
			'value'   => 'updated',
			'created' => $now->format(DATE_ATOM),
			'updated' => $now->format(DATE_ATOM),
		],
		[
			'id'      => $id->toString(),
			'value'   => 'updated',
			'created' => $now->format(DATE_ATOM),
			'updated' => $now->format(DATE_ATOM),
		],
	],
];
