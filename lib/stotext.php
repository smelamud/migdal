<?php
# @(#) $Id$

require_once('lib/text.php');
require_once('lib/charsets.php');
require_once('lib/sql.php');

define('SB_POSTING_BODY',1);
define('SB_TOPIC_DESC',2);
define('SB_MAX',2);

class Stotext
      extends DataObject
{
var $id;
var $body;
var $image_set;
var $large_filename;
var $large_format;
var $large_body;
var $large_imageset;
var $image_id;
var $has_large_image;

function Stotext($row,$body='body')
{
$row['id']=$row['stotext_id'];
$row['body']=$row[$body];
$row['large_body']=$row["large_$body"];
$this->DataObject($row);
}

function setup($vars,$body='body')
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);

$this->body=htmlspecialchars($vars[$body],ENT_QUOTES);

if(!c_digit($this->large_format) || $this->large_format>TF_MAX)
  $this->large_format=TF_PLAIN;

if($vars["large_$body"]!='')
  $this->large_body=textToStotext($this->large_format,$vars["large_$body"]);
if(isset($vars["large_${body}id"]))
  {
  $lb=tmpTextRestore($vars["large_${body}id"]);
  if($lb!='')
    $this->large_body=$lb;
  }

if(isset($vars["${body}id"]))
  $this->body=tmpTextRestore($vars["${body}id"]);
}

# Включение large_imageset в WorldVars и CorrespondentVars по непонятным
# причинам приводит к потере при редактировании связи с large_imageset
function getCorrespondentVars()
{
return array('large_format','image_set');
}

function getWorldVars()
{
return array('body','large_filename','large_format','large_body','image_set');
}

function getAdminVars()
{
return array();
}

function getJencodedVars()
{
return array('body' => '','image_set' => 'images','large_filename' => '',
             'large_body' => '','large_imageset' => 'images');
}

function store($admin)
{
$normal=$this->getNormal($admin);
if($this->id)
  {
  $result=sql(makeUpdate('stotexts',
			 $normal,
			 array('id' => $this->id)),
	      get_method($this,'store'),'update');
  journal(makeUpdate('stotexts',
                     jencodeVars($normal,$this->getJencodedVars()),
		     array('id' => journalVar('stotexts',$this->id))));
  }
else
  {
  $result=sql(makeInsert('stotexts',
                         $normal),
	      get_method($this,'store'),'insert');
  $this->id=sql_insert_id();
  journal(makeInsert('stotexts',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'stotexts',$this->id);
  }
return $result;
}

function getId()
{
return $this->id;
}

function setId($id)
{
$this->id=$id;
}

function getBody()
{
return $this->body;
}

function getLargeFilename()
{
return $this->large_filename;
}

function getLargeFormat()
{
return $this->large_format;
}

function getLargeBody()
{
return $this->large_body;
}

function getLargeImageSet()
{
return $this->large_imageset;
}

function setLargeImageSet($image_set)
{
$this->large_imageset=$image_set;
}

function getImageSet()
{
return $this->image_set;
}

function setImageSet($image_set)
{
$this->image_set=$image_set;
}

function getImageId()
{
return $this->image_id;
}

function hasLargeImage()
{
return $this->has_large_image;
}

}

function uploadLargeText(&$stotext)
{
global $large_file,$large_file_size,$large_file_type,$large_file_name,
       $large_loaded,
       $maxLargeText,$tmpDir;

if(isset($large_loaded) && $large_loaded==1)
  return EUL_OK;
if(!isset($large_file) || $large_file=='' || !is_uploaded_file($large_file)
   || filesize($large_file)!=$large_file_size)
  return EUL_OK;
if($large_file_size>$maxLargeText)
  return EUL_LARGE;

$large_file_tmpname=tempnam($tmpDir,'mig-');
if(!move_uploaded_file($large_file,$large_file_tmpname))
  return EUL_OK;
$fd=fopen($large_file_tmpname,'r');
$stotext->large_filename=$large_file_name;
$text=fread($fd,$maxLargeText);
$stotext->large_body=textToStotext($stotext->large_format,
                                   isKOI($text) ? $text : convertInput($text));
fclose($fd);
unlink($large_file_tmpname);

return EUL_OK;
}
?>
