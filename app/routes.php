<?php

use Slim\Http\Request;
use Slim\Http\Response;

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use RestCord\DiscordClient;

//$mc = new Memcached();
//$mc->addServer("localhost", 11211);
//

require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/cURL.php');
require_once(__DIR__ . '/../lib/other.php');
//require_once(__DIR__ . '/../lib/sync.class.php');
require_once(__DIR__ . '/../lib/auth.class.php');
require_once(__DIR__ . '/../lib/esi.class.php');
require_once(__DIR__ . '/../lib/ts3.class.php');
require_once(__DIR__ . '/../lib/discord.class.php');
require_once(__DIR__ . '/../lib/phpbb3.class.php');

// Load app config
$config_app = require __DIR__ . '/../config/app.php';
if ($config_app['services']['discord_enabled']) {
	$config_discord = require __DIR__ . '/../config/discord.php';
} else {
	$config_discord = null;
}
if ($config_app['services']['ts3_enabled']) {
	$config_ts3 = require __DIR__ . '/../config/ts3.php';
} else {
	$config_ts3 = null;
}

// Routes
$app->get('/', function (Request $request, Response $response) use ($config_app) {

	return $this->view->render($response, 'index.html', [
		'portal_config' => $config_app['portal'],
	]);

});

$app->get('/login', function (Request $request, Response $response) use ($config_app) {
	$_SESSION['eve_state'] = uniqid();

	//$ssoURL = "https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=" . $config_app['sso']['callbackURL'] . "&client_id=" . $config_app['sso']['clientID'] . "&scope=publicData" . "&state=" . $state;

	$sso_url = "https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=" . $config_app['sso']['callbackURL'] . "&client_id=" . $config_app['sso']['clientID'] . "&state=" . $_SESSION['eve_state'];

	return $this->view->render($response, 'login.html', [
		'portal_config' => $config_app['portal'],
		'sso_url' => $sso_url
    ]);
});

$app->get('/login/contacts', function (Request $request, Response $response) use ($config_app) {
	$db_client = new citadelDB();

	if ($db_client->user_admin($_SESSION['user_id'])) {
		$_SESSION['eve_state'] = uniqid();
		
		$sso_url = "https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=" . $config_app['sso']['callbackURL'] . "&client_id=" . $config_app['sso']['clientID'] . "&scope=esi-alliances.read_contacts.v1" . "&state=" . $_SESSION['eve_state'] . 'contacts';

		unset($db_client);

		return $this->view->render($response, 'login.html', [
			'portal_config' => $config_app['portal'],
			'sso_url' => $sso_url
		]);

	} else {
		unset($db_client);
		return $response->withRedirect('/dashboard');
	}
});

$app->get('/logout', function (Request $request, Response $response, $args) use ($config_app) {
	$db_client = new citadelDB();

	$cookie = FigRequestCookies::get($request, 'session_key');
	$session_key = $cookie->getValue();

	$response = FigResponseCookies::expire($response, 'session_key');
	$db_client->session_delete($session_key);
	session_unset();
	unset($db_client);

    return $response->withRedirect('/');
});

$app->get('/dashboard', function (Request $request, Response $response) use ($config_app, $config_discord) {

	$cookie = FigRequestCookies::get($request, 'session_key');
	$session_key = $cookie->getValue();

	if (isset($session_key)) {
		$db_client = new citadelDB();

		if ($db_client->session_keycheck($session_key)) {

			if (!isset($_SESSION['user_id'])) {
				$_SESSION['user_id'] = $db_client->session_get_id($session_key);
			}

			if (!isset($_SESSION['character_id'])) {
				$_SESSION['character_id'] = $db_client->user_get_by_id($_SESSION['user_id']);
			}

			if ($config_app['services']['discord_enabled']) {
				if (!isset($_SESSION['discord_id'])) {
					$_SESSION['discord_id'] = $db_client->discord_get_id($_SESSION['user_id']);
				}
			} else {
				$_SESSION['discord_id'] = null;
			}

			if ($config_app['services']['ts3_enabled']) {
				if (!isset($_SESSION['teamspeak_token'])) {
					$_SESSION['teamspeak_token'] = $db_client->teamspeak_get_token($_SESSION['user_id']);
				}
			} else {
				$_SESSION['teamspeak_token'] = null;
			}

			if ($config_app['services']['phpbb3_enabled']) {
				if (!isset($_SESSION['phpbb3_username'])) {
					$_SESSION['phpbb3_username'] = $db_client->phpbb3_get_username($_SESSION['user_id']);
				}
			} else {
				$_SESSION['phpbb3_username'] = null;
			}

			if (!isset($_SESSION['character_info'])) {
				$_SESSION['character_info'] = $db_client->character_info_get($_SESSION['character_id']);
			}

			if (!isset($_SESSION['corporation_info'])) {
				$_SESSION['corporation_info'] = $db_client->corporation_info_get($_SESSION['character_info']['corporation_id']);
			}

			if (isset($_SESSION['character_info']['alliance_id']) && $_SESSION['character_info']['alliance_id'] != 1) {
				if (!isset($_SESSION['alliance_info'])) {
					$_SESSION['alliance_info'] = $db_client->alliance_info_get($_SESSION['character_info']['alliance_id']);
				}
				$alliance_name = $_SESSION['alliance_info']['name'];
			} else {
				$alliance_name = "You are not in Alliance";
			}

			$discord_authorized = false;
			if ($_SESSION['discord_id'] == null) {
				$_SESSION['discord_state'] = uniqid();
				$discord_url = "https://discordapp.com/api/oauth2/authorize?client_id=" . $config_discord["client_id"] . "&redirect_uri=" . $config_discord['callback_url'] . "&response_type=code" . "&scope=identify guilds.join" . "&state=" . $_SESSION['discord_state'];
			} else {
				$discord_authorized = true;
				$discord_url = null;
			}

			if (!isset($_SESSION['is_admin'])) {
				$_SESSION['is_admin'] = $db_client->user_admin($_SESSION['user_id']);
			}

			if ($config_app['auth']['set_name_enforce']) {
				$teamspeak_nick = $_SESSION['character_info']['name'];
				if ($config_app['auth']['set_corp_ticker']) {
					$teamspeak_nick = "[".$_SESSION['corporation_info']['ticker']."] ".$teamspeak_nick;
				}
			}

			unset($db_client);

			return $this->view->render($response, 'dashboard_services.html', [
				'portal_config' => $config_app['portal'],
				'character_id' => $_SESSION['character_id'],
				'character_name' => $_SESSION['character_info']['name'],
				//'corporation_name' => $_SESSION['corporation_info']['name'],
				//'alliance_name' => $alliance_name,
				'discord_authorized' => $discord_authorized,
				'discord_url' => $discord_url,
				'discord_enabled' => $config_app['services']['discord_enabled'],
				'ts3_url' => $config_app['portal']['ts3_url'],
				'ts3_nick' => $teamspeak_nick,
				'ts3_enabled' => $config_app['services']['ts3_enabled'],
				'ts3_token' => $_SESSION['teamspeak_token'],
				'phpbb3_username' => $_SESSION['phpbb3_username'],
				'phpbb3_enabled' => $config_app['services']['phpbb3_enabled'],
				'is_admin' => $_SESSION['is_admin'],
			]);

		} else {
			unset($db_client);
			return $response->withRedirect('/login');
		}
	} else {
		return $response->withRedirect('/login');
	}
})->setName('dashboard');

$app->get('/dashboard/refresh', function (Request $request, Response $response) {

	session_unset();

    return $response->withRedirect('/dashboard');

});

$app->get('/admin/groups', function (Request $request, Response $response) use ($config_app) {
	$db_client = new citadelDB();

	if ($db_client->user_admin($_SESSION['user_id'])) {
		$groups = $db_client->groups_getall_nothidden();
		$users = $db_client->user_get_all_full();

		unset($db_client);
		if (!isset($_SESSION['admin'])) {
			$_SESSION['admin'] = null;
		}
		if (isset($_SESSION['admin']['temp'])) {
			var_dump($_SESSION['admin']['temp']);
		}

		return $this->view->render($response, 'dashboard_groups.html', [
			'portal_config' => $config_app['portal'],
			'character_id' => $_SESSION['character_id'],
			'character_name' => $_SESSION['character_info']['name'],
			'is_admin' => $_SESSION['is_admin'],
			'admin_array' => $_SESSION['admin'],
			'users' => $users,
			'groups' => $groups
		]);

	} else {
		unset($db_client);
		return $response->withRedirect('/dashboard');
	}
});

$app->post('/admin/groups', function (Request $request, Response $response) use ($config_app) {
	$_SESSION['admin'] = array();
	$action = $request->getParam("submit");
	$db_client = new citadelDB();

	switch ($action) {
		case 'add':
			$user_id = $request->getParam("user_id");
			$user_groups = $db_client->usergroups_getby_user($user_id);
			$pending_groups = $request->getParam("pending_groups");
			if (isset($user_id) && $user_id != "null") {
				if (isset($pending_groups)) {
					foreach ($pending_groups as $group_id) {
						if (!in_array($group_id,$user_groups)) {
							$db_client->usergroups_add($user_id, $group_id);
						}
					}
				}
			}
			break;

		case 'delete':
			$user_id = $request->getParam("user_id");
			$user_groups = $db_client->usergroups_getby_user($user_id);
			$pending_groups = $request->getParam("pending_groups");
			if (isset($user_id) && $user_id != "null") {
				if (isset($pending_groups)) {
					foreach ($pending_groups as $group_id) {
						if (in_array($group_id,$user_groups)) {
							$db_client->usergroups_delete($user_id, $group_id);
						}
					}
				}
			}
			break;

		case 'get':
			$user_id = $request->getParam("user_id");
			if (isset($user_id) && $user_id != "null") {
				$_SESSION['admin']['user_groups'] = $db_client->usergroups_getfullby_user($user_id);
				$user = $db_client->user_get_full($user_id);
				$_SESSION['admin']['user_name'] = $user['name'];
			}
			break;

		case 'group_add':
			$group_name = $request->getParam("group_name");
			$group_color = hexdec($request->getParam("group_color"));
			$all_services = (int)$request->getParam("all_services");
			$teamspeak = (int)$request->getParam("teamspeak");
			$discord = (int)$request->getParam("discord");
			$phpbb3 = (int)$request->getParam("phpbb3");
			$hoist = (int)$request->getParam("hoist");
			
			if (!isset($hoist)) {
				$hoist = 0;
			}
			if (isset($all_services) && $all_services) {
				$teamspeak = 1;
				$discord = 1;
				$phpbb3 = 1;
			}

			$group = $db_client->groups_getby_name($group_name);
			if ($group == null) {
				$db_client->groups_add($group_name, $group_color, $hoist);
				$group = $db_client->groups_getby_name($group_name);
				if ($teamspeak) {
					$db_client->groups_set_teamspeak($group['id']);
				}
				if ($discord) {
					$db_client->groups_set_discord($group['id']);
				}
				if ($phpbb3) {
					$db_client->groups_set_phpbb3($group['id']);
				}
			} else {
				$db_client->groups_update($group_name, 0, $group_color, $hoist);
				$group = $db_client->groups_getby_name($group_name);
				if ($teamspeak) {
					$db_client->groups_set_teamspeak($group['id']);
				} else {
					$db_client->groups_unset_teamspeak($group['id']);
				}
				if ($discord) {
					$db_client->groups_set_discord($group['id']);
				} else {
					$db_client->groups_unset_discord($group['id']);
				}
				if ($phpbb3) {
					$db_client->groups_set_phpbb3($group['id']);
				} else {
					$db_client->groups_unset_phpbb3($group['id']);
				}
			}

			break;

		case 'group_deactivate':
			$group_id = $request->getParam("group_id");
			$db_client->groups_service_disable_by_id($group_id);
			$db_client->group_set_hidden($group_id);
			break;

		case 'clear':
			unset($_SESSION['admin']);
			break;

		default:
			break;
	}

	unset($db_client);
	return $response->withRedirect('/admin/groups');
});

$app->get('/callback-sso', function (Request $request, Response $response) use ($config_app, $config_discord) {

	$state = $request->getParam("state");
	$code = $request->getParam("code");

	$base64 = base64_encode($config_app["sso"]["clientID"] . ":" . $config_app["sso"]["secretKey"]);
	$tokenURL = "https://login.eveonline.com/oauth/token";
	$verifyURL = "https://login.eveonline.com/oauth/verify";
	$data = json_decode(sendData($tokenURL, array(
		"grant_type" => "authorization_code",
		"code" => $code
	), array("Authorization: Basic {$base64}")));

	$access_token = $data->access_token;
	$refresh_token = $data->refresh_token;

	$data = json_decode(sendData($verifyURL, array(), array("Authorization: Bearer {$access_token}")));
	$character_id = $data->CharacterID;

	$db_client = new citadelDB();

	if ($state == $_SESSION['eve_state']) {
		if (isset($character_id)) {
			$esi_client = new ESIClient();
			$auth_manager = new AuthManager($db_client);
			$character_esi = $esi_client->character_get_details($character_id);
			$corporation_id = $character_esi['corporation_id'];
			$alliance_id = $character_esi['alliance_id'];
			$user = $db_client->user_get($character_id);
			if ($user == null) {
				if ($auth_manager->is_member($alliance_id, $corporation_id)) {
					//$auth_group = $config_app['auth']['role_member'];
					$is_member = true;
				} elseif ($auth_manager->is_blue($alliance_id, $corporation_id)) {
					//$auth_group = $config_app['auth']['role_blue'];
					$is_member = false;
				} else {
					return $response->withRedirect('/fuckedup');
				}

				$auth_manager->auth_user_add($character_id, $character_esi, $config_app['auth']['default_admins']);
				$user = $db_client->user_get($character_id);
				//$group = $db_client->groups_getby_name($auth_group);
				//$db_client->usergroups_add($user['id'], $group['id']);

				$corporation_esi = $esi_client->corporation_get_details($corporation_id);
				//$user_groups = $db_client->usergroups_getby_user($user['id']);
				//$member_group = $db_client->groups_getby_name($config_app['auth']['role_member']);
				//$blue_group = $db_client->groups_getby_name($config_app['auth']['role_blue']);
				//$group_old = null;
				$corp_name = corp_group_name($corporation_esi['ticker']);
				$corp_group = $db_client->groups_getby_name($corp_name);

				$auth_manager->auth_role_check($user['id'], $is_member);
				$auth_manager->corp_role_check($user['id'], null, $corp_group, $is_member);
			}

			$citadel_session = $db_client->session_get($user['id']);
			if (isset($citadel_session['session_key'])) {
				if (strtotime($citadel_session['expire_date']) <= time()) {
					$db_client->session_delete($citadel_session['session_key']);
				}
			}

			$session_key = uniqidReal(40);
			$expire =  time()+60*60*24;
			$expire_timestamp = date("Y-m-d H:i:s", $expire);
			$response = FigResponseCookies::set($response, SetCookie::create('session_key')
				->withValue($session_key)
				->withExpires($expire)
				//->withMaxAge($expire)
				//->rememberForever()
			);

			$db_client->session_add($user['id'], $session_key, $expire_timestamp);

			unset($_SESSION['eve_state'], $esi_client, $db_client, $auth_manager);

			$_SESSION['session_key'] = $session_key;

			return $response->withRedirect('/dashboard/refresh');
		} else {
			unset($db_client);
			return $response->withRedirect('/login');
		}
	} elseif ($state == ($_SESSION['eve_state'].'contacts')) {
		$scope = 'esi-alliances.read_contacts.v1';
		$expire_date =  time()+19*60;
		$expire_date = date("Y-m-d H:i:s", $expire_date);
		$contacts_token = $db_client->custom_get('contacts_token');
		if (!isset($contacts_token)) {
			$token_data = $db_client->token_get($_SESSION['user_id'], $scope);
			if ($token_data == null) {
				$db_client->token_add($_SESSION['character_id'], $access_token, $refresh_token, $scope, $expire_date);
				$db_client->custom_add('contacts_token', $_SESSION['character_id']);
			} else {
				$db_client->token_updatefull($_SESSION['character_id'], $access_token, $refresh_token, $scope, $expire_date);
			}
		}
		unset($_SESSION['eve_state'], $db_client);
		return $response->withRedirect('/dashboard/refresh');
	} else {
		unset($db_client);
		return $response->withRedirect('/login');
	}
});

$app->get('/discord/callback', function (Request $request, Response $response) use ($config_app, $config_discord) {

	$code = $request->getParam("code");
	$state = $request->getParam("state");

	if ($state == $_SESSION['discord_state']) {
		$discordOAuthProvider = new \Discord\OAuth\Discord([
			'clientId' => $config_discord["client_id"],
			'clientSecret' => $config_discord["secret_key"],
			'redirectUri' => $config_discord["callback_url"]
		]);

		$token = $discordOAuthProvider->getAccessToken('authorization_code', [
			'code' => $code,
		]);

		$user = $discordOAuthProvider->getResourceOwner($token);
		$discord_id = $user->id;

		$discord_client = new DiscordCitadelClient();

		if (isset($_SESSION['character_info']) && isset($_SESSION['corporation_info'])) {
			$db_client = new citadelDB();
			$auth_manager = new AuthManager($db_client);
			$corporation_id = $_SESSION['character_info']['corporation_id'];
			$alliance_id = $_SESSION['character_info']['alliance_id'];

			if ($auth_manager->is_member($alliance_id, $corporation_id)) {
				$auth_group = $config_app['auth']['role_member'];
			} elseif ($auth_manager->is_blue($alliance_id, $corporation_id)) {
				$auth_group = $config_app['auth']['role_blue'];
			} else {
				return $response->withRedirect('/fuckedup');
			}

			if ($config_app['auth']['set_name_enforce']) {
				$discord_nick = $_SESSION['character_info']['name'];
				if ($config_app['auth']['set_corp_ticker']) {
					$discord_nick = "[".$_SESSION['corporation_info']['ticker']."] ".$discord_nick;
				}
			}

			$discord_roles = $discord_client->make_key_name();
			$roles_to_add = array();
			$roles_to_add[] = $discord_roles[$auth_group];

			$discord_client->user_add($discord_id, $token, $discord_nick, $roles_to_add);
			$db_client->discord_add($_SESSION['user_id'], $discord_id);

			unset($_SESSION['discord_state'], $discord_client, $db_client, $auth_manager);
			return $response->withRedirect('/dashboard/refresh');
		} else {
			return $response->withRedirect('/dashboard/refresh');
		}
	} else {
		unset($_SESSION['discord_state']);
		return $response->withRedirect('/dashboard/refresh');
	}
});

$app->get('/discord/deactivate', function (Request $request, Response $response) {

	$db_client = new citadelDB();
	$discord_client = new DiscordCitadelClient();
	$discord_client->user_del($_SESSION['discord_id']);
	$db_client->discord_delete($_SESSION['user_id']);
	
	unset($_SESSION['discord_id'], $discord_client, $db_client);

    return $response->withRedirect('/dashboard/refresh');
});

$app->get('/teamspeak/activate', function (Request $request, Response $response) use ($config_app) {

	if (isset($_SESSION['character_id']) && isset($_SESSION['character_info'])) {
		$corporation_id = $_SESSION['character_info']['corporation_id'];
		$alliance_id = $_SESSION['character_info']['alliance_id'];

		$db_client = new citadelDB();
		$auth_manager = new AuthManager($db_client);
		$ts_client = new ts3client();

		if ($auth_manager->is_member($alliance_id, $corporation_id)) {
			$auth_role = $config_app['auth']['role_member'];
		} elseif ($auth_manager->is_blue($alliance_id, $corporation_id)) {
			$auth_role = $config_app['auth']['role_blue'];
		} else {
			return $response->withRedirect('/fuckedup');
		}

		$auth_role_id = $ts_client->group_get_byname($auth_role);

		$ts_user = $ts_client->user_add($_SESSION['character_id'], $_SESSION['character_info']['name'], $auth_role_id);
		$db_client->teamspeak_add($_SESSION['user_id'], $ts_user['token']);

		unset($ts_client, $db_client, $auth_manager);
		return $response->withRedirect('/dashboard/refresh');
	} else {
		return $response->withRedirect('/dashboard/refresh');
	}
});

$app->get('/teamspeak/deactivate', function (Request $request, Response $response) {

	if (isset($_SESSION['character_id'])) {
		$db_client = new citadelDB();
		$ts_client = new ts3client();

		$ts_token = $db_client->teamspeak_get_token($_SESSION['user_id']);
		$ts_client->user_del($_SESSION['character_id'], $ts_token);
		$db_client->teamspeak_delete($_SESSION['user_id']);
		unset($_SESSION['teamspeak_data'], $ts_client, $db_client);

		return $response->withRedirect('/dashboard/refresh');
	} else {
		return $response->withRedirect('/dashboard/refresh');
	}
});

$app->get('/phpbb3/activate', function (Request $request, Response $response) use ($config_app) {

	if (isset($_SESSION['character_id']) && isset($_SESSION['character_info'])) {
		$corporation_id = $_SESSION['character_info']['corporation_id'];
		$alliance_id = $_SESSION['character_info']['alliance_id'];

		$db_client = new citadelDB();
		$auth_manager = new AuthManager($db_client);
		$phpbb3_client = new phpBB3client();

		$_SESSION['phpbb3_password'] = password_generate();
		$pwhash = password_hash($_SESSION['phpbb3_password'], PASSWORD_DEFAULT);
		$_SESSION['phpbb3_username'] = $phpbb3_client->sanitize_username($_SESSION['character_info']['name']);
		$user_email = $_SESSION['phpbb3_username']."@".$config_app['phpbb3']['email_prefix'];

		if ($phpbb3_client->check_user($_SESSION['phpbb3_username'])) {
			$phpbb3_client->user_update($_SESSION['phpbb3_username'], $user_email, $pwhash);
			$phpbb3_client->user_activate($_SESSION['phpbb3_username']);
		} else {
			$regdate = time();
			$phpbb3_client->user_add(
				$_SESSION['character_info']['name'],
				$_SESSION['phpbb3_username'],
				$pwhash,
				$user_email,
				2,
				$regdate
			);

			if ($auth_manager->is_member($alliance_id, $corporation_id)) {
				$auth_role = $phpbb3_client->sanitize_groupname($config_app['auth']['role_member']);
			} elseif ($auth_manager->is_blue($alliance_id, $corporation_id)) {
				$auth_role = $phpbb3_client->sanitize_groupname($config_app['auth']['role_blue']);
			} else {
				return $response->withRedirect('/fuckedup');
			}

			$group_id = $phpbb3_client->group_get_id($auth_role);
			$phpbb3_client->user_group_add($_SESSION['phpbb3_username'], $group_id, 0);

			//if ($_SESSION['is_admin']) {
			//	$admin_group_id = $phpbb3_client->group_get_id("ADMINISTRATORS");
			//	$phpbb3_client->user_group_add($_SESSION['phpbb3_username'], $admin_group_id, 0);
			//}

			$phpbb3_client->user_avatar_set($_SESSION['phpbb3_username'], $_SESSION['character_id']);
			$phpbb3_client->user_permissions_clear($_SESSION['phpbb3_username']);
		}

		$db_client->phpbb3_add($_SESSION['user_id'], $_SESSION['phpbb3_username']);

		unset($phpbb3_client, $db_client, $auth_manager);

		return $this->view->render($response, 'dashboard_phpbb3.html', [
			'portal_config' => $config_app['portal'],
			'character_id' => $_SESSION['character_id'],
			'character_name' => $_SESSION['character_info']['name'],
			'phpbb3_username' => $_SESSION['phpbb3_username'],
			'phpbb3_password' => $_SESSION['phpbb3_password'],
			'is_admin' => $_SESSION['is_admin'],
		]);

	} else {
		return $response->withRedirect('/dashboard/refresh');
	}
});

$app->get('/phpbb3/deactivate', function (Request $request, Response $response) {

	if (isset($_SESSION['phpbb3_username']) && isset($_SESSION['user_id'])) {
		$db_client = new citadelDB();
		$phpbb3_client = new phpBB3client();

		$fake_password = password_generate();
		$user_email = "revoke_".uniqid()."@localhost";

		$pwhash = password_hash($fake_password, PASSWORD_DEFAULT);
		$phpbb3_client->user_update($_SESSION['phpbb3_username'], $user_email, $pwhash);
		$phpbb3_client->user_sessions_del($_SESSION['phpbb3_username']);
		$phpbb3_client->user_autologin_del($_SESSION['phpbb3_username']);
		$phpbb3_client->user_deactivate($_SESSION['phpbb3_username']);
		//$phpbb3_client->user_del($_SESSION['phpbb3_username']);
		$db_client->phpbb3_delete($_SESSION['user_id']);

		unset($_SESSION['phpbb3_username'], $phpbb3_client, $db_client);
		return $response->withRedirect('/dashboard/refresh');

	} else {
		return $response->withRedirect('/dashboard/refresh');
	}

});

//$app->get('/eveonline/callback', function (Request $request, Response $response, $args) use ($config_app) {
//$app->get('/callback-sso', function (Request $request, Response $response, $args) use ($config_app) {
$app->get('/test', function (Request $request, Response $response) {
    // Sample log message
    //$this->logger->info("Slim-Skeleton '/' route");

	//$response = $this->renderer->render($response, 'header.phtml');
	//$response = $this->renderer->render($response, 'index.phtml');
	//$response = $this->renderer->render($response, 'footer.phtml');

    // Render index view
    return $this->renderer->render($response, 'test/index.phtml');

	//$url = $this->router->pathFor('dashboard',[], $user);
	//return $response->withRedirect($url);

	//return $response;
});