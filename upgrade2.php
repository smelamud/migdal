<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/sql.php');
require_once('lib/text-wiki.php');
require_once('lib/utils.php');
require_once('lib/old-ids.php');
require_once('lib/charsets.php');
require_once('lib/modbits.php');
require_once('lib/grps.php');
require_once('lib/votes.php');
require_once('lib/images.php');
require_once('lib/image-types.php');
require_once('lib/image-upload.php');
require_once('lib/answers.php');
require_once('lib/users.php');
require_once('lib/catalog.php');
require_once('lib/topics.php');
require_once('lib/structure.php');
require_once('lib/catalog.php');
require_once('lib/cross-entries.php');

define('COMPL_NORMAL',1);
define('COMPL_FORUM',3);
define('COMPL_POSTING',4);

$complainIds=array(0 => 0);
$topicIds=array(0 => 0);
$postingIds=array(0 => 0);
$messageIds=array(0 => 0);
$stotextIds=array(0 => 0);
$imageIds=array(0 => 0);
$forumIds=array(0 => 0);
$maxImage=0;

function convertChatMessages()
{
$result=sql("select id,text,sent
             from chat_messages",
	    __FUNCTION__,'select');
while(list($id,$text,$sent)=mysql_fetch_array($result))
     {
     echo $id,' ';
     $text=unhtmlentities($text);
     $dtext=wikiToXML($text,TF_PLAIN,MTEXT_LINE);
     sql(makeUpdate('chat_messages',
                    array('text'         => $text,
		          'text_xml' => $dtext,
			  'sent'         => $sent),
		    array('id' => $id)),
	 __FUNCTION__,'update');
     }
echo "\n";
}

function truncateEntries()
{
sql('truncate table old_ids',
    __FUNCTION__,'old_ids');
sql('truncate table entries',
    __FUNCTION__,'entries');
sql('truncate table entry_grps',
    __FUNCTION__,'entry_grps');
sql('update image_files
     set max_id=0',
    __FUNCTION__,'image_files');
sql('truncate table cross_entries',
    __FUNCTION__,'cross_entries');
}

function convertComplains()
{
global $complainIds,$messageIds,$stotextIds;

$result=sql("select complains.id as id,recipient_id,message_id,type_id,link,
             closed,no_auto,subject,stotext_id,sender_id,group_id,perms,
	     sent,body
             from complains
	          left join messages
		       on complains.message_id=messages.id
		  left join stotexts
		       on messages.stotext_id=stotexts.id
	     order by id",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $subject=unhtmlentities($row['subject']);
     $body=unhtmlentities($row['body']);
     sql(makeInsert('entries',
                    array('entry' => ENT_COMPLAIN,
		          'grp' => $row['type_id'],
			  'link' => $row['link'],
			  'person_id' => $row['recipient_id'],
			  'user_id' => $row['sender_id'],
			  'group_id' => $row['group_id'],
			  'perms' => $row['perms'],
			  'subject' => $subject,
			  'subject_sort' => convertSort($subject),
			  'body' => $body,
			  'body_xml' => wikiToXML($body,TF_MAIL,MTEXT_SHORT),
			  'body_format' => TF_MAIL,
			  'sent' => $row['sent'],
			  'created' => $row['sent'],
			  'modified' => $row['closed']!='' ? $row['closed']
			                                   : $row['sent'],
			  'modbits' => ($row['closed']!='' ? MODC_CLOSED
			                                   : MODC_NONE) |
				       ($row['no_auto'] ? MODC_NO_AUTO
				                        : MODC_NONE)
			  )),
	 __FUNCTION__,'insert');

     $id=sql_insert_id();
     $complainIds[$row['id']]=$id;
     $messageIds[$row['message_id']]=$id;
     $stotextIds[$row['stotext_id']]=$id;
     putOldId($id,'complains',$row['id']);
     putOldId($id,'messages',$row['message_id']);
     putOldId($id,'stotexts',$row['stotext_id']);
     updateTracks('entries',$id,false);
     }
echo "\n";
}

function convertGrp($entry_id,$grp)
{
for($current=1;$current<0x20000;$current<<=1)
   if(($current & $grp)!=0)
     sql(makeInsert('entry_grps',
                    array('entry_id' => $entry_id,
		          'grp' => $current)),
	 __FUNCTION__);
}

function convertTopics()
{
global $topicIds,$stotextIds;

$result=sql("select topics.id as id,up,name,comment0,comment1,user_id,group_id,
	     perms,stotext_id,body,allow,premoderate,moderate,edit,ident,
	     index0,index1,index4
             from topics
		  left join stotexts
		       on topics.stotext_id=stotexts.id
	     order by track",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $name=unhtmlentities($row['name']);
     $body=unhtmlentities($row['body']);
     $comment0=unhtmlentities($row['comment0']);
     $comment1=unhtmlentities($row['comment1']);
     $now=date('Y-m-d H:i:s',time());
     sql(makeInsert('entries',
                    array('entry' => ENT_TOPIC,
		          'ident' => $row['ident']!='' ? $row['ident'] : NULL,
                          'up' => $topicIds[$row['up']],
			  'user_id' => $row['user_id'],
			  'group_id' => $row['group_id'],
			  'perms' => $row['perms'],
			  'subject' => $name,
			  'subject_sort' => convertSort($name),
			  'comment0' => $comment0,
			  'comment0_xml' => wikiToXML($comment0,
			                              TF_PLAIN,MTEXT_LINE),
			  'comment1' => $comment1,
			  'comment1_xml' => wikiToXML($comment1,
			                              TF_PLAIN,MTEXT_LINE),
			  'body' => $body,
			  'body_xml' => wikiToXML($body,TF_PLAIN,MTEXT_SHORT),
			  'body_format' => TF_PLAIN,
			  'index0' => $row['index0'],
			  'index1' => $row['index1'],
			  'index2' => $row['index4'],
			  'sent' => $now,
			  'created' => $now,
			  'modified' => $now,
			  'modbits' => ($row['premoderate'] ? MODT_PREMODERATE
			                                    : MODT_NONE) |
			               ($row['moderate'] ? MODT_MODERATE
			                                 : MODT_NONE) |
			               ($row['edit'] ? MODT_EDIT
			                             : MODT_NONE)
			  )),
	 __FUNCTION__,'insert');

     $id=sql_insert_id();
     $topicIds[$row['id']]=$id;
     $stotextIds[$row['stotext_id']]=$id;
     putOldId($id,'topics',$row['id'],$row['ident']);
     putOldId($id,'stotexts',$row['stotext_id']);
     updateTracks('entries',$id,false);
     convertGrp($id,$row['allow']);
     }
echo "\n";
}

function convertRegularPostings()
{
global $topicIds,$postingIds,$messageIds,$stotextIds;

$result=sql("select postings.id as id,ident,message_id,up,lang,subject,author,
                    source,comment0,comment1,stotext_id,body,large_filename,
		    large_format,large_body,sender_id,group_id,perms,disabled,
		    modbits,sent,url_domain,url,url_check,url_check_success,
		    last_updated,topic_id,personal_id,grp,priority,last_read,
		    vote,vote_count,index0,index1,index2
             from postings
	          left join messages
		       on postings.message_id=messages.id
		  left join stotexts
		       on messages.stotext_id=stotexts.id
	     where shadow=0
	     order by messages.track",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $subject=unhtmlentities($row['subject']);
     if(!in_array($row['grp'],grpArray(GRP_GRAPHICS)))
       {
       $body=unhtmlentities($row['body']);
       $title='';
       }
     else
       {
       $body='';
       $title=unhtmlentities($row['body']);
       }
     $large_body=unhtmlentities($row['large_body']);
     $author=unhtmlentities($row['author']);
     $source=unhtmlentities($row['source']);
     $comment0=unhtmlentities($row['comment0']);
     $comment1=unhtmlentities($row['comment1']);
     if($row['grp']==GRP_LIBRARY_NOVELTIES)
       {
       $comment0=$source;
       $source='';
       }
     $now=date('Y-m-d H:i:s',time());
     sql(makeInsert('entries',
                    array('entry' => ENT_POSTING,
		          'ident' => $row['ident']!='' ? 'post.'.$row['ident']
			                               : NULL,
                          'up' => $row['up'] ? $messageIds[$row['up']]
			                     : $topicIds[$row['topic_id']],
			  'parent_id' => $topicIds[$row['topic_id']],
			  'grp' => $row['grp'],
			  'person_id' => $row['personal_id'],
			  'user_id' => $row['sender_id'],
			  'group_id' => $row['group_id'],
			  'perms' => $row['perms'],
			  'disabled' => $row['disabled'],
			  'subject' => $subject,
			  'subject_sort' => convertSort($subject),
			  'lang' => $row['lang'],
			  'author' => $author,
			  'author_xml' => wikiToXML($author,TF_MAIL,MTEXT_LINE),
			  'source' => $source,
			  'source_xml' => wikiToXML($source,TF_MAIL,MTEXT_LINE),
			  'title' => $title,
			  'title_xml' => $title!=''
			                 ? wikiToXML($title,TF_MAIL,MTEXT_SHORT)
					 : '',
			  'comment0' => $comment0,
			  'comment0_xml' => wikiToXML($comment0,
			                              TF_MAIL,MTEXT_LINE),
			  'comment1' => $comment1,
			  'comment1_xml' => wikiToXML($comment1,
			                              TF_MAIL,MTEXT_LINE),
			  'url' => $row['url'],
			  'url_domain' => $row['url_domain'],
			  'url_check' => $row['url_check'],
			  'url_check_success' => $row['url_check_success'],
			  'body' => $body,
			  'body_xml' => $body!=''
					? wikiToXML($body,TF_MAIL,MTEXT_SHORT)
					: '',
			  'body_format' => TF_MAIL,
			  'has_large_body' => $large_body!='' ? 1 : 0,
			  'large_body' => $large_body,
			  'large_body_xml' => $large_body!=''
					      ? wikiToXML($large_body,
					                  $row['large_format'],
							  MTEXT_LONG)
					      : '',
			  'large_body_format' => $row['large_format'],
			  'large_body_filename' => $row['large_filename'],
			  'priority' => $row['priority'],
			  'index0' => $row['index0'],
			  'index1' => $row['index1'],
			  'index2' => $row['index2'],
			  'vote' => $row['vote'],
			  'vote_count' => $row['vote_count'],
			  'rating' => getRating($row['vote'],$row['vote_count']),
			  'sent' => $row['sent'],
			  'created' => $row['sent'],
			  'modified' => $row['last_updated'],
			  'accessed' => $row['last_read'],
			  'modbits' => $row['modbits'] & MOD_ALL
			  )),
	 __FUNCTION__,'insert');

     $id=sql_insert_id();
     $postingIds[$row['id']]=$id;
     $messageIds[$row['message_id']]=$id;
     $stotextIds[$row['stotext_id']]=$id;
     putOldId($id,'postings',$row['id'],$row['ident']);
     putOldId($id,'messages',$row['message_id']);
     putOldId($id,'stotexts',$row['stotext_id']);
     updateTracks('entries',$id,false);
     }
echo "\n";
# Не вставлять тени до выполнения этого!
sql('update entries
     set orig_id=id
     where entry='.ENT_POSTING,__FUNCTION__,'original');
}

function convertShadowPostings()
{
global $topicIds,$postingIds,$messageIds;

$result=sql("select postings.id as id,ident,message_id,topic_id,grp,index0
             from postings
	     where shadow<>0
	     order by id",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $oresult=sql("select *
		   from entries
		   where id=".$messageIds[$row['message_id']],
		  __FUNCTION__,'original');
     if(mysql_num_rows($oresult)<=0)
       bug('No original for '.$row['id']);
     $orig=mysql_fetch_assoc($oresult);
     sql(makeInsert('entries',
                    array('entry' => ENT_POSTING,
		          'ident' => $row['ident']!='' ? 'post.'.$row['ident']
			                               : NULL,
			  'up' => $topicIds[$row['topic_id']],
			  'parent_id' => $topicIds[$row['topic_id']],
			  'orig_id' => $orig['id'],
			  'grp' => $row['grp'],
			  'person_id' => $orig['person_id'],
			  'user_id' => $orig['user_id'],
			  'group_id' => $orig['group_id'],
			  'perms' => $orig['perms'],
			  'disabled' => $orig['disabled'],
			  'subject_sort' => $orig['subject_sort'],
			  'lang' => $orig['lang'],
			  'priority' => $orig['priority'],
			  'index0' => $row['index0'],
			  'index1' => $orig['index1'],
			  'index2' => $orig['index2'],
			  'vote' => $orig['vote'],
			  'vote_count' => $orig['vote_count'],
			  'rating' => $orig['rating'],
			  'sent' => $orig['sent'],
			  'created' => $orig['created'],
			  'modified' => $orig['modified'],
			  'accessed' => $orig['accessed'],
			  'modbits' => $orig['modbits'] & MOD_ALL
			  )),
	 __FUNCTION__,'insert');

     $id=sql_insert_id();
     $postingIds[$row['id']]=$id;
     putOldId($id,'postings',$row['id'],$row['ident']);
     updateTracks('entries',$id,false);
     }
echo "\n";
}

function extractImage($id,$row,$createMain)
{
global $maxImage,$imageDir,$thumbnailType;

$maxImage++;
$large=$maxImage;
$size=$row['has_large'] ? 'large' : 'small';
$format=$row['format'];
$content=$row[$size];
$large_size=strlen($content);
$largeName=getImageFilename($id,getImageExtension($format),$large,$size);
$fname="$imageDir/$largeName";
$fd=fopen($fname,'w');
fwrite($fd,$content);
fclose($fd);

if($row['has_large'])
  {
  $maxImage++;
  $small=$maxImage;
  $smallName=getImageFilename($id,getImageExtension($thumbnailType),
			      $small,'small');
  $fname="$imageDir/$smallName";
  $result=imageFileResize("$imageDir/$largeName",$format,$fname,
                          $row['small_x'],$row['small_y']);
  if($result==IFR_OK)
    list($row['small_x'],$row['small_y'])=getImageSize($fname);
  else
    echo "Resize error: $result\n";
  }
if(!$row['has_large'] || $result==IFR_SMALL)
  {
  $small=$large;
  $large=0;
  $smallName=$largeName;
  $largeName=getImageFilename($id,getImageExtension($format),$small,'large');
  @unlink("$imageDir/$largeName");
  symlink($smallName,"$imageDir/$largeName");
  }
if($createMain)
  {
  $smallMainName=getImageFilename($id,getImageExtension($thumbnailType),
				  0,'small');
  @unlink("$imageDir/$smallMainName");
  symlink($smallName,"$imageDir/$smallMainName");
  $mainName=getImageFilename($id,getImageExtension($format));
  @unlink("$imageDir/$mainName");
  symlink($largeName,"$imageDir/$mainName");
  }
return array($small,$row['small_x'],$row['small_y'],$large,$large_size);
}

function convertPostingImages()
{
global $stotextIds,$imageIds,$maxImage;

$usedEntries=array();
$result=sql("select stotexts.id as id,stotexts.image_set as image_set,
                    images.id as image_id,filename,small,small_x,small_y,
		    has_large,large,large_x,large_y,images.format as format,
		    title
             from stotexts
	          left join images
		       on stotexts.image_set=images.image_set
	     where stotexts.image_set<>0 and images.id is not null",
	     __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $id=$stotextIds[$row['id']];
     if($id=='')
       {
       echo "Unknown stotext({$row['id']})\n";
       continue;
       }
     if($usedEntries[$id])
       {
       echo "Extra image for entry $id: images({$row['image_id']})\n";
       continue;
       }
     list($small,$small_x,$small_y,
          $large,$large_size)=extractImage($id,$row,true);
     
     $title=unhtmlentities($row['title']);
     if($title!='')
       $update=array('title' => $title,
  		     'title_xml' => $title!=''
				    ? wikiToXML($title,TF_MAIL,MTEXT_SHORT)
				    : ''
		    );
     else
       $update=array();
     $update=array_merge($update,
			 array('small_image' => $small,
			       'small_image_x' => $small_x,
			       'small_image_y' => $small_y,
			       'large_image' => $large,
			       'large_image_x' => $row['large_x'],
			       'large_image_y' => $row['large_y'],
			       'large_image_size' => $large_size,
			       'large_image_format' => $row['format'],
			       'large_image_filename' => $row['filename']
			       ));
     sql(makeUpdate('entries',
                    $update,
		    array('id' => $id)),
	 __FUNCTION__,'update');
     $usedEntries[$id]=true;
     $imageIds[$row['image_id']]=$id;
     putOldId($id,'images',$row['image_id']);
     }
echo "\n";
}

function convertArticleImages()
{
global $stotextIds,$imageIds,$maxImage;

$result=sql("select stotexts.id as stotext_id,
                    stotexts.large_imageset as image_set,images.id as image_id,
		    filename,small,small_x,small_y,has_large,
		    large,large_x,large_y,images.format as format,title
             from stotexts
	          left join images
		       on stotexts.large_imageset=images.image_set
	     where stotexts.large_imageset<>0 and images.id is not null",
	     __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['stotext_id'],' ';
     $parent_id=$stotextIds[$row['stotext_id']];
     if($parent_id=='')
       {
       echo "Unknown stotext({$row['stotext_id']})\n";
       continue;
       }
     $title=unhtmlentities($row['title']);
     $now=date('Y-m-d H:i:s',time());
     sql(makeInsert('entries',
                    array('entry' => ENT_IMAGE,
		          'up' => $parent_id,
		          'title' => $title,
         		  'title_xml' => $title!=''
					 ? wikiToXML($title,TF_MAIL,MTEXT_SHORT)
					 : '',
			  'created' => $now,
			  'modified' => $now,
			  'accessed' => $now
			 )),
	 __FUNCTION__,'insert');
     $id=sql_insert_id();

     list($small,$small_x,$small_y,
          $large,$large_size)=extractImage($id,$row,false);
     sql(makeUpdate('entries',
	            array('small_image' => $small,
			  'small_image_x' => $small_x,
			  'small_image_y' => $small_y,
			  'large_image' => $large,
			  'large_image_x' => $row['large_x'],
			  'large_image_y' => $row['large_y'],
			  'large_image_size' => $large_size,
			  'large_image_format' => $row['format'],
			  'large_image_filename' => $row['filename']
			 ),
		    array('id' => $id)),
	 __FUNCTION__,'update');

     $imageIds[$row['image_id']]=$id;
     putOldId($id,'images',$row['image_id']);
     updateTracks('entries',$id,false);
     }
echo "\n";
}

function convertForums()
{
global $forumIds,$messageIds,$stotextIds;

$result=sql("select forums.id as id,parent_id,message_id,stotext_id,sender_id,
                    group_id,perms,disabled,sent,last_updated,body
             from forums
	          left join messages
		       on forums.message_id=messages.id
		  left join stotexts
		       on messages.stotext_id=stotexts.id
	     order by messages.track",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $body=unhtmlentities($row['body']);
     $parent_id=$messageIds[$row['parent_id']];
     if($parent_id=='')
       {
       echo "Unknown message({$row['parent_id']})\n";
       continue;
       }
     sql(makeInsert('entries',
                    array('entry' => ENT_FORUM,
                          'up' => $parent_id,
			  'parent_id' => $parent_id,
			  'user_id' => $row['sender_id'],
			  'group_id' => $row['group_id'],
			  'perms' => $row['perms'],
			  'disabled' => $row['disabled'],
			  'body' => $body,
			  'body_xml' => $body!=''
					? wikiToXML($body,TF_MAIL,MTEXT_SHORT)
					: '',
			  'body_format' => TF_MAIL,
			  'sent' => $row['sent'],
			  'created' => $row['sent'],
			  'modified' => $row['last_updated']
			  )),
	 __FUNCTION__,'insert');

     $id=sql_insert_id();
     $forumIds[$row['id']]=$id;
     $messageIds[$row['message_id']]=$id;
     $stotextIds[$row['stotext_id']]=$id;
     putOldId($id,'forums',$row['id']);
     putOldId($id,'messages',$row['message_id']);
     putOldId($id,'stotexts',$row['stotext_id']);
     updateTracks('entries',$id,false);
     }
echo "\n";
}

function convertComplainLinks()
{
global $complainIds,$postingIds,$forumIds;

$result=sql("select id,type_id,link
             from complains
	     where type_id=".COMPL_FORUM." or type_id=".COMPL_POSTING,
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     sql(makeUpdate('entries',
                    array('link' => $row['type_id']==COMPL_FORUM
		                    ? $forumIds[$row['link']]
		                    : $postingIds[$row['link']]),
		    array('id' => $complainIds[$row['id']])),
	 __FUNCTION__,'update');
     }
echo "\n";
}

function convertLinkedTable($table_name,$old_col,$new_col,&$xlat)
{
$result=sql("select distinct $old_col as old_id
             from $table_name",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     $old_id=$row['old_id'];
     echo "$old_id ";
     if(!isset($xlat[$old_id]))
       {
       echo "Unknown $old_col($old_id)\n";
       continue;
       }
     $id=$xlat[$old_id];
     sql("update $table_name
          set $new_col=$id
	  where $old_col=$old_id",
	 __FUNCTION__,'update');
     }
echo "\n";
}

function convertUsers()
{
$fields=array('migdal_student' => USR_MIGDAL_STUDENT,
              'accepts_complains' => USR_ACCEPTS_COMPLAINS,
	      'rebe' => USR_REBE,
	      'admin_users' => USR_ADMIN_USERS,
              'admin_topics' => USR_ADMIN_TOPICS,
	      'admin_complain_answers' => USR_ADMIN_COMPLAIN_ANSWERS,
	      'moderator' => USR_MODERATOR,
	      'judge' => USR_JUDGE,
	      'admin_domain' => USR_ADMIN_DOMAIN);

$result=sql("select id,migdal_student,accepts_complains,rebe,admin_users,
                    admin_topics,admin_complain_answers,moderator,judge,
		    admin_domain,info
	     from users",
	    __FUNCTION__,'select');

while($row=mysql_fetch_assoc($result))
     {
     echo $row['id'],' ';
     $info=unhtmlentities($row['info']);
     $rights=0;
     foreach($fields as $field => $flag)
            if($row[$field])
	      $rights|=$flag;
     sql(makeUpdate('users',
                    array('rights' => $rights,
		          'info' => $info,
			  'info_xml' => wikiToXML($info,TF_PLAIN,MTEXT_SHORT)
			 ),
		    array('id' => $row['id'])),
	 __FUNCTION__,'update');
     }
echo "\n";
}

function parseEntryAttrs($s)
{
$s.=',';
$attrs=array();
$quote=0;
$prev=0;
for($i=0;$i<strlen($s);$i++)
   switch($s{$i})
         {
	 case '"':
	      $quote=1-$quote;
	      break;
	 case ',':
	      if($quote)
	        break;
	      $c=trim(substr($s,$prev,$i-$prev));
	      if($c!='')
	        {
		$pos=strpos($c,'=');
		if($pos==false)
		  {
		  $name=trim($c);
		  $value='1';
		  }
		else
		  {
		  $name=trim(substr($c,0,$pos));
		  $value=trim(substr($c,$pos+1));
		  if($value!='' && $value{0}=='"')
		    $value=substr($value,1,-1);
		  }
		$attrs[$name]=$value;
		}
	      $prev=$i+1;
              break;
	 }
return $attrs;
}

function processGrpAttr(&$grp,$expr)
{
$prev=0;
for($i=0;$i<=strlen($expr);$i++)
   {
   if($i<strlen($expr) && $expr{$i}!='+' && $expr{$i}!='-')
     continue;
   $term=trim(substr($expr,$prev,$i-$prev));
   if($term=='')
     continue;
   if($term{0}=='+' || $term{0}=='-')
     $name=trim(substr($term,1));
   else
     $name=$term;
   eval("\$value=GRP_$name;");
   switch($term{0})
         {
	 case '+':
	      if(!in_array($value,$grp))
	        $grp[]=$value;
              break;
	 case '-':
	      $key=array_search($value,$grp);
	      if($key!==false)
	        unset($grp[$key]);
	      break;
	 default:
	      $grp=array($value);
	 }
   $prev=$i;
   }
}

function convertIdents()
{
$fd=fopen('upgrade2-idents.txt','r');
while(!feof($fd))
     {
     $s=chop(fgets($fd,4096));
     if($s=='')
       continue;
     list($table,$oldId,$ident,$other)=explode("\t",$s);
     $attrs=parseEntryAttrs($other);
     $id=getNewId($table,$oldId);
     echo "$oldId ";
     $vars=array('ident' => $ident);
     if(isset($attrs['body']))
       {
       $bodyXML=wikiToXML($attrs['body'],TF_PLAIN,MTEXT_SHORT);
       $vars=array_merge($vars,
                         array('body'     => $attrs['body'],
			       'body_xml' => $bodyXML));
       }
     sql(makeUpdate('entries',
                    $vars,
		    array('id' => $id)),
         __FUNCTION__,'update');
     if(isset($attrs['grp']))
       {
       $grps=getGrpsByEntryId($id);
       processGrpAttr($grps,$attrs['grp']);
       setGrpsByEntryId($id,$grps);
       }
     }
fclose($fd);
updateCatalogs(0);
echo "\n";
}

function convertMigdalNews()
{
$news=array('.beitenu','.mazltov','.museum','');
foreach($news as $mid)
       {
       $id=idByIdent("migdal$mid.news");
       if($id<=0)
         continue;
       echo "$id ";
       $destid=idByIdent("migdal$mid");
       deleteTopic($id,$destid);
       }
echo "\n";
}

function printArray($f,$row)
{
$s='';
ksort($row);
foreach($row as $key => $value)
       {
       if($s!='')
         $s.="\t";
       $s.="$key: $value";
       }
fputs($f,"$s\n");
}

function convertCrossEntries()
{
$log=fopen('upgrade2-cross.log','a+');
fputs($log,"\n".date('r')."\tStarted conversion\n");

$result=sql("select sources.entry_id as source_id,topic_grp as source_grp,
                    peers.entry_id as peer_id,peer_grp
             from cross_topics
                  left join old_ids as sources
		       on cross_topics.topic_id=sources.old_id and
                          sources.table_name='topics'
                  left join old_ids as peers
		       on cross_topics.peer_id=peers.old_id and
                          peers.table_name='topics'",
	    __FUNCTION__,'select');
while($row=mysql_fetch_assoc($result))
     {
     fputs($log,"\n");
     echo $row['source_id'],'-',$row['source_grp'],'-',$row['peer_grp'],'-';

     $row['source_ident']=identById($row['source_id']);
     $row['peer_ident']=identById($row['peer_id']);

     printArray($log,$row);

     $cross=array('link_type' => LINKT_SEEALSO);

     if(($row['source_grp']==GRP_LINKS || $row['source_grp']==GRP_NEWS
         || $row['source_grp']==GRP_ARTICLES) && $row['source_ident']!='major'
	&& $row['source_ident']!='major_gallery')
       $cross['source_id']=$row['source_id'];
     if($row['source_grp']==GRP_BOOKS
        && $row['source_ident']=='migdal.methodology')
       $cross['source_name']='migdal.methodology.books';
     if($row['source_ident']=='major')
       {
       $cross['source_name']='major';
       $cross['link_type']=LINKT_MAJOR;
       }
     if($row['source_ident']=='major-gallery')
       {
       $cross['source_name']='gallery.major';
       $cross['link_type']=LINKT_MAJOR;
       }
     if($row['source_grp']==GRP_LIBRARY_NOVELTIES)
       $cross['source_name']='migdal.library.novelties';
     if($row['source_grp']==GRP_REVIEWS)
       $cross['source_name']=$row['source_ident'];
     
     if(($row['peer_grp']==GRP_LINKS || $row['peer_grp']==GRP_NEWS
         || $row['peer_grp']==GRP_ARTICLES) && $row['peer_ident']!='major'
	&& $row['peer_ident']!='major_gallery')
       {
       $id=$row['peer_id'];
       $post=new Posting(array('parent_id' => $id,
                               'grp' => $row['peer_grp'],
			       'catalog' => catalog(0,'',catalogById($id))));
       $cross['peer_path']=$post->getGrpGeneralHref();
       }
     if($row['peer_grp']==GRP_BOOKS && $row['peer_ident']=='migdal.methodology')
       $cross['peer_path']='/migdal/methodology/books/';
     if($row['peer_grp']==GRP_LIBRARY_NOVELTIES)
       $cross['peer_path']='/migdal/library/novelties/';
     if($row['peer_grp']==GRP_REVIEWS)
       $cross['peer_path']='/'.catalog($row['peer_id'],$row['peer_ident']);
     
     if(!isset($cross['peer_path'])
        || !isset($cross['source_id']) && !isset($cross['source_name']))
       {
       echo 'N ';
       fputs($log,"* N\n");
       continue;
       }
     
     $info=getLocationInfo($cross['peer_path']);
     if($info->getLinkId()<=0 && $info->getLinkName()=='')
       {
       echo 'L ';
       fputs($log,"peer_path: {$cross['peer_path']}* L\n");
       continue;
       }
     echo 'Y ';
     if($info->getLinkName()!='')
       $cross['peer_name']=$info->getLinkName();
     if($info->getLinkId()>0)
       $cross['peer_id']=$info->getLinkId();
     $cross['peer_icon']=$info->getLinkIcon();
     $cross['peer_subject']=$info->getLinkTitle();
     $cross['peer_subject_sort']=convertSort($info->getLinkTitle());
     printArray($log,$cross);
     sql(makeInsert('cross_entries',
                    $cross),
	 __FUNCTION__,'insert');
     }
echo "\n";
fclose($log);
}

dbOpen();
endJournal();
/*echo "1. Chat messages...\n";
convertChatMessages();
echo "2. Truncate...\n";
truncateEntries();
echo "3. Complains...\n";
convertComplains();
echo "4. Topics...\n";
convertTopics();
echo "5. Regular postings...\n";
convertRegularPostings();
echo "6. Shadow postings...\n";
convertShadowPostings();
echo "7. Posting images...\n";
convertPostingImages();
echo "8. Article images...\n";
convertArticleImages();
setMaxImage($maxImage);
echo "9. Forums...\n";
convertForums();
echo "10. Complain links...\n";
convertComplainLinks();
echo "11. Answer info...\n";
answersRecalculate();
echo "12. Counters...\n";
convertLinkedTable('counters','message_id','entry_id',$messageIds);
echo "13. Packages...\n";
convertLinkedTable('packages','message_id','entry_id',$messageIds);
echo "14. Inner image stotexts...\n";
convertLinkedTable('inner_images','stotext_id','entry_id',$stotextIds);
echo "15. Inner image images...\n";
convertLinkedTable('inner_images','image_id','image_entry_id',$imageIds);
echo "16. Votes...\n";
convertLinkedTable('votes','posting_id','entry_id',$postingIds);
echo "17. Users...\n";
convertUsers();*/
echo "18. Idents...\n";
convertIdents();
/*echo "19. Migdal news...\n";
convertMigdalNews();*/
echo "20. Interlinks...\n";
convertCrossEntries();
beginJournal();
dbClose();
?>
