<?php
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Access Denied');

$info=($_POST && $errors)?Format::input($_POST):array(); //Re-use the post info on error...savekeyboards.org
if($topic && $_REQUEST['a']!='new'){
    $title='Edit Topic';
    $action='update';
    $info=$info?$info:$topic->getInfo();
}else {
   $title='New Help Topic';
   $action='create';
   $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
}
//get the goodies.
$tt=$_REQUEST['tt'];
$prefix=$_REQUEST['prefix'];
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);
$priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
$ticket_types=db_query('SELECT ticket_type_id,ticket_type FROM isost_ticket_type');

?>
<form action="admin.php?t=topics" method="post">
 <input type="hidden" name="do" value="<?=$action?>">
 <input type="hidden" name="a" value="<?=Format::htmlchars($_REQUEST['a'])?>">
 <input type='hidden' name='t' value='topics'>
 <input type="hidden" name="topic_id" value="<?=$info['topic_id']?>">
<table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
    <tr class="header"><td colspan=2><?=$title?></td></tr>
    <tr class="subheader">
        <td colspan=2 >Disabling auto response will overwrite dept settings.</td>
    </tr>
    <tr>
        <th width="20%">Category / Sub Category / Error Code </th>
        <td><input type="text" name="topic" size=45 value="<?=$info['topic']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['topic']?></font></td>
    </tr>
	<tr>
        <th nowrap>Ticket / Call Type</th>
        <td>
            <select name="ticket_type_id">
                <option value=0>Select Type</option>
                <?
                while (list($id,$name) = db_fetch_row($ticket_types)){
                    $selected = ($info['ticket_type_id']==$id)?'selected':''; ?>
                    <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                <?
                }?>
            </select>&nbsp;<font class="error">*&nbsp;<?=$errors['ticket_types']?></font>
        </td>
    </tr>

    <tr>
        <th>New Ticket Priority:</th>
        <td>
            <select name="priority_id">
                <option value=0>Select Priority</option>
                <?
                while (list($id,$name) = db_fetch_row($priorities)){
                    $selected = ($info['priority_id']==$id)?'selected':''; ?>
                    <option value="<?=$id?>"<?=$selected?>><?=$prefix?> <?=$name?></option>
                <?
                }?>
            </select>&nbsp;<font class="error">*&nbsp;<?=$errors['priority_id']?></font>
        </td>
    </tr>
    <tr>
        <th nowrap>New Ticket Department:</th>
        <td>
            <select name="dept_id">
                <option value=0>Select Department</option>
                <?
                while (list($id,$name) = db_fetch_row($depts)){
                    $selected = ($info['dept_id']==$id)?'selected':''; ?>
                    <option value="<?=$id?>"<?=$selected?>><?=$name?> Dept</option>
                <?
                }?>
            </select>&nbsp;<font class="error">*&nbsp;<?=$errors['dept_id']?></font>
        </td>
    </tr>
	<tr>
        <th nowrap>Main Topic:</th>
        <td>
            <select name="parent_topic_id">
                <option value=0>Select Main Topic</option>
                <?
				$sql="SELECT topic_id,topic FROM isost_help_topic where ticket_type_id=$tt and parent_topic_id=0 order by topic";
				$topics= db_query($sql);
                while (list($id,$name) = db_fetch_row($topics)){
				    $selected = ($info['parent_topic_id']==$id)?'selected':''; 
					?>
                    <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
					<?
					$parent_topic_id=$id;
					$sub_sql="SELECT topic_id,topic FROM isost_help_topic where ticket_type_id=$tt and parent_topic_id=$parent_topic_id order by topic";
					$sub_topics= db_query($sub_sql);
					while (list($sub_id,$sub_name) = db_fetch_row($sub_topics)){
					    $selected = ($info['parent_topic_id']==$sub_id)?'selected':'';?>
						<option value="<?=$sub_id?>"<?=$selected?>><?=$name?> - <?=$sub_name?></option>
					<?
					}
                    $selected = ($info['parent_topic_id']==$id)?'selected':''; 
					?>
                    <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                <?}?>
            </select>
			&nbsp;<font class="error">*&nbsp;<?=$errors['parent_topic_id']?></font>
        </td>
    </tr>
	    <tr><th>Topic Status</th>
        <td>
            <input type="radio" name="isactive"  value="1"   <?=$info['isactive']?'checked':''?> />Active
            <input type="radio" name="isactive"  value="0"   <?=!$info['isactive']?'checked':''?> />Disabled
        </td>
    </tr>
    <tr>
        <th nowrap>Auto Response:</th>
        <td>
            <input type="checkbox" name="noautoresp" value=1 <?=$info['noautoresp']? 'checked': ''?> >
                <b>Disable</b> autoresponse for this topic.   (<i>Overwrite Dept setting</i>)
        </td>
    </tr>
	
</table>
<div style="padding-left:220px;">
    <input class="button" type="submit" name="submit" value="Submit">
    <input class="button" type="reset" name="reset" value="Reset">
    <input class="button" type="button" name="cancel" value="Cancel" onClick='window.location.href="admin.php?t=topics"'>
</div>
</form>
