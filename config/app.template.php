<?php
return [
	'db' => [
		'url' => 'hostname',
		'user' => 'user',
		'pass' => 'pass',
		'dbname' => 'dbname',
	],
	'sso' => [
		'clientID' => '',
		'secretKey' => '',
		'callbackURL' => 'http://example.com/callback-sso',
	],
	'discord' => [
		'guildID' => '',
		'clientID' => '',
		'secretKey' => '',
		'callbackURL' => 'http://example.com/discord/callback',
		'inviteLink' => 'https://discord.gg/yourinvitecode',
		'token' => '',
	],
	'phpbb3' => [
		'path' => '',
	],
	'auth' => [
		'nameEnforce' => true,
		'corpTicker' => true,
		'groups' => [
			'group1' => [
				'id' => 0,
				'role' => 'Role Name 1',
				'setCorpRole' => true,
				'corpColour' => 0x1f8b4c,
			],
			'group2' => [
				'id' => 0,
				'role' => 'Role Name 2',
				'setCorpRole' => false,
				'corpColour' => 0x1f8b4c,
			],
		],
	],
];