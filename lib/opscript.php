<?php
# @(#) $Id$

function osSubVar($s,$vars,$params)
{
if(preg_match('/^%{?(\w+)}?$/',$s,$match))
  return $params[strtolower($match[1])];
if(preg_match('/^\${?(?:\w+\.)?(\w+)}?$/',$s,$match))
  return $vars[''][$match[1]];
if(preg_match('/^\${?(\w+):(?:\w+\.)?(\w+)}?$/',$s,$match))
  return $vars[strtolower($match[1])][$match[2]];
return $s;
}

function osSubVars($s,$vars,$params)
{
$c='';
while($s!='')
     {
     preg_match('/^([^\$%]*)((?:(?:\${?(?:\w+:)?(?:\w+\.)?\w+}?)|(?:%{?\w+}?))?)(.*)$/',
                $s,$match);
     list($k,$head,$var,$s)=$match;
     $c.=$head;
     if($var!='')
       $c.=osSubVar($var,$vars,$params);
     if($head=='' && $var=='')
       {
       $c.=substr($s,0,1);
       $s=substr($s,1);
       }
     }
return $c;
}

function opScript($script,$params)
{
$vars=array();
$cont=0;
$preamble=1;
$out='';
foreach(explode("\n",$script) as $line)
       {
       if($cont)
         $s=preg_replace('/\\\\\s*$/',$line,$s);
       else
         $s=$line;
       if(preg_match('/\\\\\s*$/',$s))
         {
	 $cont=1;
	 continue;
	 }
       $cont=0;
       if($preamble && (preg_match('/^\s*$/',$s) || preg_match('/^@/',$s)))
         continue;
       $s=osSubVars($s,$vars,$params);
       if(preg_match('/^\s*(\w*)\s*=(.*)$/',$s,$match))
         {
	 list($k,$name,$sql)=$match;
	 $result=mysql_query($sql);
	 if(!$result)
	   die("Ошибка в операторном скрипте в операторе: $sql: ".mysql_error());
	 $vars[strtolower($name)]=mysql_fetch_assoc($result);
	 }
       else
         {
	 $preamble=0;
	 $out.="$s\n";
	 }
       }
return $out;
}
?>
