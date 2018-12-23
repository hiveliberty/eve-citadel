<?php

// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;
use Slim\Http\Request;
use Slim\Http\Response;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

require_once(__DIR__ . '/../lib/db.class.php');
require_once(__DIR__ . '/../lib/cURL.php');
require_once(__DIR__ . '/../lib/other.php');
require_once(__DIR__ . '/../lib/auth.class.php');
require_once(__DIR__ . '/../lib/esi.class.php');
require_once(__DIR__ . '/../lib/ts3.class.php');
require_once(__DIR__ . '/../lib/discord.class.php');
require_once(__DIR__ . '/../lib/phpbb3.class.php');

class CallbackManager {
	
	private $token_url = "https://login.eveonline.com/oauth/token";
	private $verify_url = "https://login.eveonline.com/oauth/verify";
	
	function __construct($db_client = null) {
		// $output = "[%datetime%] %channel%.%level_name%: %message%\n";
		// $formatter = new LineFormatter($output);
		// $this->logger = new Logger('callback_mgr');
		// $log_handler_file = new StreamHandler(__DIR__ . '/../logs/callbacks.log', Logger::WARNING);
		// $log_handler_file->setFormatter($formatter);
		// $this->logger->pushHandler($log_handler_file);

		if ($db_client == null) {
			$this->db = new citadelDB();
		} else {
			$this->db = $db_client;
		}

		$this->app_conf = require __DIR__ . '/../config/app.php';
		$this->discord_conf = require __DIR__ . '/../config/discord.php';
		$this->callback_url = $this->app_conf['portal']['portal_url']."/callback";
	}

    function __destruct() {
		unset($this->db);
    }

	function sso_code_proc($code) {
		$base64 = base64_encode($this->app_conf["sso"]["clientID"] . ":" . $this->app_conf["sso"]["secretKey"]);
		$data = json_decode(sendData($this->token_url, array(
			"grant_type" => "authorization_code",
			"code" => $code
		), array("Authorization: Basic {$base64}")));

		return $data;
	}

	function user_login($request, $response) {
		$code = $request->getParam("code");
		$sso_data = $this->sso_code_proc($code);
		$access_token = $sso_data->access_token;
		$refresh_token = $sso_data->refresh_token;

		$login_data = json_decode(sendData($this->verify_url, array(), array("Authorization: Bearer {$access_token}")));
		$character_id = $login_data->CharacterID;

		if (isset($character_id)) {
			$esi_client = new ESIClient();
			$auth_manager = new AuthManager($this->db);
			$character_esi = $esi_client->character_get_details($character_id);
			$corporation_id = $character_esi['corporation_id'];
			$alliance_id = $character_esi['alliance_id'];
			$user = $this->db->user_get($character_id);
			if ($user == null) {
				if ($auth_manager->is_member($alliance_id, $corporation_id)) {
					$is_member = true;
				} elseif ($auth_manager->is_blue($alliance_id, $corporation_id)) {
					$is_member = false;
				} else {
					return $response->withRedirect('/');
				}

				$auth_manager->auth_user_add($character_id, $character_esi, $this->app_conf['auth']['default_admins']);
				$user = $this->db->user_get($character_id);

				$corporation_esi = $esi_client->corporation_get_details($corporation_id);
				$corp_name = corp_group_name($corporation_esi['ticker']);
				$corp_group = $this->db->groups_getby_name($corp_name);

				$auth_manager->auth_role_check($user['id'], $is_member);
				$auth_manager->corp_role_check($user['id'], null, $corp_group, $is_member);
			}

			$citadel_session = $this->db->session_get($user['id']);
			if (isset($citadel_session['session_key'])) {
				if (strtotime($citadel_session['expire_date']) <= time()) {
					$this->db->session_delete($citadel_session['session_key']);
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

			$this->db->session_add($user['id'], $session_key, $expire_timestamp);

			unset($esi_client, $auth_manager);
		}
		
		return $response;
	}

	function discord_activate($request, $response) {

		$code = $request->getParam("code");

		$discordOAuthProvider = new \Discord\OAuth\Discord([
			'clientId' => $this->discord_conf["client_id"],
			'clientSecret' => $this->discord_conf["secret_key"],
			'redirectUri' => $this->callback_url
		]);

		$token = $discordOAuthProvider->getAccessToken('authorization_code', [
			'code' => $code,
		]);

		$user = $discordOAuthProvider->getResourceOwner($token);
		$discord_id = $user->id;

		if (isset($_SESSION['character_info']) && isset($_SESSION['corporation_info'])) {
			$auth_manager = new AuthManager($this->db);
			$discord_client = new DiscordCitadelClient();
			$corporation_id = $_SESSION['character_info']['corporation_id'];
			$alliance_id = $_SESSION['character_info']['alliance_id'];

			if ($auth_manager->is_member($alliance_id, $corporation_id)) {
				$auth_group = $this->app_conf['auth']['role_member'];
			} elseif ($auth_manager->is_blue($alliance_id, $corporation_id)) {
				$auth_group = $this->app_conf['auth']['role_blue'];
			}

			if ($this->app_conf['auth']['set_name_enforce']) {
				$discord_nick = $_SESSION['character_info']['name'];
				if ($this->app_conf['auth']['set_corp_ticker']) {
					$discord_nick = "[".$_SESSION['corporation_info']['ticker']."] ".$discord_nick;
				}
			}

			$roles_to_add = array();
			if (isset($auth_group) && $auth_group != null) {
				$discord_roles = $discord_client->make_key_name();
				$roles_to_add[] = $discord_roles[$auth_group];
			}

			if ($discord_client->user_exist($discord_id)) {
				$discord_client->user_nick_set($discord_id, $discord_nick);
				foreach ($roles_to_add as $role) {
					$discord_client->user_role_add($discord_id, $role);
				}
			} else {
				$discord_client->user_add($discord_id, $token, $discord_nick, $roles_to_add);
			}

			$this->db->discord_add($_SESSION['user_id'], $discord_id);
			if ($this->db->discord_member_exist($discord_id)) {
				$this->db->discord_member_authorized_set($discord_id);
			}

			unset($auth_manager,$discord_client);
		}

		return $response;
	}
}