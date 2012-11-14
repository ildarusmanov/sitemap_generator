-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Ноя 14 2012 г., 21:10
-- Версия сервера: 5.5.24
-- Версия PHP: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `mapgen`
--

-- --------------------------------------------------------

--
-- Структура таблицы `links`
--

CREATE TABLE IF NOT EXISTS `links` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `level` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `links`
--

INSERT INTO `links` (`id`, `link`, `level`) VALUES
(1, 'http://hubnets.ru/node/1', '0.1'),
(2, 'http://hubnets.ru/node/2', '0.1');

