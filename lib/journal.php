<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');

function journalFailure($s)
{
bug("journal: $s");
}

$journalSeq=0;

function beginJournal()
{
global $journalSeq;

if($journalSeq!=0)
  bug('Nested sequence detected.');
$journalSeq=-1;
}

function endJournal()
{
global $journalSeq;

if($journalSeq==0)
  bug('No sequence.');
mysql_query("insert into journal(seq) values($journalSeq)")
  or journalFailure('Closing sequence failed.');
$journalSeq=0;
}

function journal($query,$table='',$id=0)
{
global $journalSeq;

if($journalSeq==0)
  bug('No sequence.');
$query=addslashes($query);
mysql_query("insert into journal(seq,result_table,result_id,query)
             values($journalSeq,'$table',$id,'$query')")
  or journalFailure("Query journaling failed: $query");
if($table!='' || $journalSeq<0)
  {
  $id=mysql_insert_id();
  mysql_query('update journal
               set '.($table!='' ? "result_var=$var" : '')
	            .($table!='' && $journalSeq<0 ? ',' : '')
                    .($journalSeq<0 ? "seq=$id" : ''));
    or journalFailure('Set sequence/variable failed.');
  if($journalSeq<0)
    $journalSeq=$id;
  }
}

function journalVar($table,$id)
{
global $replicationMaster;

if($replicationMaster)
  return $id;
$result=mysql_query("select result_var
                     from journal
		     where result_table='$table' and result_id=$id");
if(!$result)
  journalFailure('Variable query failed.');
return mysql_num_rows($result)>0 ? '$'.mysql_result($table,0,0) : $id;
}

function jencode($s)
{
$c='';
for($i=0;$i<strlen($s);$i++)
   if($s[$i]=='$' || $s[$i]=='%')
     $c.='%'.dechex(ord($s[$i]));
   else
     $c.=$s[$i];
return $c;
}

function jtencode($s)
{
$c='';
for($i=0;$i<strlen($s);$i++)
   if(ord($s[$i])>=0 && ord($s[$i])<=32)
     $c.='%'.dechex(ord($s[$i]));
   else
     $c.=$s[$i];
return $c;
}

function jdecode($s)
{
return rawurldecode($s);
}
?>
