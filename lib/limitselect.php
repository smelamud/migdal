<?php
# @(#) $Id$

require_once('lib/selectiterator.php');

class LimitSelectIterator
      extends SelectIterator
{
var $size;
var $limit;
var $offset;

function LimitSelectIterator($aClass,$query,$limit=10,$offset=0)
{
preg_match('/^(.*select)(.*)(from.*)$/is',$query,$parts);
$cquery=$parts[1].' count(*) '.$parts[3];
$result=mysql_query($cquery)
	     or die("ïÛÉÂËÁ SQL × ÌÉÍÉÔÉÒÏ×ÁÎÎÏÍ ÉÔÅÒÁÔÏÒÅ $cquery");
$this->size=mysql_result($result,0,0);
$this->limit=$limit;
$this->offset=$offset;
$this->SelectIterator($aClass,"$query limit $offset,$limit");
}

function getSize()
{
return $this->size;
}

function getLimit()
{
return $this->limit;
}

function getOffset()
{
return $this->offset;
}

function getPrevOffset()
{
$n=$this->offset-$this->limit;
return $n<0 ? 0 : $n;
}

function getNextOffset()
{
return $this->offset+$this->limit;
}

function getBeginValue()
{
return $this->offset+1;
}

function getEndValue()
{
return $this->offset+$this->getCount();
}

}
?>
