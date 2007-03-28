<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/limitselect.php');
require_once('lib/text.php');
require_once('lib/sql.php');

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
  sql("insert into journal(seq) values($journalSeq)",
      __FUNCTION__);
$journalSeq=0;
}

function journal($query,$table='',$id=0)
{
global $replicationJournal,$journalSeq;

if(!$replicationJournal)
  return;
if($journalSeq==0)
  bug('No sequence.');
$queryS=addslashes($query);
sql("insert into journal(seq,result_table,result_id,query)
     values($journalSeq,'$table',$id,'$queryS')",
    __FUNCTION__,'log');
if($table!='' || $journalSeq<0)
  {
  $id=sql_insert_id();
  sql('update journal
       set '.($table!='' ? "result_var=$id" : '')
	    .($table!='' && $journalSeq<0 ? ',' : '')
	    .($journalSeq<0 ? "seq=$id" : '').
     " where id=$id",
      __FUNCTION__,'seq_var');
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
$result=sql("select result_var
	     from journal
	     where result_table='$table' and result_id=$id",
	    __FUNCTION__);
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
$result=sql("select id
	     from journal
	     where seq=$seq and query=''",
	    __FUNCTION__);
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
parent::DataObject($row);
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

function getQuerySize()
{
return strlen($this->query);
}

function getQuerySizeK()
{
return (int)($this->getQuerySize()/1024);
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
parent::SelectIterator('JournalLine',
		       "select id,seq,result_table,result_id,result_var,query
			from journal
			where seq>$from
			order by seq,id");
$this->seq=0;
}

function next()
{
$line=parent::next();
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
parent::LimitSelectIterator('JournalLine',
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
$hostS=addslashes($host);
sql("update journal_vars
     set last_read=null
     where host='$hostS' and var=$var",
    __FUNCTION__,'timestamp');
$result=sql("select value
	     from journal_vars
	     where host='$hostS' and var=$var",
	    __FUNCTION__,'get');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setJournalVar($host,$var,$value)
{
$hostS=addslashes($host);
$result=sql("select value
	     from journal_vars
	     where host='$hostS' and var=$var",
	    __FUNCTION__,'check');
if(mysql_num_rows($result)>0)
  sql("update journal_vars
       set value=$value
       where host='$hostS' and var=$var",
      __FUNCTION__,'update');
else
  sql("insert into journal_vars(var,host,value)
       values($var,'$hostS',$value)",
      __FUNCTION__,'create');
}

function subJournalVars($host,$query)
{
$hostS=addslashes($host);
return preg_replace('/\$([0-9]+)/e',"getJournalVar('$hostS',\\1)",$query);
}

function clearJournal()
{
sql('insert into journal(seq)
     values(0)',
    __FUNCTION__,'create_barrier');
$id=sql_insert_id();
sql("update journal set seq=$id where id=$id",
    __FUNCTION__,'seq_barrier');
$result=sql('select min(they_know)
	     from horisonts',
	    __FUNCTION__,'get_min_horisont');
$level=mysql_result($result,0,0);
sql("delete from journal
     where seq<=$level",
    __FUNCTION__,'delete_extra');
}

function isJournalEmpty()
{
global $dbName;

$result=sql("select count(*)
	     from $dbName.journal
	     where query<>''",
	    __FUNCTION__);
return mysql_result($result,0,0)==0;
}

function duplicateDatabase()
{
global $dbName;

// FIXME incorrect list of tables
$tables=array('complain_actions','complains','counters','cross_topics',
              'forums','groups','images','instants','messages','multisites',
	      'postings','stotext_images','stotexts','topics','users','votes');
foreach($tables as $table)
       {
       sql("delete from $dbName.$table",
           __FUNCTION__,'drop',"table='$table'");
       sql("insert into $dbName.$table select * from $table",
           __FUNCTION__,'copy',"table='$table'");
       }
}
?>
