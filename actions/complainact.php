<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/complains.php');
require_once('lib/complainactions.php');
require_once('lib/messages.php');
require_once('lib/utils.php');

function executeAction($action,$complain_id)
{
global $userId;

if($action->getId()==0)
  return EECA_NO_ACTION;
$complain=getComplainInfoById($complain_id);
if($complain->getId()==0)
  return EECA_NO_COMPLAIN;
if($complain->getRecipientId()!=$userId)
  return EECA_NO_EXEC;
$forum=new Forum(array('body' => $action->getText(),
		       'up'   => $complain->getMessageId()));
if(!$forum->store())
  return EECA_SQL_FORUM;
if($action->getOpcode()!=0)
  {
  $result=mysql_query('select sql
		       from complain_statements
		       where opcode='.$action->getOpcode().
		     ' order by sql_index');
  if(!$result)
    return EECA_SQL_STATEMENTS;
  while($stat=mysql_fetch_row($result))
       {
       $result=mysql_query(subParams($stat[0],
			   array('complain_id' => $complain_id,
				 'link'        => $complain->getLink())));
       if(!$result)
	 return EECA_SQL_EXEC;
       }
  }
return EECA_OK;
}

settype($actid,'integer');
settype($complain_id,'integer');

dbOpen();
session($sessionid);
$action=getComplainActionById($actid);
$action->setup($HTTP_POST_VARS);
$err=executeAction($action,$complain_id);
header('Location: '.($err==EECA_OK ? remakeURI($redir,array('err'))
                                   : remakeURI($redir,
				               array(),
				  	       array('err' => $err))));
dbClose();
?>
