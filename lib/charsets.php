<?php
# @(#) $Id$

require_once('lib/utils.php');

function isKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='à' && $s[$i]<='ÿ')
     $c++;
   if($s[$i]>='À' && $s[$i]<='ß')
     $c--;
   }
return $c<=0;
}

function convertInput($s)
{
return convert_cyr_string(
       str_replace(array("\xAB","\xBB","\x96","\x97","\x93",
                         "\x94","\xAE","\x99","\xB9","\x85"),
                   array('<<','>>','---','---','``',
	                 "''",'(r)','(tm)','No.','...'),$s),'w','k');
}

$hebrewCodes=array(
                   'Á' => '05D0',
                   'á' => 'FB2E',
                   'Ï' => 'FB2F',
                   'Â' => 'FB31',
                   '×' => '05D1',
                   'â' => '05D1',
                   'Ç' => '05D2',
                   'Ä' => '05D3',
                   'h' => '05D4',
                   'Õ' => '05D5',
                   'õ' => 'FB35',
                   '÷' => '05F0',
                   'ï' => '05F1',
                   'Ú' => '05D6',
                   'Þ' => '05D7',
                   'Ô' => '05D8',
                   'É' => '05D9',
                   'ê' => 'FB1D',
                   'Ù' => '05F2',
                   'ù' => 'FB1F',
                   'Ë' => 'FB3B',
                   'È' => '05DB',
                   'è' => '05DA',
                   'Ì' => '05DC',
                   'Í' => '05DE',
                   'í' => '05DD',
                   'Î' => '05E0',
                   'î' => '05DF',
                   'Ó' => '05E1',
                   'Ü' => '05E2',
                   'Ð' => 'FB44',
                   'Æ' => '05E4',
                   'æ' => '05E3',
                   'Ã' => '05E6',
                   'ã' => '05E5',
                   'ë' => '05E7',
                   'Ò' => '05E8',
                   'Û' => '05E9',
                   'û' => 'FB2A',
                   'ý' => 'FB2B',
                   'ô' => 'FB4A',
                   'ó' => '05EA',
                   '+' => 'FB29',
                   '-' => '05BE',
		   "'" => '05F3',
		   '"' => '05F4'
                  );
		  
function convertHebrew($s,$htmlEntities=true)
{
global $hebrewCodes;

if($htmlEntities)
  $s=unhtmlentities($s);
$c='';
for($i=0;$i<strlen($s);$i++)
   $c.='&#x'.$hebrewCodes[$s[$i]].';';
return "$c&lrm;";
}

function convertOutput($s,$koiChars=false,$koiCharset=false)
{
$s=$koiChars
       ? str_replace(array('&lt;&lt;','&gt;&gt;','&LT;&LT;','&GT;&GT;','---',
                           '``','&#039;&#039;','&sp;','&nil;'),
                     array('"','"','"','"','--',
		           '"','"',' ',''),$s)
       : str_replace(array('&lt;&lt;','&gt;&gt;','&LT;&LT;','&GT;&GT;','---',
                           '``','&#039;&#039;','(c)','(C)','(r)',
			   '(R)','(tm)','(TM)','No.','&sp;','&nil;'),
                     array('&laquo;','&raquo;','&laquo;','&raquo;','&mdash;',
		           '&ldquo;','&rdquo;','&copy;','&copy;','&reg;',
			   '&reg;','&trade;','&trade;','&#8470;',' ',''),$s);
$s=preg_replace('/\$(-?)(\S+)\$/e',
                "'\\1'=='-' ? convertHebrew(strrev('\\2'))
		            : convertHebrew('\\2')",$s);
return $koiCharset ? $s : convert_cyr_string($s,'k','w');
}

function convertSort($s)
{
$c=convert_cyr_string(
   str_replace(array('&lt;&lt;','&gt;&gt;','&LT;&LT;','&GT;&GT;','---','``',
                     "&#039;&#039;",'(c)','(C)','(r)','(R)','(tm)',
		     '(TM)','No.'),
	       array("\xAB","\xBB","\xAB","\xBB","\x96","\x93",
	             "\x94","\xA9","\xA9","\xAE","\xAE","\x99",
		     "\x99","\xB9"),$s),'k','w');
$cc='';
for($i=0;$i<strlen($c);$i++)
   {
   $code=ord($c[$i]);
   $cc.=($code<0x7D || $code>=0x80 && $code<0xC0)
         ? $c[$i]
	 : (($code>=0xC0 && $code<0xE0)
	    ? "\x7E".chr($code-0x90)
	    : (($code>=0xE0 && $code<=0xFF)
	       ? "\x7E".chr($code-0xB0)
	       : "\x7D".$c[$i]));
   }
return $cc;
}
?>
