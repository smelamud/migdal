<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/limitselect.php');
require_once('lib/text.php');

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
if($journalSeq>0)
  mysql_query("insert into journal(seq) values($journalSeq)")
    or journalFailure('Closing sequence failed.');
$journalSeq=0;
}

function journal($query,$table='',$id=0)
{
global $replicationJournal,$journalSeq;

if(!$replicationJournal)
  return;
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
               set '.($table!='' ? "result_var=$id" : '')
	            .($table!='' && $journalSeq<0 ? ',' : '')
                    .($journalSeq<0 ? "seq=$id" : '').
	     " where id=$id")
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
if($id==0)
  return 0;
$result=mysql_query("select result_var
                     from journal
		     where result_table='$table' and result_id=$id");
if(!$result)
  journalFailure('Variable query failed.');
return mysql_num_rows($result)>0 ? '$'.mysql_result($result,0,0) : $id;
}

function jencode($s)
{
$c='';
for($i=0;$i<strlen($s);$i++)
   if($s[$i]=='$' || $s[$i]=='%')
     $c.=sprintf('%s%02X','%',ord($s[$i]));
   else
     $c.=$s[$i];
return $c;
}

function jtencode($s)
{
$c='';
for($i=0;$i<strlen($s);$i++)
   if(ord($s[$i])>=0 && ord($s[$i])<=32)
     $c.=sprintf('%s%02X','%',ord($s[$i]));
   else
     $c.=$s[$i];
return $c;
}

function jdecode($s)
{
return rawurldecode($s);
}

function jencodeVars($vars,$codings)
{
foreach($codings as $key=>$value)
       if(isset($vars[$key]))
	 if($codings[$key]=='')
	   $vars[$key]=jencode($vars[$key]);
	 else
	   $vars[$key]=journalVar($codings[$key],$vars[$key]);
return $vars;
}

function isSeqClosed($seq)
{
$result=mysql_query("select id
                     from journal
      	             where seq=$seq and query=''")
  or journalFailure("Cannot check, if seq $seq is closed.");
return mysql_num_rows($result)>0;
}

class JournalLine
      extends DataObject
{
var $id;
var $seq;
var $result_table;
var $result_id;
var $result_var;
var $query;
var $sent;

function JournalLine($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getSeq()
{
return $this->seq;
}

function getResultTable()
{
return $this->result_table;
}

function getResultTableTransfer()
{
return $this->getResultTable()!='' ? $this->getResultTable() : '%';
}

function getResultId()
{
return $this->result_id;
}

function getResultVar()
{
return $this->result_var;
}

function getQuery()
{
return $this->query;
}

function getQueryTransfer()
{
return $this->getQuery()!='' ? jtencode($this->getQuery()) : '%';
}

function getQueryShort()
{
global $queryEllipSize;

return ellipsize($this->query,$queryEllipSize);
}

function getSent()
{
return $this->sent;
}

}

class JournalIterator
      extends SelectIterator
{
var $seq;

function JournalIterator($from=0)
{
$this->SelectIterator('JournalLine',
                      "select id,seq,result_table,result_id,result_var,query
		       from journal
		       where seq>$from
		       order by seq,id");
$this->seq=0;
}

function next()
{
$line=SelectIterator::next();
if($line==0)
  return 0;
if($this->seq!=0 && $this->seq!=$line->getSeq()
   && !isSeqClosed($line->getSeq()))
  return 0;
$this->seq=$line->getSeq();
return $line;
}

}

class JournalListIterator
      extends LimitSelectIterator
{

function JournalListIterator($limit=20,$offset=0)
{
$this->LimitSelectIterator('JournalLine',
                           'select id,seq,result_table,result_id,result_var,
			           query,unix_timestamp(sent) as sent
			    from journal
			    order by seq,id',$limit,$offset,
			   'select count(*)
			    from journal');
}

}

function parseJournalTransfer($s)
{
$list=explode("\t",chop($s));
$row=array('id' => $list[0],
           'seq' => $list[1],
	   'result_table' => ($list[2]=='%' ? '' : $list[2]),
	   'result_id' => $list[3],
	   'result_var' => $list[4],
	   'query' => ($list[5]=='' ? 'error'
	                            : ($list[5]=='%' ? '' : $list[5])));
return new JournalLine($row);
}

function getJournalVar($host,$var)
{
mysql_query("update journal_vars
	     set last_read=null
	     where host='$host' and var=$var")
  or journalFailure('Cannot update timestamp of journaled variable.');
$result=mysql_query("select value
                     from journal_vars
		     where host='".addslashes($host)."' and var=$var")
  or journalFailure('Cannot get journaled variable.');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setJournalVar($host,$var,$value)
{
$host=addslashes($host);
$result=mysql_query("select value
                     from journal_vars
		     where host='$host' and var=$var")
  or journalFailure('Cannot check journaled variable.');
if(mysql_num_rows($result)>0)
  mysql_query("update journal_vars
               set value=$value
	       where host='$host' and var=$var")
    or journalFailure('Cannot update journaled variable.');
else
  mysql_query("insert into journal_vars(var,host,value)
               values($var,'$host',$value)")
    or journalFailure('Cannot insert journaled variable.');
}

function subJournalVars($host,$query)
{
$host=addslashes($host);
return preg_replace('/\$([0-9]+)/e',"getJournalVar('$host',\\1)",$query);
}

function clearJournal()
{
mysql_query('insert into journal(seq) values(0)')
  or journalFailure('Cannot insert increment barrier.');
$id=mysql_insert_id();
mysql_query("update journal set seq=$id where id=$id")
  or journalFailure('Cannot update increment barrier.');
$result=mysql_query('select min(they_know)
                     from horisonts')
  or journalFailure('Cannot determine minimal horisont.');
$level=mysql_result($result,0,0);
mysql_query("delete from journal
             where seq<=$level")
  or journalFailure('Cannot delete journal record.');
}

function isJournalEmpty()
{
global $dbName;

$result=mysql_query("select count(*)
                     from $dbName.journal
		     where query<>''")
  or journalFailure('Cannot determine size of journal.');
return mysql_result($result,0,0)==0;
}

function duplicateDatabase()
{
global $dbName;

$tables=array('complain_actions','complains','cross_topics','forums','groups',
              'images','instants','links','messages','multisites','postings',
	      'stotext_images','stotexts','topics','users','votes');
foreach($tables as $table)
       {
       mysql_query("delete from $dbName.$table")
         or journalFailure('Cannot drop non-autoritative table.');
       mysql_query("insert into $dbName.$table select * from $table")
         or journalFailure('Cannot copy autoritative table.');
       }
}
?>
