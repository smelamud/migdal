<?php
# @(#) $Id$

require_once('lib/mtext-html.php');

class LargeText
{
var $id;
var $text_xml;
var $text_html;
var $footnotes_html;

function LargeText($text_xml,$id)
{
$this->text_xml=$text_xml;
$this->id=$id;
$text=mtextToHTML($this->text_xml,MTEXT_LONG,$this->id,true);
$this->text_html=$text['body'];
$this->footnotes_html=$text['footnotes'];
}

function getId()
{
return $this->id;
}

function getTextXML()
{
return $this->text_xml;
}

function getTextHTML()
{
return $this->text_html;
}

function getFootnotesHTML()
{
return $this->footnotes_html;
}

}
?>
