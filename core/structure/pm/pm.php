<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$f = (!empty($f) ? $f : 'inbox');
$page = (!empty($page) ? $page : '1');
$pagination->setLink("pm.php?f=$f&page=%s");
$pagination->setPage($page);
$pagination->setSize($config['maxpages']);

if($f == 'inbox')
{
	$total_pm = $db->query("SELECT COUNT(*) FROM pm WHERE touser = '".$user['id']."'")->fetchColumn();
	$sql = $db->query("SELECT * FROM pm WHERE touser = '".$user['id']."' AND tostate < 3 ORDER BY date DESC " . $pagination->getLimitSql());
}
if($f == 'sentbox')
{
	$total_pm = $db->query("SELECT COUNT(*) FROM pm WHERE fromuser = '".$user['id']."'")->fetchColumn();
	$sql = $db->query("SELECT * FROM pm WHERE fromuser = '".$user['id']."' ORDER BY date DESC " . $pagination->getLimitSql());
}

$pagination->setTotalRecords($total_pm);

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('pm', 'list')));

while ($row = $sql->fetch())
{
	$xtpl->assign(array(
		'ID' => $row['id'],
		'FROMUSER' => $excursion->generateUser($row['fromuser']),
		'TOUSER' => $excursion->generateUser($row['touser']),
		'TITLE' => $row['title'],
		'TEXT' => $row['text'],
		'DATE' => date($config['date_medium'], $row['date']),
		'FROMSTATE' => $row['fromstate'],
		'TOSTATE' => $row['tostate']
	));
	$xtpl->parse('MAIN.ROW');	
}

$xtpl->assign('PAGINATION', $navigation = $pagination->create_links());

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>