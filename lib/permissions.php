<?php
# @(#) $Id$

define('PB_USER',0);
define('PB_GROUP',4);
define('PB_OTHER',8);
define('PB_GUEST',12);

define('PERM_READ',1);
define('PERM_WRITE',2);
define('PERM_APPEND',4);
define('PERM_POST',8);

define('PERM_UR',0x0001);
define('PERM_UW',0x0002);
define('PERM_UA',0x0004);
define('PERM_UP',0x0008);
define('PERM_GR',0x0010);
define('PERM_GW',0x0020);
define('PERM_GA',0x0040);
define('PERM_GP',0x0080);
define('PERM_OR',0x0100);
define('PERM_OW',0x0200);
define('PERM_OA',0x0400);
define('PERM_OP',0x0800);
define('PERM_ER',0x1000);
define('PERM_EW',0x2000);
define('PERM_EA',0x4000);
define('PERM_EP',0x8000);
?>
