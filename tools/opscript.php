<?php
# @(#) $Id$

require_once('lib/database.php');
require_once('lib/opscript.php');

dbOpen();
?>
<html>
 <head><title>Migdal - Operation Script</title></head>
 <body>
  <form method=post action='<?php echo $SCRIPT_NAME ?>'>
   <table><tr valign=top>
    <td>
     Script<br>
     <textarea name='script' rows=10 cols=70><?php echo $script ?></textarea><br>
     <input type=submit value='Execute'><input type=reset value='Reset'>
    </td>
    <td>
     Parameter
     <input type=text name='param' value='<?php echo $param ?>'><br>
     Value
     <input type=text name='value' value='<?php echo $value ?>'>
    </td>
   </tr></table>
  </form>
  <hr>
  <pre>
<?php echo opScript($script,array($param => $value)); ?>
  </pre>
 </body>
</html>
<?php
dbClose();
?>
