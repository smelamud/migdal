<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');

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

function getURL()
{
return $this->url;
}

function getCreated()
{
return strtotime($this->created);
}

}

function getPackageContentById($id)
{
$result=mysql_query("select id,posting_id,mime_type,body,url
                     from packages
		     where id=$id")
          or sqlbug('Ошибка SQL при выборке содержимого пакета');
return new Package(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                             : array());
}
?>
