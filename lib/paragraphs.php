<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/text.php');
require_once('lib/stotext-images.php');
require_once('lib/footnotes.php');
require_once('lib/array.php');

class Paragraph
{
var $number;
var $format;
var $body;
var $image;
var $dropLeft;
var $message_id;

function Paragraph($number,$format,$body,$messageId=0,$dropLeft=false)
{
$this->number=$number;
$this->format=$format;
$this->body=$body;
$this->message_id=$messageId;
$this->dropLeft=$dropLeft;
}

function getNumber()
{
return $this->number;
}

function getFormat()
{
return $this->format;
}

function getBody()
{
return $this->body;
}

function getHTMLBody()
{
return stotextToHTML($this->format,$this->body,$this->message_id);
}

function setImage($image)
{
$this->image=$image;
}

function getImageId()
{
return $this->image ? $this->image->getImageId() : 0;
}

function hasLargeImage()
{
return $this->image ? $this->image->hasLargeImage() : false;
}

function getPlacement()
{
return $this->image ? $this->image->getPlacement() : IPL_CENTER;
}

function isPlaced($place)
{
return $place<=IPL_HORIZONTAL ? ($this->getPlacement() & IPL_HORIZONTAL)==$place
                              : ($this->getPlacement() & IPL_VERTICAL)==$place;
}

function getTitle()
{
return $this->image ? $this->image->getTitle() : '';
}

function getHTMLTitle()
{
return $this->image ? $this->image->getHTMLTitle() : '';
}

function getMessageId()
{
return $this->message_id;
}

function isDropLeft()
{
return $this->dropLeft;
}

}

class ParagraphIterator
      extends Iterator
{
var $format;
var $pars;
var $notes=array();
var $noteOffset=1;
var $message_id;

function ParagraphIterator($format,$text,$messageId=0)
{
$this->Iterator();
$this->format=$format;
$this->message_id=$messageId;
$this->pars=preg_split("/\n\s*\n/",$text);
reset($this->pars);
}

function next()
{
Iterator::next();
if(list($key,$value)=each($this->pars))
  {
  $text=extractFootnotes($value,$this->format,
		         count($this->notes)+$this->noteOffset,
  			 $this->notes,$this->message_id);
  if(substr($text,0,5)=='&lt;-')
    {
    $dropLeft=true;
    $text=substr($text,5);
    }
  else
    $dropLeft=false;
  return new Paragraph($this->getPosition(),$this->format,$text,$this->message_id,
                       $dropLeft);
  }
else
  return false;
}

function exportFootnotes()
{
$notes=$this->notes;
$this->noteOffset+=count($notes);
$this->notes=array();
return $notes;
}

}

class FootnoteIterator
      extends ArrayIterator
{

function FootnoteIterator($source)
{
$this->ArrayIterator($source->exportFootnotes());
}

}
?>
