<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/text.php');

class Paragraph
{
var $number;
var $format;
var $body;

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

}

class ParagraphIterator
      extends Iterator
{
var $format;
var $pars;

function ParagraphIterator($format,$text)
{
$this->Iterator();
$this->format=$format;
$this->pars=preg_split("/\n\s*\n/",$text);
reset($this->pars);
}

function next()
{
parent::next();
if(list($key,$value)=each($this->pars))
  return new Paragraph($this->getPosition(),$this->format,$value);
else
  return false;
}

function getLastPosition()
{
return count($this->pars);
}

}

?>
