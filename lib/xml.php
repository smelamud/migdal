<?php
# @(#) $Id$

require_once('lib/charsets.php');

define('DSC_NO_GT',0);
define('DSC_GT',1);

function delicateSpecialChars($s,$gt=DSC_NO_GT)
{
$specials=array('<' => '&lt;',
                '"' => '&quot;');
if($gt==DSC_GT)
  $specials['>']='&gt;';
return strtr($s,$specials);
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
       $value=delicateSpecialChars(convertFromXMLText($value));
       $s.=" $key=\"$value\"";
       }
$s.=$empty ? ' />' : '>';
return $s;
}

function makeText($text)
{
return delicateSpecialChars(convertFromXMLText($text),DSC_GT);
}

class XMLParser
{
var $xml_parser;

function XMLParser()
{
$this->xml_parser=xml_parser_create('UTF-8');
xml_set_object($this->xml_parser,$this);
xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING,true);
xml_parser_set_option($this->xml_parser,XML_OPTION_TARGET_ENCODING,'UTF-8');
xml_set_element_handler($this->xml_parser,'startElement','endElement');
xml_set_character_data_handler($this->xml_parser,'characterData');
}

function xmlError($message)
{
}

function parse($rootTag,$body)
{
xml_parse($this->xml_parser,"<$rootTag>",false);
if(!xml_parse($this->xml_parser,$body,false))
  $this->xmlError(sprintf('** XML error: %s at line %d **',
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
