<?php
# @(#) $Id$

function utf8DecodeMarkup($s)
{
$s=iconv("UTF-8","UTF-16",$s);
$c='';
$len=strlen($s);
for($i=0;$i<$len;$i+=2)
   if($i>=2)
     if($i+1<$len && ord($s{$i+1})!=0)
       $c.='&#x'.sprintf('%02x',ord($s{$i+1})).sprintf('%02x',ord($s{$i})).';';
     else
       $c.=$s{$i};
return $c;
}

function makeTag($name,$attrs=array(),$empty=false)
{
$s='<'.strtolower($name);
foreach($attrs as $key => $value)
       {
       $key=strtolower($key);
       $value=htmlspecialchars(utf8DecodeMarkup($value));
       $s.=" $key=\"$value\"";
       }
$s.=$empty ? ' />' : '>';
return $s;
}

class XMLParser
{
var $xml_parser;

function XMLParser()
{
$this->xml_parser=xml_parser_create();
xml_set_object($this->xml_parser,$this);
xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING,true);
xml_parser_set_option($this->xml_parser,XML_OPTION_TARGET_ENCODING,"UTF-8");
xml_set_element_handler($this->xml_parser,"startElement","endElement");
xml_set_character_data_handler($this->xml_parser,"characterData");
}

function xmlError($message)
{
}

function parse($rootTag,$body)
{
xml_parse($this->xml_parser,"<$rootTag>",false);
if(!xml_parse($this->xml_parser,$body,false))
  $this->xmlError(sprintf("** XML error: %s at line %d **",
	               xml_error_string(xml_get_error_code($this->xml_parser)),
	               xml_get_current_line_number($this->xml_parser)));
xml_parse($this->xml_parser,"</$rootTag>",true);
}

function free()
{
xml_parser_free($this->xml_parser);
}

function startElement($parser,$name,$attrs)
{
}

function endElement($parser,$name)
{
}

function characterData($parser,$data)
{
}

}
?>
