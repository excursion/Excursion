<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$xtpl = new XTemplate("themes/blog/plugin.polls.admin.xtpl");

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'polls');

if($action == 'save')
{
	$options = $excursion->import('options', 'P', 'ARR');
	$insert['text'] = $excursion->import('title','P','TXT');
	$insert['code'] = $excursion->import('code','P','TXT');
	
	if (empty($insert['text'])) $excursion->reportError('admin_error_title_missing');
	if (empty($insert['code'])) $excursion->reportError('admin_error_code_missing');
	if (!$user['auth_admin']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		$insert['creationdate'] = time();
		$db->insert(polls, $insert);
		$poll_id = $db->lastInsertId();
		
		foreach ($options as $key => $val)
		{
			if (!empty($val))
			{
				$db->insert(polls_options, array(
					'pollid' => $poll_id,
					'text' => $val,
					'count' => 0
				));
			}
		}
		
		header('Location: admin.php?m=tools&plugin='.$plugin);
	}
}
if($action == 'remove')
{
	if (!$user['auth_admin']) $excursion->reportError('error_insufficient_rights');
	if (empty($id)) $excursion->reportError('error_unknown');

	if(!$excursion->error_found())
	{
		$db->delete(polls, "id='".$db->prep($id)."'");
		$db->delete(polls_options, "pollid='".$db->prep($id)."'");
		$db->delete(polls_voters, "pollid='".$db->prep($id)."'");
		header('Location: admin.php?m=tools&plugin='.$plugin);
	}
}
if($action == 'update')
{
	if (!$user['auth_admin']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		$rstructuretitle = $excursion->import('rstructuretitle', 'P', 'ARR');
		$rstructurecode = $excursion->import('rstructurecode', 'P', 'ARR');
		foreach ($rstructuretitle as $i => $k)
		{
			$rstructure['text'] = $excursion->import($rstructuretitle[$i], 'D', 'TXT');
			$rstructure['code'] = $excursion->import($rstructurecode[$i], 'D', 'TXT');
			$db->update(polls, $rstructure, "id=".(int)$i);
		}
		header('Location: admin.php?m=tools&plugin='.$plugin);
	}
}

$sql = $db->query("SELECT * FROM polls ORDER BY id DESC");
while ($row = $sql->fetch())
{
	$structure_id = $row['id'];
	$xtpl->assign(array(
		'FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['text'], 'maxlength="64"', 'input_text_large'),
		'FORM_CODE' => $excursion->inputbox('text', 'rstructurecode['.$structure_id.']', $row['code'], 'maxlength="20"', 'input_text_large'),
		'REMOVE_URL' => $excursion->url('admin', 'm=tools&plugin='.$plugin.'&action=remove&id='.$row['id']),
		'ID' => $row['id'],
	));
	$xtpl->parse('MAIN.ROW');
}

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('admin', 'm=tools&plugin='.$plugin.'&action=save'),
	'FORM_ACTION_UPDATE' => $excursion->url('admin', 'm=tools&plugin='.$plugin.'&action=update'),
	'FORM_CODE' => $excursion->inputbox('text', 'code', '', array('size' => 24, 'maxlength' => 20)),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', '', array('size' => 24, 'maxlength' => 64)),
));

?>