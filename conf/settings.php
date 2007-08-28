<?php
# @(#) $Id$

define('SETL_USER',0);
define('SETL_HOST',1);

$allSettings=array(
	'style' => array(
		'default' => 1,
		'abbrev' => 'st',
		'type' => 'integer',
		'location' => SETL_USER
	),
	'biffTime' => array(
		'default' => 24,
		'abbrev' => 'bt',
		'type' => 'integer',
		'location' => SETL_USER
	),
	'forumPortion' => array(
		'default' => 10,
		'abbrev' => 'fp',
		'type' => 'integer',
		'location' => SETL_USER
	),
	'pictureRowPortion' => array(
		'default' => 4,
		'abbrev' => 'prp',
		'type' => 'integer',
		'location' => SETL_USER
	),
	'pictureColumnPortion' => array(
		'default' => 5,
		'abbrev' => 'pcp',
		'type' => 'integer',
		'location' => SETL_USER
	),
	'guestLogin' => array(
		'default' => '',
		'abbrev' => 'gl',
		'type' => 'string',
		'location' => SETL_HOST
	),
	'loginHint' => array(
		'default' => '',
		'abbrev' => 'log',
		'type' => 'string',
		'location' => SETL_HOST
	),
	'myComputerHint' => array(
		'default' => 1,
		'abbrev' => 'my',
		'type' => 'integer',
		'location' => SETL_HOST
	),
	'willBeModeratedNote' => array(
		'default' => 1,
		'abbrev' => 'wbm',
		'type' => 'integer',
		'location' => SETL_USER
	),
);

?>
