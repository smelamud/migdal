<?php
# @(#) $Id$

require_once('lib/array.php');

define('TF_PLAIN',0);
define('TF_TEX',1);
define('TF_XML',2);
define('TF_MAIL',3);
define('TF_MAX',3);

define('MTEXT_LINE',1);
define('MTEXT_SHORT',2);
define('MTEXT_LONG',3);

function ellipsize($s,$len)
{
if(strlen($s)<=$len)
  return $s;
$c=substr($s,0,($len-3)/2);
return "$c...".substr($s,strlen($s)-($len-3-strlen($c)));
}

function uc($s)
{
setlocale(LC_CTYPE,'ru_RU.KOI8-R');
return strtoupper($s);
}

function camelCase($s)
{
$up=true;
$c='';
for($i=0;$i<strlen($s);$i++)
   if($s[$i]=='_')
     $up=true;
   else
     {
     $c.=$up ? uc($s[$i]) : $s[$i];
     $up=false;
     }
return $c;
}

function unhtmlentities($s)
{
$table=array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES));
$table['&#039;']="'";
return strtr($s,$table);
}

function getPlural($n,$forms)
{
$a=$n%10;
$b=((int)$n/10)%10;
return $b==1 || $a>=5 || $a==0 ? $forms[2] : ($a==1 ? $forms[0] : $forms[1]);
}

function getQuote($s,$width)
{
return preg_replace('/^/m','> ',wordwrap($s,$width));
}

$TextFormats=array(TF_PLAIN => 'Простой текст (без переносов строк)',
                   TF_TEX => 'Простой текст (с переносами строк)',
                   TF_XML => 'XML');

class TextFormatsIterator
      extends AssocArrayIterator
{

function TextFormatsIterator()
{
global $TextFormats;

parent::AssocArrayIterator($TextFormats);
}

}
?>
