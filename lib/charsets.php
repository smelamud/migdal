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
$c=str_replace(array("\xAB","\xBB","\x96","\x97","\x84",
                     "\x93","\xAE","\x99","\xB9"),
               array('<<','>>','---','---','``',
	             "''",'(r)','(tm)','No.'),$s);
return convert_cyr_string($c,'w','k');
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
                     array('&laquo;','&raquo;','&#151;','&#132;','&#147;',
		           '&#169;','&#169;','&#174;','&#174;','&#153;',
			   '&#153;','&#8470;'),$s),'k','w');
}
?>
