<?php
# @(#) $Id$

require_once('lib/text.php');

class Footnote
{
var $no;
var $term;
var $format;
var $body;

function Footnote($no,$term,$format,$body)
{
$this->no=$no;
$this->term=$term;
$this->format=$format;
$this->body=$body;
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

return clearStotext($this->getFormat(),
		    shorten($this->getBody(),$inplaceSize,
			    $inplaceSizeMinus,$inplaceSizePlus));
}

function getHTMLBody()
{
return stotextToHTML($this->getFormat(),$this->getBody());
}

}

function extractFootnotes($s,$format,$no,&$notes)
{
$pattern='/(^|\s+)(?:&#039;((?:[^&]*(?:&[^#;]+;)?)+)&#039;\s)?{{([^}]+)}}/';
if(!is_array($notes))
  $notes=array();
do
  {
  $matches=array();
  if(!preg_match($pattern,$s,$matches))
    break;
  $note=new Footnote($no++,$matches[2],$format,$matches[3]);
  $notes[]=$note;
  if($note->isNumbered())
    $s=preg_replace($pattern,"<a name='#_ref".$note->getNo().
                             "'></a><sup><a href='#_note".$note->getNo().
			     "' title='".$note->getInplaceBody().
			     "'>".$note->getNo()."</a></sup>",$s,1);
  else
    $s=preg_replace($pattern,$matches[1]."<a name='#_ref".$note->getNo().
                             "'></a><a href='#_note".$note->getNo().
			     "' title='".$note->getInplaceBody().
			     "'>".$note->getTerm()."</a>",$s,1);
  }
while(true);
return $s;
}
?>
