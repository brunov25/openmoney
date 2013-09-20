Recommended Database Changes:

1.rename id's of each table to {table name}_id 
2.id's use BIGINT(22) in mysql especially for user_journal table
	int/integer: 4 bytes, 0 - 4,294,967,295 (unsigned)
	bigint: 8 bytes, 0 - 18,446,744,073,709,551,615 (unsigned)
3.currency name field in currency table
4.user_journal include the id of the trading account rather than the trading name (would save lookup) and could join with user_account_currencies table.
5.user_journal tid not unique (two entries that have the same time)
6.user_journal indexes on trading_account and with_account and user_id
7.user_journal description longtext max size is 4gb but limitation is max packet size ~(32MB default) of mysql client http://stackoverflow.com/questions/4294506/maximum-length-for-longtext-field 

example user_journal table. Table could be updated using update table syntax.

DROP TABLE IF EXISTS `user_journal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_journal` (
  `user_journal_id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
  `tid` bigint(22) unsigned DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `description` longtext,
  `trading_account_id` bigint(22) NOT NULL,
  `with_account_id` bigint(22) NOT NULL,
  `currency_id` bigint(22) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `balance` decimal(11,2) NOT NULL,
  `trading` decimal(20,2) NOT NULL DEFAULT '0.00',
  `flags` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX (`user_id`),
  INDEX (`trading_account_id`),
  INDEX (`with_account_id`)
) ENGINE=INNODB AUTO_INCREMENT=577 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

