<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=standalone
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'search');
$excursion->blockAuth($user['auth_read']);

$query = $excursion->import('query', 'R', 'TXT');
$query = $db->prep($query);
$channel = $excursion->import('channel', 'P', 'TXT');

$page = (!empty($page) ? $page : '1');
$pagination->setLink("plugin.php?p=search&page=%s");
$pagination->setPage($page);
$pagination->setSize($config['maxpages']);

if(!empty($query) && $user['auth_write'])
{
	$words = explode(' ', $query);
	$sqlsearch = '%'.implode('%', $words).'%';
	if (mb_strlen($query) < $config['plugin']['search']['minsigns'])
	{
		$excursion->reportError('search_error_querytooshort');
	}
	if (count($words) > $config['plugin']['search']['maxwords'])
	{
		$excursion->reportError('search_error_toomanywords');
	}
	
	if($channel == 'pages' || empty($channel))
	{
		$where_and['state'] = "state = '1'";
		$where_or['title'] = "title LIKE '".$db->prep($sqlsearch)."'";
		$where_or['text'] = "text LIKE '".$db->prep($sqlsearch)."'";
		$where_or = array_diff($where_or, array(''));
		count($where_or) || $where_or['title'] = "title LIKE '".$db->prep($sqlsearch)."'";
		$where_and['or'] = '('.implode(' OR ', $where_or).')';
		$where_and = array_diff($where_and, array(''));
		$where = implode(' AND ', $where_and);
			
		$sql = $db->query("
			SELECT *
			FROM pages
			WHERE $where
			ORDER BY date ASC " . $pagination->getLimitSql()
		);
			
		$items = $sql->rowCount();
		$totalitems[] = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
		$pc = 0;

		foreach ($sql->fetchAll() as $row)
		{
			$DateTime = new DateTime(date($config['date_medium'], $row['date']));
			
			$xtpl->assign(array(
				'ID' => (int) $row['id'],
				'TITLE' => $row['title'],
				'DESC' => $row['desc'],
				'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
				'CAT_CODE' => $row['cat'],
				'OWNER' => $excursion->generateUser($row['owner']),
				'DATE' => date($config['date_medium'], $row['date']),
				'NEWS_DATE_MONTH' => $DateTime->format( 'F' ),
				'DATE_MONTH_SHORT' => str_replace($numberMonth, $shortMonth, $DateTime->format( 'm' )),
				'DATE_DAY' => $DateTime->format( 'd' ),
				'TEXT' => $row['text'],
				'TEXT_CUT' => $excursion->truncate($row['text'], 200)
			));
			$xtpl->parse('MAIN.RESULTS.PAGES.ROW_PAGES');
			$pc++;
		}
		if (!empty($query))
		{
			$xtpl->assign('PAGE_COUNT', $pc);
		}
		$xtpl->parse('MAIN.RESULTS.PAGES');
		unset($where_and, $where_or, $where);	
	}
	if($channel == 'forums'  || empty($channel))
	{
		$where_or['title'] = "t.title LIKE '".$db->prep($sqlsearch)."'";
		$where_or['text'] = "p.text LIKE '".$db->prep($sqlsearch)."'";
		$where_or = array_diff($where_or, array(''));
		count($where_or) || $where_or['title'] = "(t.title LIKE '".$db->prep($sqlsearch)."'";
		$where_and['or'] = '('.implode(' OR ', $where_or).')';
		$where_and = array_diff($where_and, array(''));
		$where = implode(' AND ', $where_and);
			
		$sql = $db->query("
			SELECT SQL_CALC_FOUND_ROWS p.*, t.*
			FROM forums_posts AS p, forums_topics AS t
			WHERE $where AND p.topicid = t.id
			GROUP BY t.id
			ORDER BY t.creationdate ASC " . $pagination->getLimitSql()
		);
			
		$items = $sql->rowCount();
		$totalitems[] = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
		$fc = 0;

		foreach ($sql->fetchAll() as $row)
		{
			$url = $excursion->url('forums', 'm=post&section='.$row['cat'].'&id='.$row['id']);
			$DateTime = new DateTime(date($config['date_medium'], $row['date']));
			$author = (empty($row['posterid'])) ? $row['lastposter'] : $row['posterid'];
			$xtpl->assign(array(
				'ID' => (int) $row['id'],
				'URL' => $url,
				'TITLE' => $row['title'],
				'CAT' => $row['cat'],
				'OWNER' => $excursion->generateUser($author),
				'TEXT' => $row['text'],
				'TEXT_CUT' => $excursion->truncate($row['text'], 200)
			));
			$xtpl->parse('MAIN.RESULTS.FORUMS.ROW_FORUMS');
			$fc++;
		}
		if (!empty($query))
		{
			$xtpl->assign('FORUM_COUNT', $fc);
		}
		$xtpl->parse('MAIN.RESULTS.FORUMS');
		unset($where_and, $where_or, $where);	
	}
	$xtpl->parse('MAIN.RESULTS');
}

$pagination->setTotalRecords($items);
$navigation = $pagination->create_links();

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('plugin', 'p=search'),
	'FORM_TEXT' => $excursion->inputbox('text', 'query', htmlspecialchars($query), 'size="32"'),
	'PAGINATION' => $navigation
));

$excursion->display_messages($xtpl);

?>