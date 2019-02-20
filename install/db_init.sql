-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `callback_pending` (
	`pending_id` int(11) NOT NULL AUTO_INCREMENT,
	`pending_key` varchar(191) NOT NULL,
	`pending_action` varchar(255) NOT NULL,
	`pending_subaction` varchar(255) NULL,
	`pending_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`pending_id`),
	UNIQUE KEY `uk_callback_pending_pending_key` (`pending_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `custom_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_key` varchar(191) NOT NULL,
  `custom_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_key_unique` (`custom_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `config_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_type` varchar(30) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key_unique` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `discord_queue_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` varchar(64) NOT NULL,
  `message` varchar(2048) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(128) NOT NULL,
	`discord_enabled` tinyint(1) NOT NULL DEFAULT 0,
	`teamspeak_enabled` tinyint(1) NOT NULL DEFAULT 0,
	`phpbb3_enabled` tinyint(1) NOT NULL DEFAULT 0,
	`discord_hoist` tinyint(1) NOT NULL DEFAULT 0,
	`color` int(11) NOT NULL DEFAULT 0,
	`hidden` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `group_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `eve_alliance_info` (
	`id` int(11) NOT NULL,
	`name` varchar(50) NOT NULL,
	`ticker` varchar(10) NOT NULL,
	`blue` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `eve_corporation_info` (
	`id` int(11) NOT NULL,
	`name` varchar(50) NOT NULL,
	`ticker` varchar(10) NOT NULL,
	`blue` tinyint(1) NOT NULL DEFAULT 0,
	`alliance_id` int(11) NULL,
	PRIMARY KEY (`id`),
	INDEX `ix_alliance_id` (`alliance_id` ASC),
	CONSTRAINT `fk_corporations_info&alliance_info`
		FOREIGN KEY (`alliance_id`)
		REFERENCES `eve_alliance_info` (`id`)
		ON DELETE SET NULL
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `eve_character_info` (
	`id` int(11) NOT NULL,
	`name` varchar(128) NOT NULL,
	`corporation_id` int(11) NULL,
	`alliance_id` int(11) NULL,
	PRIMARY KEY (`id`),
	INDEX `ix_corporation_id` (`corporation_id` ASC),
	CONSTRAINT `fk_character_info&corporations_info`
		FOREIGN KEY (`corporation_id`)
		REFERENCES `eve_corporation_info` (`id`)
		ON DELETE SET NULL
		ON UPDATE SET NULL,
	INDEX `ix_alliance_id` (`alliance_id` ASC),
	CONSTRAINT `fk_character_info&alliance_info`
		FOREIGN KEY (`alliance_id`)
		REFERENCES `eve_alliance_info` (`id`)
		ON DELETE SET NULL
		ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`character_id` int(11) NOT NULL,
	`is_active` tinyint(1) NOT NULL DEFAULT 1,
	`is_admin` tinyint(1) NOT NULL DEFAULT 0,
	`added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `ix_character_id` (`character_id` ASC),
	CONSTRAINT `fk_citadel_users&character_info`
		FOREIGN KEY (`character_id`)
		REFERENCES `eve_character_info` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_user_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`group_id` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `ix_user_id` (`user_id` ASC),
	CONSTRAINT `fk_citadel_users_group&citadel_users`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	INDEX `ix_group_id` (`group_id` ASC),
	CONSTRAINT `fk_citadel_users_group&citadel_groups`
		FOREIGN KEY (`group_id`)
		REFERENCES `citadel_groups` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_session_keys` (
	`user_id` int(11) NOT NULL,
	`session_key` varchar(40) NOT NULL,
	`expire_date` timestamp NOT NULL,
	PRIMARY KEY (`session_key`),
	INDEX `ix_user_id` (`user_id` ASC),
	CONSTRAINT `fk_citadel_session_keys&citadel_users`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `esi_tokens` (
	`token_id` int(11) NOT NULL AUTO_INCREMENT,
	`character_id` int(11) NOT NULL,
	`access_token` varchar(255) NULL,
	`refresh_token` varchar(255) NULL,
	`scope_name` varchar(255) NULL,
	`expire_date` timestamp NULL DEFAULT NULL,
	`update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`token_id`),
	INDEX `ix_character_id` (`character_id` ASC),
	CONSTRAINT `fk_esi_tokens&citadel_users`
		FOREIGN KEY (`character_id`)
		REFERENCES `eve_character_info` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `esi_token_types` (
	`type_id` int(11) NOT NULL AUTO_INCREMENT,
	`token_id` int(11) NOT NULL,
	`token_type` varchar(255) NULL,
	PRIMARY KEY (`type_id`),
	INDEX `ix_token_id` (`token_id` ASC),
	CONSTRAINT `fk_esi_token_types&esi_tokens`
		FOREIGN KEY (`token_id`)
		REFERENCES `esi_tokens` (`token_id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `discord_members` (
	`discord_id` varchar(30) NOT NULL,
	`discord_username` varchar(128) NULL,
	`is_bot` tinyint(1) NOT NULL DEFAULT 0,
	`is_authorized` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`discord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `discord_users` (
	`user_id` int(11) NOT NULL,
	`discord_id` varchar(30) NULL,
	PRIMARY KEY (`user_id`),
	UNIQUE KEY `discord_id_unique` (`discord_id`),
	INDEX `ix_user_id` (`user_id` ASC),
	CONSTRAINT `fk_discord_users&citadel_users`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `teamspeak_users` (
	`user_id` int(11) NOT NULL,
	`token` varchar(50) NULL,
	PRIMARY KEY (`user_id`),
	INDEX `ix_user_id` (`user_id` ASC),
	CONSTRAINT `fk_teamspeak_users&citadel_users`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `phpbb3_users` (
	`user_id` int(11) NOT NULL,
	`username` varchar(50) NULL,
	`password` varchar(50) NULL,
	PRIMARY KEY (`user_id`),
	UNIQUE KEY `username_unique` (`username`),
	INDEX `ix_user_id` (`user_id` ASC),
	CONSTRAINT `fk_phpbb3_users&citadel_users`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;