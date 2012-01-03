CREATE TABLE `migration_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_version` VARCHAR(20) NOT NULL,
  `to_version` VARCHAR(20) NOT NULL,
  `time` DATETIME NOT NULL,
  `status` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;