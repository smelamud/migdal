<?php
# @(#) $Id$


function elementEdit($title,$value,$name,$size,$length)
{
return "<tr>
         <td>$title </td>
         <td><input type=text name='$name' value='$value' size=$size
	            maxlength=$length></td>
	</tr>";
}

function condEdit($title,$edit,$value,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<tr><td><b>$title:</b></td><td>$value</td></tr>" : '' :
       elementEdit($title,$value,$name,$size,$length);
}

function condEditStatus($title,$edit,$status,$value,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<tr>
                      <td><b>$title:</b></td><td>$status&nbsp;$value</td>
		     </tr>"
                  : '' :
       "<tr>
         <td>$title </td>
	 <td>$status&nbsp;<input type=text name='$name' value='$value'
	                         size=$size maxlength=$length></td>
	</tr>";
}

function condEditValue($title,$edit,$value,$valueEdit,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<tr><td><b>$title:</b></td><td>$value</td></tr>" : '' :
       elementEdit($title,$valueEdit,$name,$size,$length);
}

function elementCheckBox($name,$value,$title,$span=1)
{
return "<tr><td colspan=$span>
         <input type=checkbox name=$name value=1 ".($value ? 'checked' : '')
                                                 ."> $title
	</td></tr>";
}

function condCheckBox($edit,$name,$value,$title,$textOn='',$textOff='',$span=1)
{
return !$edit ? "<tr><td colspan=$span>
                  <i>".($value ? $textOn : $textOff).'</i>
                 </td></tr>'
              : elementCheckBox($name,$value,$title,$span);
}

function elementOption($label,$value,$peervalue)
{
return "<option label='$label' value=$value".
       ($value==$peervalue ? ' selected ' : '').
       '>';
}

function perror($code,$message,$color='red',$span=1)
{
global $err;

if($err==$code)
  echo "<tr><td colspan=$span><a name='error'>
         <font color='$color'>$message</font>
	</td></tr>";
}

function navigator($list)
{
global $REQUEST_URI;

$prev=$list->getOffset()-$list->getLimit();
$prev=$prev<0 ? 0 : $prev;
$next=$list->getOffset()+$list->getLimit();
$prevURI=remakeURI($REQUEST_URI,
                   array(),
		   array('offset' => $prev));
$nextURI=remakeURI($REQUEST_URI,
                   array(),
		   array('offset' => $next));
return "<br><table width=100%><tr>
         <td align=left width=30%>
	  <a href=$prevURI>".
	  ($list->getOffset()!=0 ? '<-- Предыдущие' : '').
	 '</a>
	 </td>'.
	($list->getCount()==0 ? '' :
	'<td align=center width=40%>
	  ['.($list->getOffset()+1).'-'.
	     ($list->getOffset()+$list->getCount()).'] из '.$list->getSize().
	'</td>').
        "<td align=right width=30%>
	  <a href=$nextURI>".
	  ($list->getOffset()+$list->getCount()<$list->getSize()
	   ? 'Следующие -->' : '').
	 '</a>
	 </td>
	</tr></table><br>';
}

function batcher($current)
{
$s='<table width=100%><tr><td align=right><table><form method=get>
    <tr valign=center>
     <td>Показывать по&nbsp;</td>
     <td><select name="mp">';
$shows=array(10,15,20,25,30,35,40);
$shows[]=$current;
sort($shows);
$shows=array_unique($shows);
foreach($shows as $val)
       $s.="<option label=$val value=$val ".
                               ($val==$current ? 'selected' : '').
	   ' >';
return "  $s
         </select></td>
         <td>сообщений&nbsp;</td>
         <td><input type=submit value='Изменить'></td>
         </tr>
        </form></table></td></tr></table>";
}
?>
