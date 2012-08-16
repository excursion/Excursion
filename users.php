<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$ex['location'] = 'users'; 
require_once 'config.php';
require_once 'core/classes.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

$token = $excursion->import('token','G','TXT');
$email = $excursion->import('email','P','TXT',64, TRUE);

if($action == 'validate')
{
	/* === Hook === */
	foreach ($excursion->Hook('user.validate.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$member->validate($token);
}
if($action == 'remove')
{
	/* === Hook === */
	foreach ($excursion->Hook('user.remove.action.first') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$group_id = $db->query("SELECT groupid FROM members WHERE token='$token' LIMIT 1")->fetchColumn();
	if($group_id == 1)
	{
		/* === Hook === */
		foreach ($excursion->Hook('user.remove.action.loop') as $pl)
		{
			include $pl;
		}
		/* ===== */
	
		$member->remove($token);
	}
	else
	{
		header('Location: message.php');
	}
}

switch ($m)
{
	case 'profile':
	require('core/structure/users/users.profile.php');
	break;
	
	case 'edit':
	require('core/structure/users/users.edit.php');
	break;
	
	case 'recover':
	require('core/structure/users/users.recover.php');
	break;
	
	default:
	require('core/structure/users/users.php');
	break;
}
 
?>