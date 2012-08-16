<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('forums', $section);
$excursion->blockAuth($user['auth_read']);

if($action == 'delete')
{
	if($user['auth_admin'])
	{
		$sql_topic_delete = $db->delete(forums_topics, "id=$id AND cat='$section'");
		$sql_posts_delete = $db->delete(forums_posts, "topicid=$id AND cat='$section'");
	}
	header('Location: forums.php?m=topics&section='.$section);
}

$sql = $db->query("SELECT * FROM forums_sections WHERE code='".$section."' LIMIT 1");
$row = $sql->fetch();

$ex['title'] = $row['title'];

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('forums', 'topics')));

$xtpl->assign(array(
	'SECTION_CATEGORY' => $structure['forums'][$section]['title'],
	'SECTION_CODE' => $structure['forums'][$section]['code'],
	'SECTION_DESC' => $structure['forums'][$section]['desc']
));

$total_topics = $db->query("SELECT COUNT(*) FROM forums_topics WHERE cat='".$section."'")->fetchColumn();
$pagination->setLink("forums.php?m=topics&section=$section&page=%s");
$pagination->setPage($page);
$pagination->setSize($config['maxpages']);
$pagination->setTotalRecords($total_topics);

$sql_topics = $db->query("SELECT * FROM forums_topics WHERE cat='".$section."' ORDER BY sticky DESC, id DESC " . $pagination->getLimitSql());
while ($top = $sql_topics->fetch())
{
	$icon = 'new';
	$icon = ($top['creationdate'] <= strtotime('-1 week')) ? 'old' : $icon;
	$icon = ($top['postcount'] >= 10) ? 'hot' : $icon;
	$icon = ($top['locked'] > 0) ? 'closed' : $icon;
	$icon = ($top['sticky'] > 0) ? 'sticky' : $icon;
	
	$sql_pos = $db->query("SELECT *
			FROM forums_posts
			WHERE topicid='".$top['id']."'
			ORDER BY updated DESC, creation DESC 
			LIMIT 1");
	$pos = $sql_pos->fetch();

	if($pos)
	{
		$timeago = ($pos['updated'] > 0) ? $pos['updated'] : $pos['creation'];
		$xtpl->assign(array(
			'TIMEAGO' => $excursion->timeGap($timeago),
			'AUTHOR' => $excursion->generateUser($pos['posterid']),
			'POST_ID' => $pos['id'],
			'TOPIC_ID' => $pos['topicid'],
			'SECTION' => $pos['cat']
		));
		$xtpl->parse('MAIN.ROW.LAST_POST');
	}
	
	$xtpl->assign(array(
		'ID' => $top['id'],
		'ICON' => $excursion->rc('forum_icon', array('value' => $icon)),
		'TITLE' => $top['title'],
		'POST_COUNT' => $top['postcount'],
		'VIEW_COUNT' => $top['viewcount'],
	));
	$xtpl->parse('MAIN.ROW');
}

$navigation = $pagination->create_links();
$xtpl->assign('PAGINATION', $navigation);

if(!$sql_topics->rowCount())
{
	$xtpl->parse('MAIN.EMPTY');
}

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>