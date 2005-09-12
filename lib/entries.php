<?php
require_once('lib/dataobject.php');

define('ENT_NULL',0);
define('ENT_POSTING',1);
define('ENT_FORUM',2);
define('ENT_TOPIC',3);
define('ENT_IMAGE',4);
define('ENT_COMPLAIN',5);
define('ENT_VERSION',6);

class Entry
      extends DataObject
{
var $id;
var $user_id;
var $group_id;
var $perms;

function Entry($row)
{
parent::parent($row);
}

function getId()
{
return $this->id;
}

function getUserId()
{
return $this->user_id;
}

function getGroupId()
{
return $this->group_id;
}

function getPerms()
{
return $this->perms;
}

}
?>
