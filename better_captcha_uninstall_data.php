<?php
/** Better CAPTCHA destructive data cleanup. @version 0.1.0 */
if (!defined('SMF'))
	die('No direct access...');

global $smcFunc, $sourcedir;
require_once($sourcedir . '/Subs.php');

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable LIKE {string:prefix}',
	array('prefix' => 'better\\_captcha\\_%')
);
