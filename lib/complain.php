<?php
# @(#) $Id$

require_once('lib/grps.php');
require_once('lib/messages.php');

class Complain
      extends Message
{

function Complain($row)
{
$this->grp=GRP_COMPLAIN;
$this->Message($row);
}

function hasTopic()
{
return false;
}

function hasImage()
{
return false;
}

}

function getComplainById($id)
{
$result=mysql_query("select complains.id as id,body,subject
		     from complains
		          left join messages
			       on messages.id=complains.message_id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке жалобы');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                              : array());
}

?>
