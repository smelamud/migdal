<?php
# @(#) $Id$

function composeDateTime($timestamp,$vars,$base_name)
{
$comps=array('seconds' => 'second',
             'minutes' => 'minute',
	     'hours' => 'hour',
	     'mday' => 'day',
	     'mon' => 'month',
	     'year' => 'year');
$dt=getdate($timestamp);
foreach($comps as $comp => $varname)
       {
       $name="${base_name}_$varname";
       if(isset($vars[$name]) && $vars[$name]>0
          || $comp{strlen($comp)-1}=='s' && $vars[$name]>=0)
	 $dt[$comp]=$vars[$name];
       }
$ts=mktime($dt['hours'],$dt['minutes'],$dt['seconds'],
           $dt['mon'],$dt['mday'],$dt['year']);
if($ts===false || $ts===-1)
  return time();
else
  return $ts;
}

function getTimestamp($year=0,$month=0,$day=0,$hour=-1,$minute=-1,$second=-1)
{
$vars=array('_year'   => $year,
            '_month'  => $month,
	    '_day'    => $day,
	    '_hour'   => $hour,
	    '_minute' => $minute,
	    '_second' => $second);
return composeDateTime(time(),$vars,'');
}
?>
