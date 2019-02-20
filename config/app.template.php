<?php
return [
	'portal' => [
		'title' => '',
		'ts3_url' => 'example.com',
		'portal_url' => "http://example.com",
		'forum_url' => "http://example.com/forum",
		'killboard_url' => "http://example.com",
		'recruitment_url' => "http://example.com",
		'meta_description' => "",
		'meta_keywords' => "",
	],
	'services' => [
		'discord_enabled' => true,
		'ts3_enabled' => true,
		'phpbb3_enabled' => true, 
	],
	'sso' => [
		'clientID' => '',
		'secretKey' => '',
	],
	'phpbb3' => [
		'path' => '',
		'email_prefix' => '',
	],
	'auth' => [
		'corp_color' => 0x1f8b4c,
		'corp_hoist' => true,
		'member_color' => 0x9033ff,
		'blue_color' => 0x3374ff,
		'default_admins' => [0,],
		'role_member' => 'Member',
		'role_blue' => 'Blue',
		'role_exempt' => 'NoAuth',
		'set_corp_role' => true,
		'set_corp_ticker' => true,
		'set_name_enforce' => true,
	],
];