<?php
# @(#) $Id$

require_once('lib/array.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/charsets.php');

class AlphabetIterator
      extends ArrayIterator
{

function getAlphas($query,$limit=0,$sortCoding=false,$prefix='')
{
$result=sql(str_replace(array('@prefix@',
                              '@len@'),
                        array($sortCoding ? convertSort($prefix) : $prefix,
			      strlen($prefix)+1),
	                $query),
            __METHOD__);
$counts=array();
while($row=mysql_fetch_assoc($result))
     if($row['count']!=0)
       $counts[uc($row['letter'])]+=$row['count'];
setlocale(LC_COLLATE,'ru_RU.KOI8-R');
uksort($counts,'strcoll');
$alpha=array();
foreach($counts as $letter => $count)
       if($letter!='')
	 if($count<=$limit || $limit==0)
	   $alpha[]=$letter;
	 else
	   $alpha=array_merge($alpha,$this->getAlphas($query,$limit,$sortCoding,
						      $letter));
return $alpha;
}

function AlphabetIterator($query,$limit=0,$sortCoding=false)
{
parent::ArrayIterator($this->getAlphas($query,$limit,$sortCoding));
}

}
?>
