<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('forums', $section);
$excursion->blockAuth($user['auth_write']);

/* === Hook === */
foreach ($excursion->Hook('forums.editpost') as $pl)
{
	include $pl;
}
/* ===== */
	
$sql = $db->query("SELECT * FROM forums_posts WHERE id='".$post_id."' LIMIT 1");
$insert = $sql->fetch();
$topic = $db->query("SELECT title FROM forums_topics WHERE id='".$id."'")->fetchColumn();

if($action == 'send')
{
	$insert['text'] = $excursion->import('text', 'P', 'HTM');
	if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		$insert['updated'] = (int)time();
		$insert['update_by'] = (int)$user['id'];
		$edit_post = $db->update(forums_posts, $insert, 'id=?', array($post_id));
		header('Location: forums.php?m=post&section='.$section.'&id='.$id.'#post-'.$post_id);
	}
}

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('forums', 'posts.edit')));

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('forums', 'm=edit&section='.$section.'&id='.$id.'&post_id='.$post_id.'&action=send'),
	'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor'),
	'TOPIC_TITLE' => $topic
));

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>