<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/journal.php');
require_once('lib/sql.php');

// For regular postings
define('MOD_NONE',0x0000);
define('MOD_MODERATE',0x0001);
define('MOD_HTML',0x0002); # deprecated
define('MOD_EDIT',0x0004);
define('MOD_ATTENTION',0x0008);
define('MOD_MULTIPART',0x0010);
define('MOD_ALL',0x001f);

define('MOD_HIDDEN',-1);
define('MOD_DISABLED',-2);
define('MOD_DELETE',-3);

$modbitLetters=array('M','H' /* deprecated */,'E','S','L');
$modbitNames=array('Модерировать',
		   'HTML', # deprecated
		   'Редактировать',
		   'Особо проверить',
		   'Многостраничное');

// For complains
define('MODC_NONE',0x0000);
define('MODC_CLOSED',0x0001);
define('MODC_NO_AUTO',0x0002);
define('MODC_ALL',0x0003);

$modbitCNames=array('Закрыта',
                    'Автоматически не закрывается');

// For topics
define('MODT_NONE',0x0000);
define('MODT_PREMODERATE',0x0001);
define('MODT_MODERATE',0x0002);
define('MODT_EDIT',0x0004);
define('MODT_ROOT',0x0008);
define('MODT_TRANSPARENT',0x0010);
define('MODT_ALL',0x001f);

$modbitTNames=array('Премодерировать',
                    'Модерировать',
		    'Редактировать',
		    'Корневая',
		    'Прозрачная');

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
var $max;
var $letters,$names;

function ModbitIterator($max,$letters,$names)
{
parent::Iterator();
$this->bit=1;
$this->lbbit=0;
$this->max=$max;
$this->letters=$letters;
$this->names=$names;
}

function next()
{
global $modbitLetters,$modbitNames;

parent::next();
$result=$this->bit<=$this->max
        ? new Modbit($this->bit,
	             $this->letters!=null ? $this->letters[$this->lbbit] : '',
	             $this->names[$this->lbbit])
	: 0;
$this->bit*=2;
$this->lbbit++;
return $result;
}

}

class PostingModbitIterator
      extends ModbitIterator
{

function PostingModbitIterator()
{
global $modbitLetters,$modbitNames;

parent::ModbitIterator(MOD_ALL,$modbitLetters,$modbitNames);
}

}

class ComplainModbitIterator
      extends ModbitIterator
{

function ComplainModbitIterator()
{
global $modbitCNames;

parent::ModbitIterator(MODC_ALL,null,$modbitCNames);
}

}

class TopicModbitIterator
      extends ModbitIterator
{

function TopicModbitIterator()
{
global $modbitTNames;

parent::ModbitIterator(MODT_ALL,null,$modbitTNames);
}

}

function getModbitsByEntryId($id)
{
$result=sql("select modbits
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setModbitsByEntryId($id,$bits)
{
sql("update entries
     set modbits=modbits | $bits
     where id=$id",
    __FUNCTION__);
journal("update entries
         set modbits=modbits | $bits
	 where id=".journalVar('entries',$id));
}

function resetModbitsByEntryId($id,$bits)
{
sql("update entries
     set modbits=modbits & ~$bits
     where id=$id",
    __FUNCTION__);
journal("update entries
         set modbits=modbits & ~$bits
	 where id=".journalVar('entries',$id));
}

function assignModbitsByEntryId($id,$bits)
{
sql("update entries
     set modbits=$bits
     where id=$id",
    __FUNCTION__);
journal("update entries
         set modbits=$bits
	 where id=".journalVar('entries',$id));
}
?>
