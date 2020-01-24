
-- --------------------------------------------------------

--
-- Table structure for table `oauth_tokens`
--

CREATE TABLE IF NOT EXISTS `oauth_tokens` (
  `site` binary(64) NOT NULL,
  `token` binary(64) NOT NULL,
  `phpbb_userid` mediumint(9) NOT NULL,
  `byond_key` varchar(30) NOT NULL,
  `password_hash` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `spent` tinyint(1) NOT NULL DEFAULT 0,
  `last_access` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`site`,`token`),
  KEY `phpbb_userid` (`phpbb_userid`),
  KEY `password_hash` (`password_hash`),
  KEY `spent` (`spent`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
