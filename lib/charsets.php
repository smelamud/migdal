<?php
# @(#) $Id$

require_once('lib/utils.php');

function isKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='Ю' && $s[$i]<='Ъ')
     $c++;
   if($s[$i]>='ю' && $s[$i]<='ъ')
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
                   'а' => '05D0',
                   'А' => 'FB2E',
                   'о' => 'FB2F',
                   'б' => 'FB31',
                   'в' => '05D1',
                   'Б' => '05D1',
                   'г' => '05D2',
                   'д' => '05D3',
                   'h' => '05D4',
                   'у' => '05D5',
                   'У' => 'FB35',
                   'В' => '05F0',
                   'О' => '05F1',
                   'з' => '05D6',
                   'ч' => '05D7',
                   'т' => '05D8',
                   'и' => '05D9',
                   'Й' => 'FB1D',
                   'ы' => '05F2',
                   'Ы' => 'FB1F',
                   'к' => 'FB3B',
                   'х' => '05DB',
                   'Х' => '05DA',
                   'л' => '05DC',
                   'м' => '05DE',
                   'М' => '05DD',
                   'н' => '05E0',
                   'Н' => '05DF',
                   'с' => '05E1',
                   'э' => '05E2',
                   'п' => 'FB44',
                   'ф' => '05E4',
                   'Ф' => '05E3',
                   'ц' => '05E6',
                   'Ц' => '05E5',
                   'К' => '05E7',
                   'р' => '05E8',
                   'ш' => '05E9',
                   'Ш' => 'FB2A',
                   'Щ' => 'FB2B',
                   'Т' => 'FB4A',
                   'С' => '05EA',
                   '+' => 'FB29',
                   '-' => '05BE',
		   "&#039;" => '05F3',
		   '"' => '05F4'
                  );

// Использовать только для обработки вывода convertOutput()!
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
