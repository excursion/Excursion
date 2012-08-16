<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('forums', $section);
$excursion->blockAuth($user['auth_read']);

$update_viewcount = ($user['auth_read'] ? $db->query("UPDATE forums_topics SET viewcount=viewcount+1 WHERE id = $id") : '');
$quote = $excursion->import('quote', 'G', 'INT');

if($action == 'send')
{
	$insert['text'] = $excursion->import('text', 'P', 'HTM');
	if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		$insert['topicid'] = $id;
		$insert['cat'] = $section;
		$insert['posterid'] = (int)$user['id'];
		$insert['creation'] = (int)time();
		
		$db->insert(forums_posts, $insert);
		$post_id = $db->lastInsertId();
		$update_postcount = $db->query("UPDATE forums_topics SET postcount=postcount+1 WHERE id = $id");
		header('Location: forums.php?m=post&section='.$section.'&id='.$id.'#post-'.$post_id);
	}
}
if($action == 'delete')
{
	if($user['auth_admin'])
	{
		$sql_post_delete = $db->delete(forums_posts, "id=$post_id");
		$update_postcount = $db->query("UPDATE forums_topics SET postcount=postcount-1 WHERE id = $id");
	}
	header('Location: forums.php?m=post&section='.$section.'&id='.$id);
}
if($action == 'sticky')
{
	if ($user['auth_admin']) 
	{
		$update['sticky'] = 1;
		$sticky = $db->update(forums_topics, $update, 'id=?', array($id));
	}
	else
	{
		$excursion->reportError('error_insufficient_rights');
	}		
}
if($action == 'unsticky')
{
	if ($user['auth_admin']) 
	{
		$update['sticky'] = 0;
		$sticky = $db->update(forums_topics, $update, 'id=?', array($id));
	}
	else
	{
		$excursion->reportError('error_insufficient_rights');
	}		
}
if($action == 'lock')
{
	if ($user['auth_admin']) 
	{
		$update['locked'] = 1;
		$lock = $db->update(forums_topics, $update, 'id=?', array($id));
	}
	else
	{
		$excursion->reportError('error_insufficient_rights');
	}
}
if($action == 'unlock')
{
	if ($user['auth_admin']) 
	{
		$update['locked'] = 0;
		$lock = $db->update(forums_topics, $update, 'id=?', array($id));
	}
	else
	{
		$excursion->reportError('error_insufficient_rights');
	}
}

$sql = $db->query("SELECT * FROM forums_topics WHERE id='".$id."' LIMIT 1");
$row = $sql->fetch();

$ex['title'] = $row['title'];

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('forums', 'posts')));

if($user['auth_admin'])
{
	$sticky_url = ($row['sticky'] > 0) ? $excursion->url('forums', 'm=post&section='.$section.'&id='.$id.'&action=unsticky') : $excursion->url('forums', 'm=post&section='.$section.'&id='.$id.'&action=sticky');
	$sticky_lang = ($row['sticky'] > 0) ? 'Unsticky' : 'Sticky';
	$lock_url = ($row['locked'] > 0) ? $excursion->url('forums', 'm=post&section='.$section.'&id='.$id.'&action=unlock') : $excursion->url('forums', 'm=post&section='.$section.'&id='.$id.'&action=lock');
	$lock_lang = ($row['locked'] > 0) ? 'Unlock' : 'Lock';
	$delete_url = $excursion->url('forums', 'm=topics&section='.$section.'&id='.$id.'&action=delete');
	$xtpl->assign(array(
		'STICKY' => $excursion->rc('forums_button', array('url' => $sticky_url, 'lang' => $sticky_lang)),
		'LOCK' => $excursion->rc('forums_button', array('url' => $lock_url, 'lang' => $lock_lang)),
		'DELETE' => $excursion->rc('forums_button', array('url' => $delete_url, 'lang' => $lang['del']))
	));
	$xtpl->parse('MAIN.ADMIN');
}

$xtpl->assign(array(
	'TOPIC_TITLE' => $row['title'],
	'SECTION_TITLE' => $structure['forums'][$section]['title']
));

if($user['auth_write'] && $row['locked'] == 0)
{
	if ($quote > 0)
	{
		$sql_quote = $db->query("SELECT id, text, posterid, creation FROM forums_posts
			WHERE topicid = ? AND cat = ? AND id = ? LIMIT 1",
			array($id, $section, $quote));
		if ($row4 = $sql_quote->fetch())
		{
			$insert['text'] = $excursion->rc('forums_quote', array(
				'url' => $excursion->url('forums', 'm=posts&p=' . $row4['id'], '#post-' . $row4['id']),
				'id' => $row4['id'],
				'date' => date($config['date_medium'], $row4['creation']),
				'postername' => $excursion->generateUser($row4['posterid']),
				'text' => $row4['text']
				));
		}
	}
	$xtpl->assign(array(
		'FORM_ACTION' => $excursion->url('forums', 'm=post&section='.$section.'&id='.$id.'&action=send'),
		'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor')
	));
	$xtpl->parse('MAIN.REPLY');
}

$total_posts = $db->query("SELECT COUNT(*) FROM forums_posts WHERE topicid='".$id."' AND cat='".$section."'")->fetchColumn();
$pagination->setLink("forums.php?m=post&section=$section&id=$id&page=%s");
$pagination->setPage($page);
$pagination->setSize($config['maxpages']);
$pagination->setTotalRecords($total_posts);

$sql_post = $db->query("SELECT * FROM forums_posts WHERE topicid='".$id."' AND cat='".$section."' ORDER BY id ASC " . $pagination->getLimitSql());
while ($post = $sql_post->fetch())
{
	$group = $db->query("SELECT groupid FROM members WHERE id='".$post['posterid']."'")->fetchColumn();
	$avatar = $excursion->buildAvatar($post['posterid'], '');
	$delete_url = $excursion->url('forums', 'm=post&section='.$section.'&id='.$id.'&action=delete&post_id='.$post['id'].'');
	$edit_url = $excursion->url('forums', 'm=edit&section='.$section.'&id='.$id.'&post_id='.$post['id'].'');
	$post_admin = ($user['auth_admin'] || $post['posterid'] == $user['id']) ? 
						$excursion->rc('link', array('url' => $delete_url, 'lang' => $lang['del'])) .
						$excursion->rc('link', array('url' => $edit_url, 'lang' => $lang['edit']))
							: 
						'';
						
	if($post['updated'] > 0)
	{
		$xtpl->assign(array(
			'DATE' => date($config['date_medium'], $post['updated']),
			'AUTHOR' => $excursion->generateUser($post['update_by'])
		));
		$xtpl->parse('MAIN.POST.UPDATED');
	}
	
	$postcount = $db->query("SELECT count(*) FROM forums_posts WHERE posterid='".$post['posterid']."'")->fetchColumn();

	/* === Hook === */
	foreach ($excursion->Hook('forums.posts.row') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
	$xtpl->assign(array(
		'ID' => $post['id'],
		'TEXT' => $post['text'],
		'DATE' => date($config['date_medium'], $post['creation']),
		'GROUP' => $excursion->generateGroup($group),
		'AUTHOR' => $excursion->generateUser($post['posterid']),
		'AVATAR' => $avatar,
		'POSTCOUNT' => $postcount,
		'ADMIN' => $post_admin
	));
	$xtpl->parse('MAIN.POST');
}

$navigation = $pagination->create_links();
$xtpl->assign('PAGINATION', $navigation);

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>