<?php
# @(#) $Id$

require_once('lib/array.php');
require_once('lib/bug.php');

class LetterOffset
{
var $letter;
var $offset;

function LetterOffset($letter,$offset)
{
$this->letter=$letter;
$this->offset=$offset;
}

function getLetter()
{
return $this->letter;
}

function getOffset()
{
return $this->offset;
}

}

class AlphabetIterator
      extends ArrayIterator
{

function AlphabetIterator($query,$no_russian=false)
{
$result=mysql_query($query);
if(!$result)
  sqlbug('ïÛÉÂËÁ SQL × ÁÌÆÁ×ÉÔÎÏÍ ÉÔÅÒÁÔÏÒÅ');
$counts=array();
while($row=mysql_fetch_assoc($result))
     if($row['count']!=0)
       $counts[uc($row['letter'])]+=$row['count'];
setlocale('LC_COLLATE','ru_RU.KOI8-R');
uksort($counts,'strcoll');
if($no_russian)
  $alphabets='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
else
  $alphabets='ABCDEFGHIJKLMNOPQRSTUVWXYZáâ÷çäå³öúéêëìíîïğòóôõæèãşûıÿùøüàñ';
$alpha=array();
$len=strlen($alphabets);
$i=0;
reset($counts);
$offset=0;
while($i<$len || current($counts))
     {
     if($i>=$len)
       {
       $alpha[key($counts)]=new LetterOffset(key($counts),$offset);
       $offset+=current($counts);
       next($counts);
       continue;
       }
     if(!current($counts))
       {
       $alpha[$alphabets[$i]]=new LetterOffset($alphabets[$i],$offset);
       $i++;
       continue;
       }
     if(strcoll($alphabets[$i],key($counts))<=0)
       {
       $alpha[$alphabets[$i]]=new LetterOffset($alphabets[$i],$offset);
       $i++;
       }
     else
       {
       $alpha[key($counts)]=new LetterOffset(key($counts),$offset);
       $offset+=current($counts);
       next($counts);
       }
     }
$this->ArrayIterator($alpha);
}

}
?>
