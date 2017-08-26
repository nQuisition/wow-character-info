CREATE TABLE `character` (
	`id` int NOT NULL AUTO_INCREMENT,
	`name` varchar(128) NOT NULL,
	`realm` varchar(128) NOT NULL,
	`region` varchar(32) NOT NULL,
	`class` int NOT NULL,
	`race` int NOT NULL,
	`gender` int NOT NULL,
	`thumbnail` varchar(255) NOT NULL,
	`achievementPoints` int NOT NULL,
	`lastModified` bigint NOT NULL,
	`activeSpec` int NOT NULL,
	`ilvl` int NOT NULL,
	`ilvle` int NOT NULL,
	`lastUpdated` bigint NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `class` (
	`id` int NOT NULL,
  `wlid` int NOT NULL,
	`name` varchar(32) NOT NULL,
	`color` varchar(32) NOT NULL,
	`icon` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `race` (
	`id` int NOT NULL,
	`name` varchar(32) NOT NULL,
	`maleIcon` varchar(255) NOT NULL,
	`femaleIcon` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `talent` (
	`id` int NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`tier` int NOT NULL,
	`column` int NOT NULL,
	`spellid` int NOT NULL,
	`icon` varchar(255) NOT NULL,
	`spec` int NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `spec` (
	`id` int NOT NULL AUTO_INCREMENT,
	`class` int NOT NULL,
	`name` varchar(32) NOT NULL,
	`role` varchar(32) NOT NULL,
	`order` int NOT NULL,
	`backgroundImage` varchar(255) NOT NULL,
	`icon` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `character_talent` (
	`character` int NOT NULL,
	`talent` int NOT NULL
);

CREATE TABLE `item` (
	`id` int NOT NULL,
	`name` varchar(255) NOT NULL,
	`icon` varchar(255) NOT NULL,
	`slot` int NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `character_item` (
	`id` int NOT NULL AUTO_INCREMENT,
	`character` int NOT NULL,
	`item` int NOT NULL,
	`quality` int NOT NULL,
	`ilvl` int NOT NULL,
	`setList` varchar(128) DEFAULT NULL,
	`transmogItem` int DEFAULT NULL,
	`bonusList` varchar(128) DEFAULT NULL,
	`enchant` int DEFAULT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `itemslots` (
	`id` int NOT NULL,
	`name` varchar(64) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `itemquality` (
	`id` int NOT NULL,
	`name` varchar(32) NOT NULL,
	`color` varchar(32) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `character_item_gem` (
	`character_item` int NOT NULL,
	`gemid` int NOT NULL
);

CREATE TABLE `character_item_traits` (
	`character_item` int NOT NULL,
	`traitid` int NOT NULL,
	`rank` int NOT NULL
);

CREATE TABLE `character_item_relic` (
	`character_item` int NOT NULL,
	`relicid` int NOT NULL,
	`bonusList` varchar(128) DEFAULT NULL
);

ALTER TABLE `character` ADD CONSTRAINT `character_fk0` FOREIGN KEY (`class`) REFERENCES `class`(`id`);

ALTER TABLE `character` ADD CONSTRAINT `character_fk1` FOREIGN KEY (`race`) REFERENCES `race`(`id`);

ALTER TABLE `character` ADD CONSTRAINT `character_fk2` FOREIGN KEY (`activeSpec`) REFERENCES `spec`(`id`);

ALTER TABLE `character` ADD UNIQUE `character_unique` (`name`, `realm`, `region`);

ALTER TABLE `talent` ADD CONSTRAINT `talent_fk0` FOREIGN KEY (`spec`) REFERENCES `spec`(`id`);

ALTER TABLE `talent` ADD UNIQUE `talent_unique` (`spec`, `tier`, `column`);

ALTER TABLE `spec` ADD CONSTRAINT `spec_fk0` FOREIGN KEY (`class`) REFERENCES `class`(`id`);

ALTER TABLE `spec` ADD UNIQUE `spec_unique` (`class`, `order`);

ALTER TABLE `character_talent` ADD CONSTRAINT `character_talent_fk0` FOREIGN KEY (`character`) REFERENCES `character`(`id`) ON DELETE CASCADE;

ALTER TABLE `character_talent` ADD CONSTRAINT `character_talent_fk1` FOREIGN KEY (`talent`) REFERENCES `talent`(`id`) ON DELETE CASCADE;

ALTER TABLE `item` ADD CONSTRAINT `item_fk0` FOREIGN KEY (`slot`) REFERENCES `itemslots`(`id`);

ALTER TABLE `character_item` ADD CONSTRAINT `character_item_fk0` FOREIGN KEY (`character`) REFERENCES `character`(`id`) ON DELETE CASCADE;

ALTER TABLE `character_item` ADD CONSTRAINT `character_item_fk1` FOREIGN KEY (`item`) REFERENCES `item`(`id`) ON DELETE CASCADE;

ALTER TABLE `character_item` ADD CONSTRAINT `character_item_fk2` FOREIGN KEY (`quality`) REFERENCES `itemquality`(`id`);

ALTER TABLE `character_item_gem` ADD CONSTRAINT `character_item_gem_fk0` FOREIGN KEY (`character_item`) REFERENCES `character_item`(`id`) ON DELETE CASCADE;

ALTER TABLE `character_item_traits` ADD CONSTRAINT `character_item_traits_fk0` FOREIGN KEY (`character_item`) REFERENCES `character_item`(`id`) ON DELETE CASCADE;

ALTER TABLE `character_item_traits` ADD UNIQUE (`character_item`, `traitid`);

ALTER TABLE `character_item_relic` ADD CONSTRAINT `character_item_relic_fk0` FOREIGN KEY (`character_item`) REFERENCES `character_item`(`id`) ON DELETE CASCADE;
