<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'forums';

$xtpl = new XTemplate($excursion->generateTPL('forums', 'admin'));

if($action == 'update')
{
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		$rstructuretitle = $excursion->import('rstructuretitle', 'P', 'ARR');
		$rstructuredesc = $excursion->import('rstructuredesc', 'P', 'ARR');
		$rstructurepath = $excursion->import('rstructurepath', 'P', 'ARR');
		$rstructurecode = $excursion->import('rstructurecode', 'P', 'ARR');

		foreach ($rstructuretitle as $i => $k)
		{
			$oldrow = $db->query("SELECT id FROM forums_sections WHERE id=".(int)$i)->fetch();
			$rstructure['title'] = $excursion->import($rstructuretitle[$i], 'D', 'TXT');
			$rstructure['desc'] = $excursion->import($rstructuredesc[$i], 'D', 'TXT');
			$rstructure['path'] = $excursion->import($rstructurepath[$i], 'D', 'TXT');
			$rstructure['code'] = $excursion->import($rstructurecode[$i], 'D', 'TXT');
			
			$auth_old = $db->query("SELECT code FROM forums_sections WHERE id=".(int)$i)->fetch();
			$auth['area'] = $rstructure['code'];
			
			$db->update(forums_topics, $rstructure, "id=".(int)$i);
			$db->update(auth, $auth, "code='forums' AND area='".$auth_old[code]."'");
			
			$excursion->reorderAuth();
		}
		header('Location: admin.php?m=forums');
	}
}

if($action == 'save')
{
	$insert['title'] = $excursion->import('title','P','TXT');
	$insert['desc'] = $excursion->import('desc','P','TXT');
	$insert['path'] = $excursion->import('path','P','TXT');
	$insert['code'] = $excursion->import('code','P','TXT');

	if (empty($insert['title'])) $excursion->reportError('admin_error_title_missing');
	if (empty($insert['path'])) $excursion->reportError('admin_error_path_missing');
	if (empty($insert['code'])) $excursion->reportError('admin_error_code_missing');
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');

	if(!$excursion->error_found())
	{
		$db->insert(forums_sections, $insert);
		$id = $db->lastInsertId();
		
		$excursion->newAuth('forums', $insert['code']);
		$excursion->reorderAuth();
		
		header('Location: admin.php?m=forums');
	}
}

if($action == 'remove')
{
	if (!$user['auth_admin']) $excursion->reportError('error_insufficient_rights');
	if (empty($id)) $excursion->reportError('error_unknown');

	if(!$excursion->error_found())
	{
		$forum_code = $db->query("SELECT code FROM forums_sections WHERE id='".$id."'")->fetchColumn();
		$db->delete(forums_sections, "id='".$db->prep($id)."'");
		$db->delete(forums_topics, "cat='".$forum_code."'");
		$db->delete(forums_posts, "cat='".$forum_code."'");
		$db->delete(auth, "area='$forum_code' AND code='forums'");
		$excursion->reorderAuth();

		header('Location: admin.php?m=forums');
	}
}

$sql = $db->query("SELECT * FROM forums_sections ORDER BY path ASC");
foreach ($sql->fetchAll() as $row)
{
	$structure_id = $row['id'];

	$xtpl->assign(array(
		'FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['title'], 'maxlength="64"', 'input_text_medium'),
		'FORM_DESC' => $excursion->inputbox('text', 'rstructuredesc['.$structure_id.']', $row['desc'], 'maxlength="85"', 'input_text_medium'),
		'FORM_CODE' => $excursion->inputbox('text', 'rstructurecode['.$structure_id.']', $row['code'], 'maxlength="20"', 'input_text_custom'),
		'FORM_PATH' => $excursion->inputbox('text', 'rstructurepath['.$structure_id.']', $row['path'], 'maxlength="7"', 'input_text_small'),
		'ID' => $row['id'],
		'TITLE' => $row['title'],
		'DESC' => $row['desc'],
		'CODE' => $row['code'],
		'PATH' => $row['path'],
	));
	
	$xtpl->parse('MAIN.ROW');
}

$xtpl->assign(array(
	'FORM_ACTION_SAVE' => $excursion->url('admin', 'm=forums&action=save'),
	'FORM_ACTION_UPDATE' => $excursion->url('admin', 'm=forums&action=update'),
	'FORM_PATH' => $excursion->inputbox('text', 'path', '', array('size' => 24, 'maxlength' => 7)),
	'FORM_CODE' => $excursion->inputbox('text', 'code', '', array('size' => 24, 'maxlength' => 20)),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', '', array('size' => 24, 'maxlength' => 64)),
	'FORM_DESC' => $excursion->inputbox('text', 'desc', '', array('size' => 24, 'maxlength' => 64)),
));

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>