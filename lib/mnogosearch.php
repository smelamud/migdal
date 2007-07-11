<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/iterator.php');
require_once('lib/url-fopen.php');

class SearchItem
{
var $postid;
var $topic_id;
var $body;
var $title;

function SearchItem($postid,$topic_id,$body,$title)
{
$this->postid=$postid;
$this->topic_id=$topic_id;
settype($this->postid,'integer');
settype($this->topic_id,'integer');
$this->body=$body;
$this->title=$title;
}

function getPostingId()
{
return $this->postid;
}

function getTopicId()
{
return $this->topic_id;
}

function getBody()
{
return $this->body;
}

function getTitle()
{
return $this->title;
}

}

class MnogosearchIterator
      extends MIterator
{
var $fd;
var $status;
var $size;
var $limit;
var $offset;
var $count;

function MnogosearchIterator($query,$limit=20,$offset=0)
{
global $siteDomain;

$this->limit=$limit;
$this->offset=$offset;
$this->fd=url_fopen("http://$siteDomain/cgi-bin/search.cgi?q="
                    .urlencode(convert_cyr_string($query,'k','w'))
		    .'&wf=14442&np='.($this->getPage()-1).'&ps='
		    .$this->getLimit());
$this->status=$this->nextLine();
if($this->status=='OK')
  {
  $s=$this->nextLine();
  $this->size=$this->nextLine();
  $first=$this->nextLine();
  $last=$this->nextLine();
  $this->count=$last-$first+1;
  }
else
  {
  $this->size=0;
  $this->count=0;
  }
do
  {
  $s=$this->nextLine();
  }
while($s!='ITEM' && $s!='EOF');
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

function getCount()
{
return $this->count;
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
return $this->size==0 ? 0 : (int)(($this->size-1)/$this->limit)+1;
}

function getStatus()
{
return $this->status;
}

function nextLine()
{
if($this->fd && !url_feof($this->fd))
  return trim(convert_cyr_string(url_fgets($this->fd),'w','k'));
else
  {
  if($this->fd)
    {
    url_fclose($this->fd);
    $this->fd=0;
    }
  return 'EOF';
  }
}

function next()
{
$url=$this->nextLine();
if($url=='EOF')
  return 0;
$parts=parse_url($url);
$vars=parseQuery($parts['query']);
$s=$this->nextLine();
$body=$this->nextLine();
$title=$this->nextLine();
do
  {
  $s=$this->nextLine();
  }
while($s!='ITEM' && $s!='EOF');
return new SearchItem($vars['postid'],$vars['topic_id'],$body,$title);
}

}
?>
