<?php
# @(#) $Id$

function isLeapJewishYear($year)
{
$n=($year-1)%19;
return $n==2 || $n==5 || $n==7 || $n==10 || $n==13 || $n==16 || $n==18;
}

function getJewishYearLength($year)
{
$jdb=isCalendarAvailable() ? JewishToJD(1,1,$year) : JewishToSDN(1,1,$year);
$jde=isCalendarAvailable() ? JewishToJD(1,1,$year+1) : JewishToSDN(1,1,$year+1);
return $jde-$jdb;
}

function getJewishFromDateDetailed($month,$day,$year,&$jmonth,&$jday,&$jyear)
{
if(isCalendarAvailable())
  list($jmonth,$jday,$jyear)=explode('/',JDToJewish(GregorianToJD($month,$day,$year)));
else
  SDNToJewish(GregorianToSDN($month,$day,$year),$jmonth,$jday,$jyear);
}

function getJewishFromUNIXDetailed($time,&$jmonth,&$jday,&$jyear)
{
if(isCalendarAvailable())
  list($jmonth,$jday,$jyear)=explode('/',JDToJewish($time!=0 ? UNIXToJD($time)
                                                             : UNIXToJD()));
else
  SDNToJewish(UNIXToSDN($time),$jmonth,$jday,$jyear);
}

function getJewishAbsoluteDayFromJewish($month,$day,$year)
{
$jdb=isCalendarAvailable() ? JewishToJD(1,1,$year) : JewishToSDN(1,1,$year);
$jde=isCalendarAvailable() ? JewishToJD($month,$day,$year)
                           : JewishToSDN($month,$day,$year);
return $jde-$jdb+1;
}

function getJewishAbsoluteDayFromUNIX($time=0)
{
getJewishFromUNIXDetailed($time,$month,$day,$year);
return getJewishAbsoluteDayFromJewish($month,$day,$year);
}

function getJewishYearDelay($year)
{
$len=getJewishYearLength($year);
return $len<360 ? $len-354 : $len-384;
}

function getJewishMonthLength($month,$year)
{
global $jewMonthLen;

switch($month)
      {
      case 2:
           return getJewishYearDelay($year)==1 ? 30 : 29;
      case 3:
           return getJewishYearDelay($year)==-1 ? 29 : 30;
      case 6:
           return isLeapJewishYear($year) ? 30 : 29;
      default:
           return $jewMonthLen[$month];
      }
}
?>
