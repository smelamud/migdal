<?php
# @(#) $Id$

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
$c=str_replace(array("\xAB","\xBB","\x96","\x97","\x93",
                     "\x94","\xAE","\x99","\xB9","\x85"),
               array('<<','>>','---','---','``',
	             "''",'(r)','(tm)','No.','...'),$s);
$cc=convert_cyr_string($c,'w','k');
$fd=fopen('/tmp/msgs.log','a');
fputs($fd,"$s\n");
fputs($fd,"$c\n");
fputs($fd,"$cc\n");
fclose($fd);
return $cc;
}

function convertOutput($s)
{
global $userReadKOI;

return convert_cyr_string($userReadKOI>0
       ? str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;"),
                     array('"','"','--','"','"'),$s)
       : str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;",
                           '(c)','(C)','(r)','(R)','(tm)',
			   '(TM)','No.'),
                     array('&laquo;','&raquo;','&mdash;','&ldquo;','&rdquo;',
		           '&copy;','&copy;','&reg;','&reg;','&trade;',
			   '&trade;','&#8470;'),$s),'k','w');
}
?>
