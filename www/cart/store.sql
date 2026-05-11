-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2021-09-30 06:33:51
-- 伺服器版本： 10.4.20-MariaDB
-- PHP 版本： 8.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- --------------------------------------------------------

--
-- 資料表結構 `product_list`
--

CREATE TABLE `product_list` (
  `book_no` varchar(20) NOT NULL DEFAULT '',
  `book_name` varchar(30) NOT NULL DEFAULT '',
  `price` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 傾印資料表的資料 `product_list`
--

INSERT INTO `product_list` (`book_no`, `book_name`, `price`) VALUES
('EE0018', '最新計算機概論', 560),
('EE0069', '網路概論', 550),
('EL0063', 'ASP.NET網頁程式設計', 580),
('EN0010', 'HTML5網頁程式設計', 500),
('P766', 'PHP&MySQL網站開發', 580),
('P816', 'Python程式設計', 500),
('P818', 'Visual C#程式設計', 500);

--
-- 已傾印資料表的索引
--


