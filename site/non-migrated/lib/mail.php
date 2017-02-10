<?php
# @(#) $Id$

require_once('conf/migdal.conf');
require_once('conf/mailings.php');

require_once('lib/errors.php');
require_once('lib/sql.php');
require_once('lib/users.php');
require_once('lib/mail-format.php');

function lockMailTables()
{
sql('lock tables mail_queue write,mail_log write,users read,profiling write',
    __FUNCTION__);
}

function unlockMailTables()
{
sql('unlock tables',
    __FUNCTION__);
}

function postMailTo($user_id,$template,$params)
{
debugLog(LL_FUNCTIONS,'postMailTo(user_id=%,template=%,params=%)',
         array($user_id,$template,$params));
return postMailToUser(getUserById($user_id),$template,$params);
}

function postMailToAdmins($right,$template,$params)
{
debugLog(LL_FUNCTIONS,'postMailToAdmins(right=%,template=%,params=%)',
         array($right,$template,$params));
$iter=new UserListIterator('',SORT_LOGIN,$right);
foreach($iter as $user)
       {
       $result=postMailToUser($user,$template,$params);
       if($result!=EG_OK)
         return $result;
       }
return EG_OK;
}

function postMailToUser($user,$template,$params)
{
global $forcedMailings;

debugLog(LL_FUNCTIONS,'postMailToUser(user=%,template=%,params=%)',
         array($user,$template,$params));
if($user->getId()<=0)
  return ESM_NO_USER;
if($user->isEmailDisabled() && !in_array($template,$forcedMailings))
  return EG_OK;
list($subject,$headers,$body)=formatMail($template,$params);
return sendMailOrDefer($user->getEmail(),$subject,$headers,$body);
}

function getMailLimit()
{
global $mailSendLimit,$mailSendPeriod;

$now=sqlNow();
$result=sql("select count(*)
	     from mail_log
	     where sent+interval $mailSendPeriod minute>='$now'",
	    __FUNCTION__);
$sent=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
return $mailSendLimit-$sent;
}

function purgeMailLog()
{
global $mailSendPeriod;

$now=sqlNow();
$result=sql("delete
             from mail_log
	     where sent+interval $mailSendPeriod minute<'$now'",
	    __FUNCTION__);
sql("optimize table mail_log",
    __FUNCTION__,'optimize');
}

function sendMailOrDefer($destination,$subject,$headers,$body)
{
lockMailTables();
if(getMailLimit()>=1)
  $result=sendMail($destination,$subject,$headers,$body);
else
  {
  sql(sqlInsert('mail_queue',
                array('destination' => $destination,
		      'subject'     => $subject,
		      'headers'     => $headers,
		      'body'        => $body)),
      __FUNCTION__);
  $result=EG_OK;
  }
unlockMailTables();
return $result;
}

function runMailQueue()
{
lockMailTables();
$limit=getMailLimit();
if($limit<=0)
  return EG_OK;
$runResult=EG_OK;
$result=sql("select id,destination,subject,headers,body
	     from mail_queue
	     order by created
	     limit $limit",
	    __FUNCTION__,'select');
$sentList=array();
while($row=mysql_fetch_assoc($result))
     {
     $sendResult=sendMail($row['destination'],$row['subject'],$row['headers'],
                          $row['body']);
     if($sendResult!=EG_OK)
       $runResult=$sendResult;
     $sentList[]=$row['id'];
     }
if(count($sentList)>0)
  sql('delete from mail_queue
       where id in ('.join(',',$sentList).')',
      __FUNCTION__);
unlockMailTables();
return $runResult;
}

function sendMail($destination,$subject,$headers,$body)
{
sql('insert into mail_log(sent)
     values(null)',
    __FUNCTION__);
$result=mail($destination,$subject,$body,$headers);
return $result ? EG_OK : ESM_SEND;
}
?>