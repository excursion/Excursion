<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$sql = $db->query("SELECT p.*, m.*
				   FROM pm as p
				   LEFT JOIN members AS m ON p.fromuser=m.id
				   WHERE p.id='$id'
				   LIMIT 1");
$row = $sql->fetch();

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('pm', 'message')));

if($row['fromuser'] == $user['id'] || $row['touser'] == $user['id'])
{
	$xtpl->assign(array(
		'ID' => (int) $row['id'],
		'TITLE' => $row['title'],
		'FROMUSER' => $excursion->generateUser($row['fromuser']),
		'AVATAR' => $excursion->buildAvatar($row['fromuser'], 'avatar photo'),
		'GROUP' => $excursion->generateGroup($row['groupid']),
		'TOUSER' => $excursion->generateUser($row['touser']),
		'FROMUSER_ID' => $row['fromuser'],
		'TOUSER_ID' => $row['touser'],
		'DATE' => date($config['date_medium'], $row['date']),
		'TEXT' => $row['text'],
		'FROMSTATE' => $row['fromstate'],
		'TOSTATE' => $row['tostate']
	));
	
	if($row['fromuser'] == $user['id'])
	{
		$update['fromstate'] = 1;
	}
	if($row['touser'] == $user['id'])
	{
		$update['tostate'] = 1;
	}
	
	$sql_update_state = $db->update(pm, $update, 'id=?', array($id));
}
else
{
	header('Location: message.php');
}

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>