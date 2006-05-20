<?php
# @(#) $Id$

function utf8DecodeMarkup($s,$maxlen=0)
{
$s=iconv("UTF-8","UTF-16",$s);
$c='';
$len=strlen($s);
if($maxlen!=0)
  $len=min($len,$maxlen*2);
for($i=0;$i<$len;$i+=2)
   if($i>=2)
     if($i+1<$len && ord($s{$i+1})!=0)
       $c.='&#x'.sprintf('%02x',ord($s{$i+1})).sprintf('%02x',ord($s{$i})).';';
     else
       $c.=$s{$i};
return $c;
}

function delicateSpecialChars($s)
{
return strtr($s,array('<' => '&lt;',
                      '"' => '&quot;'));
}

function delicateAmps($s,$xmlEntities=true)
{
if($xmlEntities)
  $entities='lt|amp|quot';
else
  $entities='[A-Za-z]+';
$c='';
for($i=0;$i<strlen($s);$i++)
   switch($s{$i})
         {
	 case '&':
	      if(preg_match("/^&(?:#[0-9]{1,5}|#x[0-9A-Fa-f]{1,4}|$entities);/",
	                    substr($s,$i)))
	        $c.='&';
	      else
	        $c.='&amp;';
	      break;
	 default:
	      $c.=$s{$i};
	 }
return $c;
}

function makeTag($name,$attrs=array(),$empty=false)
{
$s='<'.strtolower($name);
foreach($attrs as $key => $value)
       {
       $key=strtolower($key);
       $value=delicateSpecialChars(utf8DecodeMarkup($value));
       $s.=" $key=\"$value\"";
       }
$s.=$empty ? ' />' : '>';
return $s;
}

function makeText($text)
{
return delicateSpecialChars(utf8DecodeMarkup($text));
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
