<?php
# @(#) $Id$

error_reporting(0);

require_once('conf/migdal.conf');

function openDb()
{
global $dbhost,$dbuser,$dbpasswd,$dbname,$link;

$link=mysql_connect($dbhost,$dbuser,$dbpasswd);
if(!$link)
  return false;
if(!mysql_select_db($dbname))
  return false;
return true;
}

function closeDb()
{
mysql_close($link);
}

function executeStatement(&$stat)
{
$q=0;
$qq=0;
for($i=0;$i<strlen($stat);$i++)
   switch($stat[$i])
         {
	 case "'":
	      $q=1-$q;
	      break;
	 case '"':
	      $qq=1-$qq;
	      break;
	 case ';':
	      if($q==0 && $qq==0)
	        {
		$sql=substr($stat,0,$i);
		$stat=substr($stat,$i+1);
		mysql_query($sql);
		return mysql_errno().': '.mysql_error();
		}
	 }
return '';
}

function executeScript()
{
global $script,$result;

if(!openDb())
  {
  $result[]=mysql_errno().': '.mysql_error();
  return;
  }
$result=array();
$file=fopen($script,'r');
if(!$file)
  {
  $result[]='File not found';
  return;
  }
$stat='';
while($line=fgets($file,4096))
     {
     if($line[0]=='#')
       continue;
     $stat.=$line;
     $rs=executeStatement($stat);
     if($rs!='')
       $result[]="$rs\n";
     }
fclose($file);
closeDb();
}

function execute()
{
global $sql,$result;

if(!openDb())
  return;
$result=mysql_query($sql);
if(!$result)
  return;
closeDb();
}

if($script!='')
  executeScript();
if($sql!='')
  execute();

if($dbname=='')
  $dbname=$dbHost;
if($dbuser=='')
  $dbuser=$dbName;
if($dbhost=='')
  $dbhost=$dbUser;
?>
<html>
<head><title>Migdal - SQL</title></head>
<body>
 <form method=post action='<?php echo $SCRIPT_NAME ?>#error'>
  <table>
   <tr><td><table>
    <tr>
     <td>Database</td>
     <td>
      <input type=text name='dbname' size=25 value='<?php echo $dbname ?>'>
     </td>
    </tr>
    <tr>
     <td>User</td>
     <td>
      <input type=text name='dbuser' size=25 value='<?php echo $dbuser ?>'>
     </td>
    </tr>
    <tr>
     <td>Password</td>
     <td>
      <input type=password name='dbpasswd' size=25
	     value='<?php echo $dbpasswd ?>'>
     </td>
    </tr>
    <tr>
     <td>Host</td>
     <td>
      <input type=text name='dbhost' size=25 value='<?php echo $dbhost ?>'>
     </td>
    </tr>
    <tr></tr>
    <tr>
     <td>Script</td>
     <td>
      <input type=text name='script' size=25 value='<?php echo $script ?>'>
     </td>
    </tr>
   </table></td><td><table>
    <tr><td colspan=2>Query</td></tr>
    <tr><td colspan=2>
     <textarea name='sql' rows=10 cols=50><?php echo $sql ?></textarea>
    </td></tr>
    <tr><td colspan=2>
     <input type=submit value='Execute'><input type=reset value='Reset'>
    </td></tr>
   </table></td></tr>
  </table>
 </form>
 <a name='error'>
 <?php
 if($sql!='' && mysql_errno()!=0)
   {
   ?>
   <font color=red><?php echo mysql_errno().': '.mysql_error() ?></font>
   <?php
   }
 elseif($sql!='')
   {
   ?>
   <p>
   <table border=1>
   <tr>
   <?php
   $n=mysql_num_fields($result);
   for($i=0;$i<$n;$i++)
      {
      ?>
      <th>
      <?php echo mysql_field_name($result,$i) ?>
      </th>
      <?
      }
   ?>
   </tr>
   <?php
   while($row=mysql_fetch_row($result))
        {
	?>
	<tr>
	<?php
	foreach($row as $value)
	       {
	       ?>
	       <td><?php echo $value ?></td>
	       <?php
	       }
	?>
	</tr>
	<?php
	}
   ?>
   </table>
   <?php
   }
elseif($script!='')
   {
   ?>
   <pre>
   <?php
   echo "\n";
   foreach($result as $line)
          echo $line;
   ?>
   </pre>
   <?php
   }
 ?>
</body>
</html>
