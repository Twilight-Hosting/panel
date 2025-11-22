<?php

header('Content-Type: application/json');

echo(json_encode([
	'%%__NONCE__%%:%%__USER__%%' => [
		'version' => '1.1.0',
		'engine' => 'ainx',
		'timestamp' => 1763794635,
		'target' => 'ainx@1.13.21 beta-2024-12',
	]
]));