
-- --------------------------------------------------------

--
-- Table structure for table `byond_oauth_tokens`
--

CREATE TABLE IF NOT EXISTS `byond_oauth_tokens` (
  `token` char(128) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `key` varchar(32) NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
