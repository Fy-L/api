DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user`(
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
    `username` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '用户名',
    `password` CHAR(32) NOT NULL DEFAULT '' COMMENT '密码',
    `created_at` DATETIME,
    PRIMARY KEY(`user_id`)    
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article`(
    `article_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章id',
    `title` VARCHAR(40) NOT NULL DEFAULT '' COMMENT '标题',
    `content` TEXT  COMMENT '内容',
    `user_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY(`article_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
