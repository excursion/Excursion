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
foreach ($excursion->Hook('forums.newtopic') as $pl)
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
	$topic['title'] = $excursion->import('title', 'P', 'TXT');
	
	/* === Hook === */
	foreach ($excursion->Hook('forums.newtopic.send.first') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
	if (mb_strlen($topic['title']) < 4) $excursion->reportError('error_title_length');
	if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		$topic['creationdate'] = (int)time();
		$topic['cat'] = $section;
		$topic['lastposter'] = (int)$user['id'];
		$topic['firstposter'] = (int)$user['id'];
		$topic['postcount'] = 1;
		$create_topic = $db->insert(forums_topics, $topic);
		$topic_id = $db->lastInsertId();
		$insert['topicid'] = $topic_id;
		$insert['cat'] = $section;
		$insert['posterid'] = (int)$user['id'];
		$insert['creation'] = (int)time();
		
		/* === Hook === */
		foreach ($excursion->Hook('forums.newtopic.send.last') as $pl)
		{
			include $pl;
		}
		/* ===== */
		
		$create_post = $db->insert(forums_posts, $insert);
		$post_id = $db->lastInsertId();
		header('Location: forums.php?m=post&section='.$section.'&id='.$topic_id.'#post-'.$post_id);
	}
}

$ex['title'] = 'New topic';

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('forums', 'newtopic')));

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('forums', 'm=newtopic&section='.$section.'&action=send'),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', $topic['title'], array('size' => '64', 'maxlength' => '255')),
	'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor'),
	'SECTION_TITLE' => $structure['forums'][$section]['title']
));

/* === Hook === */
foreach ($excursion->Hook('forums.newtopic.tags') as $pl)
{
	include $pl;
}
/* ===== */

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>