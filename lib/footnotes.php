<?php
# @(#) $Id$

require_once('lib/text.php');

class Footnote
{
var $no;
var $term;
var $format;
var $body;
var $message_id;

function Footnote($no,$term,$format,$body,$messageId=0)
{
$this->no=$no;
$this->term=$term;
$this->format=$format;
$this->body=$body;
$this->message_id=$messageId;
}

function getNo()
{
return $this->no;
}

function isNumbered()
{
return $this->term=='';
}

function getTerm()
{
return $this->term;
}

function getFormat()
{
return $this->format;
}

function getBody()
{
return $this->body;
}

function getInplaceBody()
{
global $inplaceSize,$inplaceSizeMinus,$inplaceSizePlus;

$shortBody=shorten($this->getBody(),$inplaceSize,
                   $inplaceSizeMinus,$inplaceSizePlus);
if($shortBody!=$this->getBody())
  $shortBody.='...';
return clearStotext($this->getFormat(),$shortBody);
}

function getHTMLBody()
{
return stotextToHTML($this->getFormat(),$this->getBody(),$this->message_id);
}

function getMessageId()
{
return $this->message_id;
}

}

function extractFootnotes($s,$format,$no,&$notes,$message_id=0)
{
$pattern='/(^|\s+)(?:&#039;((?:[^&]*(?:&[^#;]+;)?)+)&#039;\s)?{{((?:[^}]|}[^}])+)}}/';
if(!is_array($notes))
  $notes=array();
do
  {
  $matches=array();
  if(!preg_match($pattern,$s,$matches))
    break;
  $body=strtr($matches[3],"\r\n",'  ');
  $note=new Footnote($no++,$matches[2],$format,$body,$message_id);
  $notes[]=$note;
  if($note->isNumbered())
    $s=preg_replace($pattern,"<a name='_ref".$note->getNo().
                             "'></a><sup><a href='#_note".$note->getNo().
			     "' title='".$note->getInplaceBody().
			     "'>".$note->getNo()."</a></sup>",$s,1);
  else
    $s=preg_replace($pattern,$matches[1]."<a name='_ref".$note->getNo().
                             "'></a><a href='#_note".$note->getNo().
			     "' title='".$note->getInplaceBody().
			     "'>".$note->getTerm()."</a>",$s,1);
  }
while(true);
return $s;
}
?>
