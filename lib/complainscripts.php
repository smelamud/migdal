<?php
# @(#) $Id$

require_once('lib/messages.php');
require_once('lib/postings.php');

define('CSCR_NONE',0);
define('CSCR_CLOSE',1);
define('CSCR_CLOSE_ENABLE',2);
define('CSCR_OPEN',4);
define('CSCR_OPEN_DISABLE',8);
define('CSCR_ALL',15);
define('CSCR_MASK_NORMAL',CSCR_CLOSE|CSCR_OPEN);
define('CSCR_MASK_FORUM',CSCR_CLOSE|CSCR_OPEN);
define('CSCR_MASK_POSTING',CSCR_CLOSE|CSCR_CLOSE_ENABLE|CSCR_OPEN
                           |CSCR_OPEN_DISABLE);

$cscrProcNames=array(CSCR_CLOSE        => 'cscrClose',
		     CSCR_CLOSE_ENABLE => 'cscrCloseEnable',
		     CSCR_OPEN         => 'cscrOpen',
		     CSCR_OPEN_DISABLE => 'cscrOpenDisable');

$cscrTitles=array(CSCR_CLOSE        => 'Закрыть жалобу',
		  CSCR_CLOSE_ENABLE => 'Закрыть жалобу и открыть доступ',
		  CSCR_OPEN         => 'Возобновить жалобу',
		  CSCR_OPEN_DISABLE => 'Возобновить жалобу и закрыть доступ');

function cscrClose($complain)
{
$id=$complain->getId();
mysql_query("update complains
             set closed=now()
	     where id=$id")
     or die('Ошибка SQL при закрытии жалобы');
}

function cscrCloseEnable($complain)
{
cscrClose($complain);
$message_id=getMessageIdByPostingId($complain->getLink());
setDisabledByMessageId($message_id,0);
}

function cscrOpen($complain)
{
$id=$complain->getId();
reopenComplain($id);
}

function cscrOpenDisable($complain)
{
cscrOpen($complain);
$message_id=getMessageIdByPostingId($complain->getLink());
setDisabledByMessageId($message_id,1);
}

class ComplainScript
{
var $id;

function ComplainScript($id)
{
$this->id=$id;
}

function exec($complain)
{
global $cscrProcNames;

if($this->id>CSCR_NONE && $this->id<=CSCR_ALL)
  {
  $proc=$cscrProcNames[$this->id];
  $proc($complain);
  }
}

function getId()
{
return $this->id;
}

function getTitle()
{
global $cscrTitles;

return ($this->id>CSCR_NONE && $this->id<=CSCR_ALL)
       ? $cscrTitles[$this->id] : '';
}

}

class ComplainScriptListIterator
      extends Iterator
{
var $id;
var $mask;

function ComplainScriptListIterator($mask)
{
$this->Iterator();
$this->id=1;
$this->mask=$mask;
$this->roll();
}

function roll()
{
while(($this->id & $this->mask)==0 && $this->id<=CSCR_ALL)
     $this->id*=2;
}

function next()
{
if($this->id<=CSCR_ALL)
  {
  $script=new ComplainScript($this->id);
  $this->id*=2;
  $this->roll();
  return $script;
  }
else
  return 0;
}

}

function getComplainScriptById($id)
{
return new ComplainScript($id);
}
?>
