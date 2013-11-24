<?php
# @(#) $Id$

require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/sql.php');

class LimitSelectIterator
      extends SelectIterator
{
var $count_query;
var $size;
var $limit;
var $offset;

function __construct($aClass,$query,$limit=10,$offset=0,$cquery='')
{
if($cquery=='')
  {
  preg_match('/^(.*select)(.*)(from.*)$/is',$query,$parts);
  $cquery=$parts[1].' count(*) '.$parts[3];
  }
$this->count_query=$cquery;
$this->size=-1;
$this->limit=$limit;
$this->offset=$offset;
parent::__construct($aClass,$limit==0 ? $query
                                      : "$query limit $offset,$limit" );
}

function countSelect()
{
$METHOD=get_method($this,'countSelect');
if($this->size<0)
  {
  $result=sql($this->count_query,
              $METHOD);
  $this->size=mysql_result($result,0,0);
  }
}

function getCountQuery()
{
return $this->count_query;
}

function setCountQuery($cquery)
{
$this->count_query=$cquery;
}

function getSize()
{
$this->countSelect();
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

function getPage()
{
return (int)($this->offset/$this->limit)+1;
}

function getPageCount()
{
$this->countSelect();
return $this->size==0 ? 0 : (int)(($this->size-1)/$this->limit)+1;
}

}
?>
