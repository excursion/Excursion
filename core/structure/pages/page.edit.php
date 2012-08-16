<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$page_cat = $db->query("SELECT cat FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $page_cat);
$excursion->blockAuth($user['auth_write']);

if($action == 'send' && $user['auth_write'])
{
	/* === Hook === */
	foreach ($excursion->Hook('page.edit.send') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$insert['title'] = $excursion->import('title', 'P', 'TXT');
	$insert['desc'] = $excursion->import('desc', 'P', 'TXT');
	$insert['cat'] = $excursion->import('category', 'P', 'TXT');
	$insert['page_file'] = intval($excursion->import('pagefile', 'P', 'INT'));
	$insert['page_url'] = $excursion->import('pageurl', 'P', 'TXT');
	$insert['text'] = $excursion->import('text', 'P', 'HTM');
	
	if (mb_strlen($insert['title']) < 4) $excursion->reportError('error_title_length');
	if (mb_strlen($insert['cat']) < 2) $excursion->reportError('page_error_cat_missing');
	if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
	if ($insert['page_file'] > 0 && empty($insert['page_url'])) $excursion->reportError('error_file_missing');
	if (!empty($insert['page_url']) && !file_exists($insert['page_url'])) $excursion->reportError('error_invalid_file');
	
	if(!$excursion->error_found())
	{
		$db->update(pages, $insert, 'id=?', array($id));
		header('Location: page.php?id='.$id.'');
	}
}

$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
$row = $sql->fetch();

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('page', 'edit', $c)));

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('page', 'id='.$id.'&m=edit&action=send'),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', $row['title'], array('size' => '64', 'maxlength' => '255')),
	'FORM_DESC' => $excursion->inputbox('text', 'desc', $row['desc'], array('size' => '64', 'maxlength' => '255')),
	'FORM_CAT' => $excursion->selectbox_categories($row['cat'], 'category'),
	'FORM_PAGEFILE' => $excursion->selectbox($row['page_file'], 'pagefile', range(0, 2), array($lang['no'], $lang['yes'], $lang['members_only']), false),
	'FORM_PAGEURL' => $excursion->inputbox('text', 'pageurl', $row['page_url'], array('size' => '64', 'maxlength' => '255')),
	'FORM_TEXT' => $excursion->textarea('text', $row['text'], 24, 120, '', 'input_textarea_editor')
));

/* === Hook === */
foreach ($excursion->Hook('page.edit.tags') as $pl)
{
	include $pl;
}
/* ===== */

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>