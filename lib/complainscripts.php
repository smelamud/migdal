<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');

define('CSCR_NONE',0x0000);
define('CSCR_CLOSE',0x0001);
define('CSCR_OPEN',0x0002);
define('CSCR_ALL',0x0003);

$cscrProcNames=array(CSCR_CLOSE => 'cscrClose',
		     CSCR_OPEN  => 'cscrOpen');

$cscrTitles=array(CSCR_CLOSE => 'Закрыть жалобу',
		  CSCR_OPEN  => 'Возобновить жалобу');

function cscrClose($complain)
{
closeComplain($complain->getId());
}

function cscrOpen($complain)
{
openComplain($complain->getId());
}

class ComplainScript
{
var $id;

function __construct($id)
{
$this->id=$id;
}

function exec($complain)
{
global $cscrProcNames;

if(isset($cscrProcNames[$this->id]))
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

return isset($cscrTitles[$this->id]) ? $cscrTitles[$this->id] : '';
}

}

class ComplainScriptListIterator
      extends MIterator
{
var $id;
var $mask;

function __construct($mask=CSCR_ALL)
{
parent::__construct();
$this->id=1;
$this->mask=$mask;
$this->roll();
}

function roll()
{
while(($this->id & $this->mask)==0 && $this->id<=CSCR_ALL)
     $this->id*=2;
}

function getNext()
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
