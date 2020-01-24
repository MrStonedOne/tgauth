
-- --------------------------------------------------------

--
-- Table structure for table `oauth_sessions`
--

CREATE TABLE IF NOT EXISTS `oauth_sessions` (
  `site_private_token` varbinary(255) NOT NULL,
  `session_public_token` binary(64) NOT NULL,
  `session_private_token` binary(64) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `return_uri` text NOT NULL,
  `phpbb_user_id` mediumint(9) DEFAULT NULL,
  `phpbb_password` varchar(40) DEFAULT NULL,
  `last_access` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp
) ;
