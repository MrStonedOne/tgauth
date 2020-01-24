
-- --------------------------------------------------------

--
-- Dumping data for table `profile_fields`
--

INSERT IGNORE INTO `profile_fields` (`field_name`, `field_type`, `field_ident`, `field_length`, `field_minlen`, `field_maxlen`, `field_novalue`, `field_default_value`, `field_validation`, `field_required`, `field_show_novalue`, `field_show_on_reg`, `field_show_on_vt`, `field_show_profile`, `field_hide`, `field_no_view`, `field_active`, `field_order`) VALUES
('byond_username', 2, 'byond_username', '10', '0', '40', '', '', '.*', 0, 0, 0, 1, 0, 0, 0, 1, 1),
('github', 2, 'github', '20', '0', '40', '', '', '.*', 0, 0, 0, 1, 0, 0, 0, 1, 2),
('reddit', 2, 'reddit', '10', '0', '80', '', '', '[\\w]+', 0, 0, 0, 1, 0, 1, 0, 1, 3);
