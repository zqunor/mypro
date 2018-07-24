CREATE DATABASE IF NOT EXISTS test;

CREATE TABLE IF NOT EXISTS `message` (
  `id`          INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `title`       VARCHAR(40),
  `content`     TEXT,
  `create_time` INT
)
  ENGINE = InnoDB, CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS `comment` (
  `id`          INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `mes_id`      TEXT,
  `username`    VARCHAR(20),
  `content`     TEXT,
  `create_time` INT
)
  ENGINE = InnoDB, CHARACTER SET utf8;