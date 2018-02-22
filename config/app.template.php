<?php
return [
	'services' => [
		'discord_enabled' => true,
		'ts3_enabled' => true,
		'phpbb3_enabled' => true, 
	],
	'sso' => [
		'clientID' => '',
		'secretKey' => '',
		'callbackURL' => 'http://example.com/callback-sso',
	],
	'ts3_url' => 'host',
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
		'default_admins' => [0,],
		'nameEnforce' => true,
		'corpTicker' => true,
		'blue_role' => 'Blue',
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