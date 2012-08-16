<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

if($action == 'send')
{
	$insert['title'] = $excursion->import('title', 'P', 'TXT');
	$insert['text'] = $excursion->import('text', 'P', 'HTM');
	$insert['date'] = (int)time();
	$insert['touser'] = $excursion->import('touser', 'P', 'INT');
	$insert['fromuser'] = $user['id'];
	
	$user_exists = (bool)$db->query("SELECT id FROM members WHERE id = ? LIMIT 1", array($insert['touser']))->fetch();
	
	if (mb_strlen($insert['title']) < 2) $excursion->reportError('error_title_length');
	if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
	if (empty($insert['touser']) || !$user_exists) $excursion->reportError('pm_error_touser_notexist');
	
	if(!$excursion->error_found())
	{
		$db->insert(pm, $insert);			
		header('Location: pm.php');
	}
}

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('pm', 'send')));

$insert['title'] = (!empty($title) ? $title : $insert['title']);

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('pm', 'm=send&action=send'),
	'FORM_TOUSER' => $excursion->inputbox('text', 'touser', $id, array('size' => '64', 'maxlength' => '255')),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', $insert['title'], array('size' => '64', 'maxlength' => '255')),
	'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor')
));

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>