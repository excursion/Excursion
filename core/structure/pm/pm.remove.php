<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$msg = $excursion->import('msg', 'P', 'ARR');

/* === Hook === */
foreach ($excursion->Hook('pm.remove') as $pl)
{
	include $pl;
}
/* ===== */

if (!is_array($msg))
{
	header('Location: pm.php');
}

foreach($msg as $k => $v)
{
	$msg[] = (int)$excursion->import($k, 'D', 'INT');
}

if (count($msg)>0)
{
	$msg = '('.implode(',', $msg).')';
	$msg = str_replace('on,', '', $msg);
	$sql = $db->query("SELECT * FROM pm WHERE id IN $msg");
	while($row = $sql->fetch())
	{
		$id = $row['id'];
		if (($row['fromuser'] == $user['id'] && ($row['tostate'] == 3 || $row['tostate'] == 0)) ||
				($row['touser'] == $user['id'] && $row['fromstate'] == 3) ||
				($row['fromuser'] == $user['id'] && $row['touser'] == $user['id']))
		{
			$sql2 = $db->delete(pm, "id = $id");
		}
		elseif($row['fromuser'] == $user['id'] && ($row['tostate'] != 3 || $row['tostate'] != 0))
		{
			$sql2 = $db->update(pm, array('fromstate' => '3'), "id = $id");
		}
		elseif($row['touser'] == $user['id'] && $row['fromstate'] != 3)
		{
			$sql2 = $db->update(pm, array('tostate' => '3'), "id = $id");
		}
	}
	$sql->closeCursor();
}

header('Location: pm.php');

?>