<?php
# @(#) $Id$

error_reporting(0);

function execute()
{
global $dbhost,$dbuser,$dbpasswd,$dbname,$sql,$result;

$link=mysql_connect($dbhost,$dbuser,$dbpasswd);
if(!$link)
  return;
if(!mysql_select_db($dbname))
  return;
$result=mysql_query($sql);
if(!$result)
  return;
mysql_close($link);
}

if($sql!='')
  execute();

if($dbname=='')
  $dbname='migdal';
if($dbuser=='')
  $dbuser='root';
if($dbhost=='')
  $dbhost='localhost';
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
      <input type=text name='dbname' size=20 value='<?php echo $dbname ?>'>
     </td>
    </tr>
    <tr>
     <td>User</td>
     <td>
      <input type=text name='dbuser' size=20 value='<?php echo $dbuser ?>'>
     </td>
    </tr>
    <tr>
     <td>Password</td>
     <td>
      <input type=password name='dbpasswd' size=20
	     value='<?php echo $dbpasswd ?>'>
     </td>
    </tr>
    <tr>
     <td>Host</td>
     <td>
      <input type=text name='dbhost' size=20 value='<?php echo $dbhost ?>'>
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
 if(mysql_errno()!=0)
   {
   ?>
   <font color=red><?php echo mysql_errno().': '.mysql_error() ?></font>
   <?php
   }
 else
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
 ?>
</body>
</html>
