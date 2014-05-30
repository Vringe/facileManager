<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2013 The facileManager Team                               |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | facileManager: Easy System Administration                               |
 | fmDNS: Easily manage one or more ISC BIND servers                       |
 +-------------------------------------------------------------------------+
 | http://www.facilemanager.com/modules/fmdns/                             |
 +-------------------------------------------------------------------------+
*/

function upgradefmDNSSchema($module) {
	global $fmdb;
	
	/** Include module variables */
	@include(dirname(__FILE__) . '/variables.inc.php');
	
	/** Get current version */
	$running_version = getOption('version', 0, $module);
	
	/** Checks to support older versions (ie n-3 upgrade scenarios */
	$success = version_compare($running_version, '1.2.4', '<') ? upgradefmDNS_124($__FM_CONFIG, $running_version) : true;
	if (!$success) return $fmdb->last_error;
	
	setOption('client_version', $__FM_CONFIG['fmDNS']['client_version'], 'auto', false, 0, 'fmDNS');
		
	return true;
}

/** 1.0-b5 */
function upgradefmDNS_100($__FM_CONFIG) {
	global $fmdb;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` CHANGE  `record_ttl`  `record_ttl` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';";
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-b7 */
function upgradefmDNS_101($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-b5', '<') ? upgradefmDNS_100($__FM_CONFIG) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_type`  `server_type` ENUM(  'bind9' ) NOT NULL DEFAULT  'bind9',
CHANGE  `server_run_as`  `server_run_as` VARCHAR( 50 ) NULL DEFAULT NULL,
CHANGE  `server_run_as_predefined`  `server_run_as_predefined` ENUM(  'named',  'bind',  'daemon',  'as defined:' ) NOT NULL DEFAULT  'named' ;";
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-b10 */
function upgradefmDNS_102($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-b7', '<') ? upgradefmDNS_101($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_run_as_predefined`  `server_run_as_predefined` ENUM(  'named',  'bind',  'daemon',  'root',  'wheel', 'as defined:' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'named';";
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-b11 */
function upgradefmDNS_103($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-b10', '<') ? upgradefmDNS_102($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` ADD  `def_multiple_values` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'no',
ADD  `def_view_support` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'no';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` CHANGE  `def_type`  `def_type` VARCHAR( 200 ) NOT NULL ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` DROP  `def_id` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` ADD UNIQUE (`def_option`);";
	
	$inserts[] = "INSERT IGNORE INTO  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` (
`def_function` ,
`def_option` ,
`def_type` ,
`def_multiple_values` ,
`def_view_support`
)
VALUES 
('options',  'avoid-v4-udp-ports',  '( port )',  'yes',  'no'), 
('options',  'avoid-v6-udp-ports',  '( port )',  'yes',  'no'),
('options',  'blackhole',  '( address_match_element )',  'yes',  'no'),
('options',  'coresize',  '( size_in_bytes )',  'no',  'no'),
('options',  'datasize',  '( size_in_bytes )',  'no',  'no'),
('options',  'dump-file',  '( quoted_string )',  'no',  'no'),
('options',  'files',  '( size_in_bytes )',  'no',  'no'),
('options',  'heartbeat-interval',  '( integer )',  'no',  'no'),
('options',  'hostname',  '( quoted_string | none )',  'no',  'no'),
('options',  'interface-interval',  '( integer )',  'no',  'no'),
('options',  'listen-on',  '( address_match_element )',  'yes',  'no'),
('options',  'listen-on-v6',  '( address_match_element )',  'yes',  'no'),
('options',  'match-mapped-addresses',  '( yes | no )',  'no',  'no'),
('options',  'memstatistics-file',  '( quoted_string )',  'no',  'no'),
('options',  'pid-file',  '( quoted_string | none )',  'no',  'no'),
('options',  'port',  '( integer )',  'no',  'no'),
('options',  'querylog',  '( yes | no )',  'no',  'no'),
('options',  'recursing-file',  '( quoted_string )',  'no',  'no'),
('options',  'random-device',  '( quoted_string )',  'no',  'no'),
('options',  'recursive-clients',  '( integer )',  'no',  'no'),
('options',  'serial-query-rate',  '( integer )',  'no',  'no'),
('options',  'server-id',  '( quoted_string | none )',  'no',  'no'),
('options',  'stacksize',  '( size_in_bytes )',  'no',  'no'),
('options',  'statistics-file',  '( quoted_string )',  'no',  'no'),
('options',  'tcp-clients',  '( integer )',  'no',  'no'),
('options',  'tcp-listen-queue',  '( integer )',  'no',  'no'),
('options',  'transfers-per-ns',  '( integer )',  'no',  'no'),
('options',  'transfers-in',  '( integer )',  'no',  'no'),
('options',  'transfers-out',  '( integer )',  'no',  'no'),
('options',  'use-ixfr',  '( yes | no )',  'no',  'no'),
('options',  'version',  '( quoted_string | none )',  'no',  'no'),

('options',  'allow-recursion',  '( address_match_element )',  'yes',  'yes'),
('options',  'sortlist',  '( address_match_element )',  'yes',  'yes'),
('options',  'auth-nxdomain',  '( yes | no )',  'no',  'yes'),
('options',  'minimal-responses',  '( yes | no )',  'no',  'yes'),
('options',  'recursion',  '( yes | no )',  'no',  'yes'),
('options',  'provide-ixfr',  '( yes | no )',  'no',  'yes'),
('options',  'request-ixfr',  '( yes | no )',  'no',  'yes'),
('options',  'additional-from-auth',  '( yes | no )',  'no',  'yes'),
('options',  'additional-from-cache',  '( yes | no )',  'no',  'yes'),
('options',  'query-source',  'address ( ipv4_address | * ) [ port ( ip_port | * ) ]',  'no',  'yes'),
('options',  'query-source-v6',  'address ( ipv6_address | * ) [ port ( ip_port | * ) ]',  'no',  'yes'),
('options',  'cleaning-interval',  '( integer )',  'no',  'yes'),
('options',  'lame-ttl',  '( seconds )',  'no',  'yes'),
('options',  'max-ncache-ttl',  '( seconds )',  'no',  'yes'),
('options',  'max-cache-ttl',  '( seconds )',  'no',  'yes'),
('options',  'transfer-format',  '( many-answers | one-answer )',  'no',  'yes'),
('options',  'max-cache-size',  '( size_in_bytes )',  'no',  'yes'),
('options',  'check-names',  '( master | slave | response) ( warn | fail | ignore )',  'no',  'yes'),
('options',  'cache-file',  '( quoted_string )',  'no',  'yes'),
('options',  'preferred-glue',  '( A | AAAA )',  'no',  'yes'),
('options',  'edns-udp-size',  '( size_in_bytes )',  'no',  'yes'),
('options',  'dnssec-enable',  '( yes | no )',  'no',  'yes'),
('options',  'dnssec-lookaside',  'domain trust-anchor domain',  'no',  'yes'),
('options',  'dnssec-must-be-secure',  'domain ( yes | no )',  'no',  'yes'),
('options',  'dialup',  '( yes | no | notify | refresh | passive | notify-passive )',  'no',  'yes'),
('options',  'ixfr-from-differences',  '( yes | no )',  'no',  'yes'),
('options',  'allow-query',  '( address_match_element )',  'yes',  'yes'),
('options',  'allow-transfer',  '( address_match_element )',  'yes',  'yes'),
('options',  'allow-update-forwarding',  '( address_match_element )',  'yes',  'yes'),
('options',  'notify',  '( yes | no | explicit )',  'no',  'yes'),
('options',  'notify-source',  '( ipv4_address | * )',  'no',  'yes'),
('options',  'notify-source-v6',  '( ipv6_address | * )',  'no',  'yes'),
('options',  'also-notify',  '( ipv4_address | ipv6_address )',  'yes',  'yes'),
('options',  'allow-notify',  '( address_match_element )',  'yes',  'yes'),
('options',  'forward',  '( first | only )',  'no',  'yes'),
('options',  'forwarders',  '( ipv4_address | ipv6_address )',  'yes',  'yes'),
('options',  'max-journal-size',  '( size_in_bytes )',  'no',  'yes'),
('options',  'max-transfer-time-in',  '( minutes )',  'no',  'yes'),
('options',  'max-transfer-time-out',  '( minutes )',  'no',  'yes'),
('options',  'max-transfer-idle-in',  '( minutes )',  'no',  'yes'),
('options',  'max-transfer-idle-out',  '( minutes )',  'no',  'yes'),
('options',  'max-retry-time',  '( seconds )',  'no',  'yes'),
('options',  'min-retry-time',  '( seconds )',  'no',  'yes'),
('options',  'max-refresh-time',  '( seconds )',  'no',  'yes'),
('options',  'min-refresh-time',  '( seconds )',  'no',  'yes'),
('options',  'multi-master',  '( yes | no )',  'no',  'yes'),
('options',  'sig-validity-interval',  '( integer )',  'no',  'yes'),
('options',  'transfer-source',  '( ipv4_address | * )',  'no',  'yes'),
('options',  'transfer-source-v6',  '( ipv6_address | * )',  'no',  'yes'),
('options',  'alt-transfer-source',  '( ipv4_address | * )',  'no',  'yes'),
('options',  'alt-transfer-source-v6',  '( ipv6_address | * )',  'no',  'yes'),
('options',  'use-alt-transfer-source',  '( yes | no )',  'no',  'yes'),
('options',  'zone-statistics',  '( yes | no )',  'no',  'yes'),
('options',  'key-directory',  '( quoted_string )',  'no',  'yes')
;";
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $query) {
			$fmdb->query($query);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-b13 */
function upgradefmDNS_104($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-b11', '<') ? upgradefmDNS_103($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` CHANGE  `record_type`  `record_type` ENUM(  'A',  'AAAA',  'CNAME',  'TXT',  'MX',  'PTR',  'SRV',  'NS' ) NOT NULL DEFAULT  'A' ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` ENGINE = INNODB;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}soa` ENGINE = INNODB;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}track_builds` ENGINE = INNODB;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}track_reloads` ENGINE = INNODB;";
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-b14 */
function upgradefmDNS_105($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-b13', '<') ? upgradefmDNS_104($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}domains` CHANGE  `domain_name`  `domain_name` VARCHAR( 255 ) NOT NULL DEFAULT  '';";
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-rc2 */
function upgradefmDNS_106($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-b14', '<') ? upgradefmDNS_105($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = <<<TABLE
CREATE TABLE IF NOT EXISTS `fm_{$__FM_CONFIG['fmDNS']['prefix']}options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
TABLE;

	$inserts[] = "INSERT IGNORE INTO  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` (
`def_function` ,
`def_option` ,
`def_type` ,
`def_multiple_values` ,
`def_view_support`
)
VALUES 
('options',  'match-clients',  '( address_match_element )',  'yes',  'yes'),
('options',  'match-destinations',  '( address_match_element )',  'yes',  'yes'),
('options',  'match-recursive-only',  '( yes | no )',  'no',  'yes')
";	


	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-rc3 */
function upgradefmDNS_107($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-rc2', '<') ? upgradefmDNS_106($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` ADD  `server_update_port` INT( 5 ) NOT NULL DEFAULT  '0' AFTER  `server_update_method` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` ADD  `server_os` VARCHAR( 50 ) DEFAULT NULL AFTER  `server_name` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_run_as`  `server_run_as` VARCHAR( 50 ) NULL ;";

	$updates[] = "UPDATE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` SET  `server_update_port` =  '80' WHERE  `server_update_method` = 'http';";
	$updates[] = "UPDATE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` SET  `server_update_port` =  '443' WHERE  `server_update_method` = 'https';";


	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($updates) && $updates[0]) {
		foreach ($updates as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0-rc6 */
function upgradefmDNS_108($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-rc3', '<') ? upgradefmDNS_107($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_os`  `server_os_distro` VARCHAR( 50 ) NULL DEFAULT NULL ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` ADD  `server_os` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `server_name` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_run_as_predefined`  `server_run_as_predefined` ENUM(  'named',  'bind',  'daemon',  'as defined:' ) NOT NULL DEFAULT  'named';";


	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0 */
function upgradefmDNS_109($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0-rc6', '<') ? upgradefmDNS_108($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` ADD  `def_dropdown` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'no';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_update_method`  `server_update_method` ENUM(  'http',  'https',  'cron',  'ssh' ) NOT NULL DEFAULT  'http';";

	$updates[] = "UPDATE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` SET  `def_dropdown` =  'yes' WHERE  `def_option` IN ('match-mapped-addresses','transfer-format','check-names','preferred-glue','dialup','notify','forward');";
	$updates[] = "UPDATE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` SET  `def_dropdown` =  'yes' WHERE  `def_type` =  '( yes | no )';";
	$updates[] = "UPDATE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` SET  `def_type` =  '( master | slave | response ) ( warn | fail | ignore )' WHERE `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions`.`def_option` =  'check-names';";
	$updates[] = "UPDATE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` SET  `def_type` =  '( port )' WHERE  `def_option` =  'port';";

	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($updates) && $updates[0]) {
		foreach ($updates as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.0.1 */
function upgradefmDNS_110($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0', '<') ? upgradefmDNS_109($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$fmdb->query("SELECT * FROM `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions`");
	$table[] = ($fmdb->num_rows) ? null : "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` ADD  `def_dropdown` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'no';";

	$inserts[] = <<<INSERT
INSERT IGNORE INTO  `fm_{$__FM_CONFIG['fmDNS']['prefix']}functions` (
`def_function` ,
`def_option` ,
`def_type` ,
`def_multiple_values` ,
`def_view_support`,
`def_dropdown`
)
VALUES 
('key', 'algorithm', 'string', 'no', 'no', 'no'),
('key', 'secret', 'quoted_string', 'no', 'no', 'no'),
('options', 'avoid-v4-udp-ports', '( port )', 'yes', 'no', 'no'), 
('options', 'avoid-v6-udp-ports', '( port )', 'yes', 'no', 'no'),
('options', 'blackhole', '( address_match_element )', 'yes', 'no', 'no'),
('options', 'coresize', '( size_in_bytes )', 'no', 'no', 'no'),
('options', 'datasize', '( size_in_bytes )', 'no', 'no', 'no'),
('options', 'dump-file', '( quoted_string )', 'no', 'no', 'no'),
('options', 'files', '( size_in_bytes )', 'no', 'no', 'no'),
('options', 'heartbeat-interval', '( integer )', 'no', 'no', 'no'),
('options', 'hostname', '( quoted_string | none )', 'no', 'no', 'no'),
('options', 'interface-interval', '( integer )', 'no', 'no', 'no'),
('options', 'listen-on', '( address_match_element )', 'yes', 'no', 'no'),
('options', 'listen-on-v6', '( address_match_element )', 'yes', 'no', 'no'),
('options', 'match-mapped-addresses', '( yes | no )', 'no', 'no', 'yes'),
('options', 'memstatistics-file', '( quoted_string )', 'no', 'no', 'no'),
('options', 'pid-file', '( quoted_string | none )', 'no', 'no', 'no'),
('options', 'port', '( port )', 'no', 'no', 'no'),
('options', 'querylog', '( yes | no )', 'no', 'no', 'yes'),
('options', 'recursing-file', '( quoted_string )', 'no', 'no', 'no'),
('options', 'random-device', '( quoted_string )', 'no', 'no', 'no'),
('options', 'recursive-clients', '( integer )', 'no', 'no', 'no'),
('options', 'serial-query-rate', '( integer )', 'no', 'no', 'no'),
('options', 'server-id', '( quoted_string | none )', 'no', 'no', 'no'),
('options', 'stacksize', '( size_in_bytes )', 'no', 'no', 'no'),
('options', 'statistics-file', '( quoted_string )', 'no', 'no', 'no'),
('options', 'tcp-clients', '( integer )', 'no', 'no', 'no'),
('options', 'tcp-listen-queue', '( integer )', 'no', 'no', 'no'),
('options', 'transfers-per-ns', '( integer )', 'no', 'no', 'no'),
('options', 'transfers-in', '( integer )', 'no', 'no', 'no'),
('options', 'transfers-out', '( integer )', 'no', 'no', 'no'),
('options', 'use-ixfr', '( yes | no )', 'no', 'no', 'yes'),
('options', 'version', '( quoted_string | none )', 'no', 'no', 'no'),

('options', 'allow-recursion', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'sortlist', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'auth-nxdomain', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'minimal-responses', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'recursion', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'provide-ixfr', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'request-ixfr', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'additional-from-auth', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'additional-from-cache', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'query-source', 'address ( ipv4_address | * ) [ port ( ip_port | * ) ]', 'no', 'yes', 'no'),
('options', 'query-source-v6', 'address ( ipv6_address | * ) [ port ( ip_port | * ) ]', 'no', 'yes', 'no'),
('options', 'cleaning-interval', '( integer )', 'no', 'yes', 'no'),
('options', 'lame-ttl', '( seconds )', 'no', 'yes', 'no'),
('options', 'max-ncache-ttl', '( seconds )', 'no', 'yes', 'no'),
('options', 'max-cache-ttl', '( seconds )', 'no', 'yes', 'no'),
('options', 'transfer-format', '( many-answers | one-answer )', 'no', 'yes', 'yes'),
('options', 'max-cache-size', '( size_in_bytes )', 'no', 'yes', 'no'),
('options', 'check-names', '( master | slave | response ) ( warn | fail | ignore )', 'no', 'yes', 'yes'),
('options', 'cache-file', '( quoted_string )', 'no', 'yes', 'no'),
('options', 'preferred-glue', '( A | AAAA )', 'no', 'yes', 'yes'),
('options', 'edns-udp-size', '( size_in_bytes )', 'no', 'yes', 'no'),
('options', 'dnssec-enable', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'dnssec-lookaside', 'domain trust-anchor domain', 'no', 'yes', 'no'),
('options', 'dnssec-must-be-secure', 'domain ( yes | no )', 'no', 'yes', 'no'),
('options', 'dialup', '( yes | no | notify | refresh | passive | notify-passive )', 'no', 'yes', 'yes'),
('options', 'ixfr-from-differences', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'allow-query', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'allow-transfer', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'allow-update-forwarding', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'notify', '( yes | no | explicit )', 'no', 'yes', 'yes'),
('options', 'notify-source', '( ipv4_address | * )', 'no', 'yes', 'no'),
('options', 'notify-source-v6', '( ipv6_address | * )', 'no', 'yes', 'no'),
('options', 'also-notify', '( ipv4_address | ipv6_address )', 'yes', 'yes', 'no'),
('options', 'allow-notify', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'forward', '( first | only )', 'no', 'yes', 'yes'),
('options', 'forwarders', '( ipv4_address | ipv6_address )', 'yes', 'yes', 'no'),
('options', 'max-journal-size', '( size_in_bytes )', 'no', 'yes', 'no'),
('options', 'max-transfer-time-in', '( minutes )', 'no', 'yes', 'no'),
('options', 'max-transfer-time-out', '( minutes )', 'no', 'yes', 'no'),
('options', 'max-transfer-idle-in', '( minutes )', 'no', 'yes', 'no'),
('options', 'max-transfer-idle-out', '( minutes )', 'no', 'yes', 'no'),
('options', 'max-retry-time', '( seconds )', 'no', 'yes', 'no'),
('options', 'min-retry-time', '( seconds )', 'no', 'yes', 'no'),
('options', 'max-refresh-time', '( seconds )', 'no', 'yes', 'no'),
('options', 'min-refresh-time', '( seconds )', 'no', 'yes', 'no'),
('options', 'multi-master', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'sig-validity-interval', '( integer )', 'no', 'yes', 'no'),
('options', 'transfer-source', '( ipv4_address | * )', 'no', 'yes', 'no'),
('options', 'transfer-source-v6', '( ipv6_address | * )', 'no', 'yes', 'no'),
('options', 'alt-transfer-source', '( ipv4_address | * )', 'no', 'yes', 'no'),
('options', 'alt-transfer-source-v6', '( ipv6_address | * )', 'no', 'yes', 'no'),
('options', 'use-alt-transfer-source', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'zone-statistics', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'key-directory', '( quoted_string )', 'no', 'yes', 'no'),
('options', 'match-clients', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'match-destinations', '( address_match_element )', 'yes', 'yes', 'no'),
('options', 'match-recursive-only', '( yes | no )', 'no', 'yes', 'yes'),
('options', 'dnssec-validation', '( yes | no | auto )', 'no', 'yes', 'yes'),
('options', 'bindkeys-file', '( quoted_string )', 'no', 'yes', 'no')
;
INSERT;

	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	return true;
}

/** 1.1 */
function upgradefmDNS_111($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.0.1', '<') ? upgradefmDNS_110($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}acls` ADD  `acl_comment` TEXT NULL AFTER  `acl_addresses` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}config` ADD  `cfg_comment` TEXT NULL AFTER  `cfg_data` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}keys` ADD  `key_comment` TEXT NULL AFTER  `key_view` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}views` ADD  `view_comment` TEXT NULL AFTER  `view_name` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}domains` CHANGE  `domain_type`  `domain_type` ENUM(  'master',  'slave',  'forward',  'stub' ) NOT NULL DEFAULT  'master';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` CHANGE  `server_update_config`  `server_update_config` ENUM(  'yes',  'no',  'conf' ) NOT NULL DEFAULT  'no';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}servers` ADD  `server_client_version` VARCHAR( 150 ) NULL AFTER  `server_installed` ;";

	$inserts = $updates = null;
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($updates) && $updates[0]) {
		foreach ($updates as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (!setOption('fmDNS_client_version', $__FM_CONFIG['fmDNS']['client_version'], 'auto', false)) return false;
		
	return true;
}

/** 1.2-beta1 */
function upgradefmDNS_1201($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.1', '<') ? upgradefmDNS_111($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
//	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` CHANGE  `record_type`  `record_type` ENUM( 'A',  'AAAA',  'CERT',  'CNAME',  'DHCID',  'DLV',  'DNAME',  'DNSKEY', 'DS',  'HIP',  'IPSECKEY',  'KEY',  'KX',  'MX',  'NAPTR',  'NS',  'NSEC',  'NSEC3',  'NSEC3PARAM',  'PTR',  'RSIG',  'RP',  'SIG',  'SRV',  'SSHFP',  'TA',  'TKEY', 'TLSA',  'TSIG',  'TXT', 'HINFO' ) NOT NULL DEFAULT  'A';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` CHANGE  `record_type`  `record_type` ENUM( 'A',  'AAAA',  'CERT',  'CNAME',  'DNAME',  'DNSKEY', 'KEY',  'KX',  'MX',  'NS',  'PTR',  'RP',  'SRV',  'TXT', 'HINFO' ) NOT NULL DEFAULT  'A';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` CHANGE  `record_class`  `record_class` ENUM(  'IN',  'CH',  'HS' ) NOT NULL DEFAULT  'IN';";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` ADD  `record_os` VARCHAR( 255 ) NULL AFTER  `record_port`,
ADD  `record_cert_type` TINYINT NULL AFTER  `record_os` ,
ADD  `record_key_tag` INT NULL AFTER  `record_cert_type` ,
ADD  `record_algorithm` TINYINT NULL AFTER  `record_key_tag`,
ADD  `record_flags` ENUM(  '0',  '256',  '257' ) NULL AFTER  `record_algorithm`,
ADD  `record_text` VARCHAR( 255 ) NULL AFTER  `record_flags` ;";
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` CHANGE  `record_value`  `record_value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
	$table[] = <<<TABLE
CREATE TABLE IF NOT EXISTS `fm_{$__FM_CONFIG['fmDNS']['prefix']}records_skipped` (
  `account_id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `record_status` enum('active','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
TABLE;

	$inserts = $updates = null;
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($updates) && $updates[0]) {
		foreach ($updates as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}
	
	/** Force rebuild of server configs for Issue #75 */
	$current_module = $_SESSION['module'];
	$_SESSION['module'] = 'fmDNS';
	setBuildUpdateConfigFlag(null, 'yes', 'build', $__FM_CONFIG);
	$_SESSION['module'] = $current_module;
	unset($current_module);
	
	/** Move module options */
	$fmdb->get_results("SELECT * FROM `fm_{$__FM_CONFIG['fmDNS']['prefix']}options`");
	if ($fmdb->num_rows) {
		$count = $fmdb->num_rows;
		$result = $fmdb->last_result;
		for ($i=0; $i<$count; $i++) {
			if (!setOption($result[$i]->option_name, $result[$i]->option_value, 'auto', true, $result[$i]->account_id, 'fmDNS')) return false;
		}
	}
	$fmdb->query("DROP TABLE `fm_{$__FM_CONFIG['fmDNS']['prefix']}options`");
	if (!$fmdb->result || $fmdb->sql_errors) return false;
	
	$fm_user_caps = getOption('fm_user_caps');
	
	/** Update user capabilities */
	$fm_user_caps['fmDNS'] = array(
			'read_only'				=> '<b>Read Only</b>',
			'manage_servers'		=> 'Server Management',
			'build_server_configs'	=> 'Build Server Configs',
			'manage_zones'			=> 'Zone Management',
			'manage_records'		=> 'Record Management',
			'reload_zones'			=> 'Reload Zones',
			'manage_settings'		=> 'Manage Settings'
		);
	if (!setOption('fm_user_caps', $fm_user_caps)) return false;
	
	$fmdb->get_results("SELECT * FROM `fm_users`");
	if ($fmdb->num_rows) {
		$count = $fmdb->num_rows;
		$result = $fmdb->last_result;
		for ($i=0; $i<$count; $i++) {
			$user_caps = null;
			/** Update user capabilities */
			$j = 1;
			$temp_caps = null;
			foreach ($fm_user_caps['fmDNS'] as $slug => $trash) {
				$user_caps = isSerialized($result[$i]->user_caps) ? unserialize($result[$i]->user_caps) : $result[$i]->user_caps;
				if (@array_key_exists('fmDNS', $user_caps)) {
					if ($user_caps['fmDNS']['imported_perms'] == 0) {
						$temp_caps['fmDNS']['read_only'] = 1;
					} else {
						if ($j & $user_caps['fmDNS']['imported_perms'] && $j > 1) $temp_caps['fmDNS'][$slug] = 1;
						$j = $j*2 ;
					}
				} else {
					$temp_caps['fmDNS']['read_only'] = $user_caps['fmDNS']['read_only'] = 1;
				}
			}
			if (@array_key_exists('fmDNS', $temp_caps)) $user_caps['fmDNS'] = array_merge($temp_caps['fmDNS'], $user_caps['fmDNS']);
			if (@array_key_exists('zone_access', $user_caps['fmDNS'])) $user_caps['fmDNS']['access_specific_zones'] = $user_caps['fmDNS']['zone_access'];
			unset($user_caps['fmDNS']['imported_perms'], $user_caps['fmDNS']['zone_access']);
			
			$fmdb->query("UPDATE fm_users SET user_caps = '" . serialize($user_caps) . "' WHERE user_id=" . $result[$i]->user_id);
			if (!$fmdb->result) return false;
		}
	}

	setOption('client_version', $__FM_CONFIG['fmDNS']['client_version'], 'auto', false, 0, 'fmDNS');
		
	return true;
}


/** 1.2-rc1 */
function upgradefmDNS_1202($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.2-beta1', '<') ? upgradefmDNS_1201($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$fm_user_caps = getOption('fm_user_caps');
	
	/** Update user capabilities */
	$fm_user_caps['fmDNS'] = array(
			'view_all'				=> 'View All',
			'manage_servers'		=> 'Server Management',
			'build_server_configs'	=> 'Build Server Configs',
			'manage_zones'			=> 'Zone Management',
			'manage_records'		=> 'Record Management',
			'reload_zones'			=> 'Reload Zones',
			'manage_settings'		=> 'Manage Settings'
		);
	if (!setOption('fm_user_caps', $fm_user_caps)) return false;
	
	$fmdb->get_results("SELECT * FROM `fm_users`");
	if ($fmdb->num_rows) {
		$count = $fmdb->num_rows;
		$result = $fmdb->last_result;
		for ($i=0; $i<$count; $i++) {
			$user_caps = null;
			/** Update user capabilities */
			$temp_caps = null;
			foreach ($fm_user_caps['fmDNS'] as $slug => $trash) {
				$user_caps = isSerialized($result[$i]->user_caps) ? unserialize($result[$i]->user_caps) : $result[$i]->user_caps;
				if (@array_key_exists('fmDNS', $user_caps)) {
					if (array_key_exists('read_only', $user_caps['fmDNS'])) {
						$temp_caps['fmDNS']['view_all'] = 1;
						unset($user_caps['fmDNS']['read_only']);
					}
				}
			}
			if (@array_key_exists('fmDNS', $temp_caps)) $user_caps['fmDNS'] = array_merge($temp_caps['fmDNS'], $user_caps['fmDNS']);
			$fmdb->query("UPDATE fm_users SET user_caps = '" . serialize($user_caps) . "' WHERE user_id=" . $result[$i]->user_id);
			if (!$fmdb->result) return false;
		}
	}
	
	setOption('client_version', $__FM_CONFIG['fmDNS']['client_version'], 'auto', false, 0, 'fmDNS');
		
	return true;
}


/** 1.2.3 */
function upgradefmDNS_123($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.2-rc1', '<') ? upgradefmDNS_1202($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records_skipped` DROP PRIMARY KEY;";

	$inserts = $updates = null;
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($updates) && $updates[0]) {
		foreach ($updates as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}
		
	return true;
}


/** 1.2.4 */
function upgradefmDNS_124($__FM_CONFIG, $running_version) {
	global $fmdb;
	
	$success = version_compare($running_version, '1.2.3', '<') ? upgradefmDNS_123($__FM_CONFIG, $running_version) : true;
	if (!$success) return false;
	
	$table[] = "ALTER TABLE  `fm_{$__FM_CONFIG['fmDNS']['prefix']}records` ADD INDEX (  `domain_id` ) ;";

	$inserts = $updates = null;
	
	/** Create table schema */
	if (count($table) && $table[0]) {
		foreach ($table as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($inserts) && $inserts[0]) {
		foreach ($inserts as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}

	if (count($updates) && $updates[0]) {
		foreach ($updates as $schema) {
			$fmdb->query($schema);
			if (!$fmdb->result || $fmdb->sql_errors) return false;
		}
	}
		
	return true;
}


?>