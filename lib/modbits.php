<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/journal.php');
require_once('lib/sql.php');

define('MOD_NONE',0x0000);
define('MOD_MODERATE',0x0001);
define('MOD_HTML',0x0002);
define('MOD_EDIT',0x0004);
define('MOD_ATTENTION',0x0008);
define('MOD_MULTIPART',0x0010);
define('MOD_ARCHIVE',0x0020);
define('MOD_ALL',0x003f);

$modbitLetters=array('M','H','E','S','L','A');
$modbitNames=array('������������',
		   'HTML',
		   '�������������',
		   '����� ���������',
		   '���������������',
		   '�����');

define('MODC_NONE',0x0000);
define('MODC_CLOSED',0x0001);
define('MODC_NO_AUTO',0x0002);

define('MODT_NONE',0x0000);
define('MODT_PREMODERATE',0x0001);
define('MODT_MODERATE',0x0002);
define('MODT_EDIT',0x0004);

class Modbit
{
var $bit;
var $letter;
var $name;

function Modbit($bit,$letter,$name)
{
$this->bit=$bit;
$this->letter=$letter;
$this->name=$name;
}

function getBit()
{
return $this->bit;
}

function getLetter()
{
return $this->letter;
}

function getName()
{
return $this->name;
}

}

class ModbitIterator
      extends Iterator
{
var $bit,$lbbit;

function ModbitIterator()
{
$this->Iterator();
$this->bit=1;
$this->lbbit=0;
}

function next()
{
global $modbitLetters,$modbitNames;

Iterator::next();
$result=$this->bit<=MOD_ALL
        ? new Modbit($this->bit,
	             $modbitLetters[$this->lbbit],
	             $modbitNames[$this->lbbit])
	: 0;
$this->bit*=2;
$this->lbbit++;
return $result;
}

}

function getModbitsByMessageId($id)
{
$result=sql("select modbits
	     from messages
	     where id=$id",
	    'getModbitsByMessageId');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setModbitsByMessageId($id,$bits)
{
sql("update messages
     set modbits=modbits | $bits
     where id=$id",
    'setModbitsByMessageId');
journal("update messages
         set modbits=modbits | $bits
	 where id=".journalVar('messages',$id));
}

function resetModbitsByMessageId($id,$bits)
{
sql("update messages
     set modbits=modbits & ~$bits
     where id=$id",
    'resetModbitsByMessageId');
journal("update messages
         set modbits=modbits & ~$bits
	 where id=".journalVar('messages',$id));
}

function assignModbitsByMessageId($id,$bits)
{
sql("update messages
     set modbits=$bits
     where id=$id",
    'assignModbitsByMessageId');
journal("update messages
         set modbits=$bits
	 where id=".journalVar('messages',$id));
}
?>
