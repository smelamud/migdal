<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/text.php');
require_once('lib/stotext-images.php');
require_once('lib/footnotes.php');

class Paragraph
{
var $number;
var $format;
var $body;
var $image;

function Paragraph($number,$format,$body)
{
$this->number=$number;
$this->format=$format;
$this->body=$body;
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
return stotextToHTML($this->format,$this->body);
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

}

class ParagraphIterator
      extends Iterator
{
var $format;
var $pars;
var $notes=array();
var $noteOffset=1;

function ParagraphIterator($format,$text)
{
$this->Iterator();
$this->format=$format;
$this->pars=preg_split("/\n\s*\n/",$text);
reset($this->pars);
}

function next()
{
Iterator::next();
if(list($key,$value)=each($this->pars))
  return new Paragraph($this->getPosition(),$this->format,
                       extractFootnotes($value,$this->format,
		                        count($this->notes)+$this->noteOffset,
					$this->notes));
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
?>
