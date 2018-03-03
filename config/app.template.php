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
		'email_prefix' => '',
	],
	'auth' => [
		'corp_color' => 0x1f8b4c,
		'corp_color_member' => 0x1f8b4c,
		'corp_color_blue' => 0x1f8b4c,
		'default_admins' => [0,],
		'role_member' => 'Member',
		'role_blue' => 'Blue',
		'role_exempt' => 'NoAuth',
		'set_corp_role' => true,
		'set_corp_ticker' => true,
		'set_name_enforce' => true,
		'groups' => [
			'group1' => [
				'id' => 0,
				'set_corp_role' => true,
			],
			'group2' => [
				'id' => 0,
				'setCorpRole' => false,
			],
		],
	],
];