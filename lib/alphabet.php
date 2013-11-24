<?php
# @(#) $Id$

require_once('lib/array.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/charsets.php');

define('AI_MAX_DEPTH', 6);

class AlphabetIterator
      extends MArrayIterator
{

function getAlphas($query,$limit=0,$prefix='',$depth=0)
{
$METHOD=get_method($this,'getAlphas');
$result=sql(str_replace(array('@prefix@',
                              '@len@'),
                        array($prefix,
			      strlen($prefix)+1),
	                $query),
            $METHOD);
$counts=array();
while($row=mysql_fetch_assoc($result))
     if($row['count']!=0)
       $counts[uc($row['letter'])]+=$row['count'];
// FIXME должно проставляться в конфиге
setlocale(LC_COLLATE,'ru_RU.KOI8-R');
uksort($counts,'strcoll');
$alpha=array();
foreach($counts as $letter => $count)
       if($letter!='')
	 if($count<=$limit || $limit==0 || $depth>=AI_MAX_DEPTH)
	   $alpha[]=$letter;
	 else
	   $alpha=array_merge($alpha,
	                      $this->getAlphas($query,$limit,$letter,$depth+1));
return $alpha;
}

function __construct($query,$limit=0)
{
parent::__construct($this->getAlphas($query,$limit));
}

}
?>
