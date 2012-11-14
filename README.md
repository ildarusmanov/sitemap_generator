sitemap_generator
=================

sitemap.xml generator

Setup
=================
1. Change config.php to your database settings and your web-site url

2. Import dump.sql in PhpMyAdmin or create table `links` with SQL:

CREATE TABLE IF NOT EXISTS `links` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `level` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

3. Start generator

in your browser http://sitename/generator_dir/index.php 

or with console: 

cd generator_dir

php index.php

