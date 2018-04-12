/**
 * Author:  Derek
 * Created: 2-Mar-2018
 */

CREATE TABLE `glove`.`gv_user` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(64) NOT NULL,
  `password` VARCHAR(45) NULL,
  `reg_time` DATETIME NOT NULL,
  `last_time` DATETIME NOT NULL,
  PRIMARY KEY (`user_id`));

CREATE TABLE `glove`.`gv_money` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `user_name` VARCHAR(64) NOT NULL,
  `amount` DECIMAL(13,2) NOT NULL,
  `req_time` DATETIME NOT NULL,
  `status` INT NOT NULL,
  PRIMARY KEY (`id`));
