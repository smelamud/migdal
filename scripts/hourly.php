<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/sessions.php');
require_once('lib/tmptexts.php');
require_once('lib/redirs.php');
require_once('lib/counters.php');
require_once('lib/votes.php');
require_once('lib/users.php');
require_once('lib/mail.php');
require_once('lib/postings.php');
require_once('lib/captcha.php');

dbOpen();
session(getShamesId());

deleteExpiredCounterIPs();
deleteNonConfirmedUsers();
deleteExpiredVotes();
deleteClosedSessions();
deleteObsoleteTmpTexts();
deleteObsoleteRedirs();
deleteObsoleteCaptchas();
purgeMailLog();

autoEnablePostings();
rotateAllCounters();

dbClose();
?>
