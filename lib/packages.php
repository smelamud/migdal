<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/utils.php');
require_once('lib/selectiterator.php');

define('PT_UNDEFINED',0);
define('PT_BOOK_ONEFILE',1);
define('PT_BOOK_SPLIT',2);

class Package
      extends DataObject
{
var $id;
var $posting_id;
var $type;
var $mime_type;
var $title;
var $body;
var $size;
var $url;
var $created;

function Package($row)
{
$this->DataObject($row);
}

function getWorldVars()
{
return array('posting_id','type','mime_type','title','body','size','url');
}

function getAdminVars()
{
return array();
}

function store()
{
$normal=$this->getNormal(false);
if($this->id)
  $result=mysql_query(makeUpdate('packages',
                                 $normal,
				 array('id' => $this->id)));
else
  {
  $normal['created']=date('Y-m-d H:i:s',time());
  $result=mysql_query(makeInsert('packages',$normal));
  $this->id=mysql_insert_id();
  $this->created=$normal['created'];
  }
return $result;
}

function getId()
{
return $this->id;
}

function getPostingId()
{
return $this->posting_id;
}

function getType()
{
return $this->type;
}

function getMimeType()
{
return $this->mime_type;
}

function getTitle()
{
return $this->title;
}

function getBody()
{
return $this->body;
}

function getSize()
{
return $this->size;
}

function getSizeKB()
{
return (int)($this->size/1024);
}

function getURL()
{
return $this->url;
}

function getCreated()
{
return strtotime($this->created);
}

}

class PackagesIterator
      extends SelectIterator
{

function PackagesIterator($posting_id)
{
$this->SelectIterator('Package',
                      "select id,posting_id,type,title,size
		       from packages
		       where posting_id=$posting_id
		       order by type,created");
}

}

function getPackageContentById($id)
{
$result=mysql_query("select id,posting_id,mime_type,body,url
                     from packages
		     where id=$id")
          or sqlbug('������ SQL ��� ������� ����������� ������');
return new Package(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                             : array());
}
?>
