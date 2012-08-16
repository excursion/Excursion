<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
$row = $sql->fetch();

$ex['title'] = $row['title'];
	
list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $row['cat']);
$excursion->blockAuth($user['auth_read']);

if($row['state'] == '0')
{
	if($user['auth_admin'] || $row['owner'] != $user['id'])
	{
		header('Location: message.php');
	}
}
	
require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('page', $row['cat'])));

$text = $excursion->cut_more($text);

$xtpl->assign(array(
	'ID' => (int) $row['id'],
	'TITLE' => $row['title'],
	'DESC' => $row['desc'],
	'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
	'CAT_CODE' => $row['cat'],
	'OWNER' => $excursion->generateUser($row['owner']),
	'AVATAR' => $excursion->buildAvatar($row['owner'], 'avatar'),
	'DATE' => date($config['date_medium'], $row['date']),
	'TEXT' => $row['text']
));

if($row['page_file'] > 0)
{
	$allowed_dl = false;
	if($row['page_file'] == 1)
	{
		$allowed_dl = true;
		$xtpl->assign(array(
			'FILE_URL' => $excursion->url('page', 'id='.$row['id'].'&action=dl')
		));
		$xtpl->parse('MAIN.PAGE_FILE');
	}
	if($row['page_file'] == 2 && $user['id'] != 0)
	{
		$allowed_dl = true;
		$xtpl->assign(array(
			'FILE_URL' => $excursion->url('page', 'id='.$row['id'].'&action=dl')
		));
		$xtpl->parse('MAIN.PAGE_FILE');
	}
	if($action == 'dl' && $allowed_dl)
	{
		if (file_exists($row['page_url']))
		{
			header('Location: '.$row['page_url']);
		}
	}
}

$row['page_tab'] = empty($page) ? 0 : $page;
$row['page_tabs'] = explode('[newpage]', $xtpl->vars['TEXT'], 99);
$row['page_totaltabs'] = count($row['page_tabs']);

if ($row['page_totaltabs'] > 1)
{
	if (empty($row['page_tabs'][0]))
	{
		$remove = array_shift($row['page_tabs']);
		$row['page_totaltabs']--;
	}
	$max_tab = $row['page_totaltabs'] - 1;
	$row['page_tab'] = ($row['page_tab'] > $max_tab) ? 0 : $row['page_tab'];
	$row['page_tabtitles'] = array();

	for ($i = 0; $i < $row['page_totaltabs']; $i++)
	{
		if (mb_strpos($row['page_tabs'][$i], '<br />') === 0)
		{
			$row['page_tabs'][$i] = mb_substr($row['page_tabs'][$i], 6);
		}

		$p1 = mb_strpos($row['page_tabs'][$i], '[title]');
		$p2 = mb_strpos($row['page_tabs'][$i], '[/title]');

		if ($p2 > $p1 && $p1 < 4)
		{
			$row['page_tabtitle'][$i] = mb_substr($row['page_tabs'][$i], $p1 + 7, ($p2 - $p1) - 7);
			if ($i == $row['page_tab'])
			{
				$row['page_tabs'][$i] = trim(str_replace('[title]'.$row['page_tabtitle'][$i].'[/title]', '', $row['page_tabs'][$i]));
			}
		}
		else
		{
			$row['page_tabtitle'][$i] = $i == 0 ? $row['title'] : 'Page' . ' ' . ($i + 1);
		}
		$tab_url = $excursion->url('page', 'id='.$id.'&page='.$i);
		$row['page_tabtitles'][] .= '<li>' . $excursion->rc_link($tab_url, ($i+1).'. '.$row['page_tabtitle'][$i]) . '</li>';
		$row['page_tabs'][$i] = str_replace('[newpage]', '', $row['page_tabs'][$i]);
		$row['page_tabs'][$i] = preg_replace('#^(<br />)+#', '', $row['page_tabs'][$i]);
		$row['page_tabs'][$i] = trim($row['page_tabs'][$i]);
	}

	$row['page_tabtitles'] = implode($row['page_tabtitles']);
	$row['text'] = $row['page_tabs'][$row['page_tab']];
	
	$xtpl->assign(array(
		'NAV_TABTITLES' => $row['page_tabtitles'],
		'NAV_CURTAB' => $row['page_tab'] + 1,
		'NAV_MAXTAB' => $row['page_totaltabs'],
		'TEXT' => $row['text'],
		'TITLE' => $row['page_tabtitle'][$row['page_tab']]
	));
	$xtpl->parse('MAIN.NAV');
}

/* === Hook === */
foreach ($excursion->Hook('page.tags') as $pl)
{
	include $pl;
}
/* ===== */

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>