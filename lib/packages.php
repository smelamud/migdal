<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/utils.php');
require_once('lib/selectiterator.php');
require_once('lib/mime.php');

define('PT_UNDEFINED',0);
define('PT_BOOK_ONEFILE',1);
define('PT_BOOK_SPLIT',2);
define('PT_PDF',3);

$packageNamePrefixes=array(PT_BOOK_ONEFILE => 'book-',
                           PT_BOOK_SPLIT   => 'book-split-');

function getPackageFileName($type,$message_id,$mime_type)
{
global $packageNamePrefixes;

return $packageNamePrefixes[$type].$message_id.'.'.getMimeExtension($mime_type);
}

class Package
      extends DataObject
{
var $id;
var $message_id;
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
return array('message_id','type','mime_type','title','body','size','url');
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

function getMessageId()
{
return $this->message_id;
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

function getHref()
{
if($this->getURL()!='')
  return $this->getURL();
else
  return '/lib/package.php/'.getPackageFileName($this->getType(),
                                                $this->getMessageId(),
						$this->getMimeType()).
	 '?id='.$this->getId();
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
                      "select packages.id as id,postings.id as posting_id,type,
		              mime_type,title,size,url
		       from packages
		            left join postings
			         on packages.message_id=postings.message_id
		       where postings.id=$posting_id
		       order by type,created");
}

}

function getPackageContentById($id)
{
$result=mysql_query("select id,message_id,mime_type,body,url
                     from packages
		     where id=$id")
          or sqlbug('Ошибка SQL при выборке содержимого пакета');
return new Package(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                             : array());
}
?>
