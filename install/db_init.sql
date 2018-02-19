-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `citadel_users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`character_id` varchar(128) NOT NULL,
	`is_admin` varchar(10) NOT NULL DEFAULT 'no',
	`added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `character_id_unique` (`character_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(128) NOT NULL,
	`discord_enabled` varchar(10) NOT NULL DEFAULT 'no',
	`teamspeak_enabled` varchar(10) NOT NULL DEFAULT 'no',
	`phpbb3_enabled` varchar(10) NOT NULL DEFAULT 'no',
	PRIMARY KEY (`id`),
	UNIQUE KEY `group_name_unique` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_users_group` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`group_id` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_citadel_users_group_citadel_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_citadel_users_group_citadel_users_idx`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	INDEX `fk_citadel_users_group_citadel_groups_idx` (`group_id` ASC),
	CONSTRAINT `fk_citadel_users_group_citadel_groups_idx`
		FOREIGN KEY (`group_id`)
		REFERENCES `citadel_groups` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_session` (
	`user_id` int(11) NOT NULL,
	`session_key` varchar(40) NULL,
	`expire` timestamp NULL,
	PRIMARY KEY (`session_key`),
	INDEX `fk_citadel_session_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_citadel_session_users_idx`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `discord_users` (
	`user_id` int(11) NOT NULL,
	`discord_id` varchar(30) NULL,
	PRIMARY KEY (`user_id`),
	INDEX `fk_discord_users_citadel_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_discord_users_citadel_users_idx`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `teamspeak_users` (
	`user_id` int(11) NOT NULL,
	`token` varchar(50) NULL,
	PRIMARY KEY (`user_id`),
	INDEX `fk_teamspeak_users_citadel_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_teamspeak_users_citadel_users_idx`
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
	INDEX `fk_phpbb3_users_citadel_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_phpbb3_users_citadel_users_idx`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_cache_alliances` (
	`id` int(11) NOT NULL,
	`name` varchar(50) NULL,
	`ticker` varchar(50) NULL,
	`is_blue` varchar(50) NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_cache_corporations` (
	`id` int(11) NOT NULL,
	`name` varchar(50) NULL,
	`ticker` varchar(50) NULL,
	`is_blue` varchar(50) NULL,
	`alliance_id` int(11) NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_alliances_cache_corporations_cache_idx` (`alliance_id` ASC),
	CONSTRAINT `fk_alliances_cache_corporations_cache_idx`
		FOREIGN KEY (`alliance_id`)
		REFERENCES `citadel_cache_alliances` (`id`)
		ON DELETE SET NULL
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_cache_characters` (
	`user_id` int(11) NOT NULL,
	`name` varchar(128) NULL,
	`is_blue` varchar(50) NULL,
	`corporation_id` int(11) NULL,
	PRIMARY KEY (`user_id`),
	INDEX `fk_characters_cache_citadel_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_characters_cache_citadel_users_idx`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	INDEX `fk_corporations_cache_characters_cache_idx` (`corporation_id` ASC),
	CONSTRAINT `fk_corporations_cache_characters_cache_idx`
		FOREIGN KEY (`corporation_id`)
		REFERENCES `citadel_cache_corporations` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_tokens_storage` (
	`user_id` int(11) NOT NULL,
	`token_access` varchar(255) DEFAULT NULL,
	`token_refresh` varchar(255) DEFAULT NULL,
	`updated` timestamp NULL DEFAULT NULL,
	`added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`user_id`),
	INDEX `fk_citadel_tokens_storage_citadel_users_idx` (`user_id` ASC),
	CONSTRAINT `fk_citadel_tokens_storage_citadel_users_idx`
		FOREIGN KEY (`user_id`)
		REFERENCES `citadel_users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citadel_custom_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_key` varchar(191) NOT NULL,
  `custom_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_key_unique` (`custom_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;