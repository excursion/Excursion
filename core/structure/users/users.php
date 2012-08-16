<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

require_once 'core/header.php';

if(!empty($id))
{
	$xtpl = new XTemplate($excursion->generateTPL(array('users', 'details')));

	$sql = $db->query("SELECT * FROM members WHERE id = $id LIMIT 1");
	$row = $sql->fetch();

	$xtpl->assign(array(
		'ID' => $row['id'],
		'USERNAME' => $row['username'],
		'GROUP' => $excursion->generateGroup($row['groupid']),
		'EMAIL' => $row['email'],
		'AVATAR' => $row['avatar'],
		'REGDATE' => date($config['date_medium'], $row['regdate']),
		'BIRTHDATE' => date($config['date_medium'], $excursion->datetostamp($row['birthdate'])),
		'GENDER' => $lang['gender_' . $row['gender']]
	));

	/* === Hook === */
	foreach ($excursion->Hook('user.details.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */
}
else
{
	$page = (!empty($page) ? $page : '1');
	$total_users = $db->query("SELECT COUNT(*) FROM members")->fetchColumn();
	$pagination->setLink("users.php?page=%s");
	$pagination->setPage($page);
	$pagination->setSize($config['maxpages']);
	$pagination->setTotalRecords($total_users);

	$xtpl = new XTemplate($excursion->generateTPL('users'));

	$sql = $db->query("SELECT * FROM members ORDER BY id ASC " . $pagination->getLimitSql());
	while ($row = $sql->fetch())
	{
		$xtpl->assign(array(
			'ID' => $row['id'],
			'USERNAME' => $row['username'],
			'GROUP' => $excursion->generateGroup($row['groupid']),
			'EMAIL' => $row['email'],
			'REGDATE' => date($config['date_medium'], $row['regdate'])
		));
		$xtpl->parse('MAIN.USERS_LIST');	
	}

	$navigation = $pagination->create_links();
	$xtpl->assign('PAGINATION', $navigation);
}

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>