<?php
# @(#) $Id$

require_once('lib/usertag.php');

class SenderTag
      extends UserTag
{
var $sender_id;
var $sender_hidden;

function SenderTag($row)
{
$this->UserTag($row);
}

function getSenderId()
{
return $this->sender_id;
}

function isSenderHidden()
{
return $this->sender_hidden ? 1 : 0;
}

function isSenderAdminHidden()
{
return $this->sender_hidden>1 ? 1 : 0;
}

function isSenderVisible()
{
global $userAdminUsers;

return !$this->isSenderHidden()
       || ($userAdminUsers && !$this->isSenderAdminHidden());
}

}
?>
