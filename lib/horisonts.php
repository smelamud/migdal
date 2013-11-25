<?php
# @(#) $Id$

require_once('lib/journal.php');
require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/sql.php');

define('HOR_WE_KNOW',true);
define('HOR_THEY_KNOW',false);

function getHorisont($host,$weKnow)
{
global $dbName;

$hostS=addslashes($host);
$result=sql('select '.($weKnow ? 'we_know' : 'they_know')."
	     from $dbName.horisonts
	     where host='$hostS'",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setHorisont($host,$horisont,$weKnow)
{
global $dbName;

$hostS=addslashes($host);
$result=sql("select host
	     from $dbName.horisonts
	     where host='$hostS'",
	    __FUNCTION__,'get');
if(mysql_num_rows($result)<=0)
  sql("insert into $dbName.horisonts(host,".($weKnow ? 'we_know'
                                                     : 'they_know').")
		   values('$hostS',$horisont)",
      __FUNCTION__,'create');
else
  sql("update $dbName.horisonts
       set ".($weKnow ? 'we_know' : 'they_know')."=$horisont
       where host='$hostS'",
      __FUNCTION__,'update');
}

function lockReplication($host)
{
global $dbName;

$now=sqlNow();
$hostS=addslashes($host);
sql("update $dbName.horisonts
     set `lock`='$now'
     where host='$hostS'",
    __FUNCTION__);
}

function unlockReplication($host)
{
global $dbName;

$hostS=addslashes($host);
sql("update $dbName.horisonts
     set `lock`=null
     where host='$hostS'",
    __FUNCTION__);
}

function updateReplicationLock($host)
{
lockReplication($host);
}

function isReplicationLocked($host)
{
global $replicationLockTimeout,$dbName;

$now=sqlNow();
$hostS=addslashes($host);
$result=sql("select host
	     from $dbName.horisonts
	     where host='$hostS' and
		   `lock` is not null and
		   `lock`+interval $replicationLockTimeout minute>'$now'",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

class Horisont
      extends DataObject
{
var $host;
var $we_know;
var $they_know;

function __construct($row)
{
parent::__construct($row);
}

function getHost()
{
return $this->host;
}

function getWeKnow()
{
return $this->we_know;
}

function getTheyKnow()
{
return $this->they_know;
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->we_know=$vars['we_know'];
$this->they_know=$vars['they_know'];
}

}

function storeHorisont(&$horisont)
{
setHorisont($horisont->host,$horisont->we_know,HOR_WE_KNOW);
setHorisont($horisont->host,$horisont->they_know,HOR_THEY_KNOW);
}

class HorisontsIterator
        extends SelectIterator {

    public function __construct() {
        global $dbName;

        parent::__construct('Horisont',
                            "select host,we_know,they_know
                             from $dbName.horisonts
                             order by host");
    }

}

function getHorisontByHost($host)
{
global $dbName;

$hostS=addslashes($host);
$result=sql("select host,we_know,they_know
	     from $dbName.horisonts
	     where host='$hostS'",
	    __FUNCTION__);
return new Horisont(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                              : array());
}
?>
