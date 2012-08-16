<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$ex['location'] = 'page';
require_once 'config.php';
require_once 'core/classes.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

/* === Hook === */
foreach ($excursion->Hook('page.actions') as $pl)
{
	include $pl;
}
/* ===== */

if($action == 'remove')
{
	$page_cat = $db->query("SELECT cat FROM pages WHERE id='".$id."' LIMIT 1")->fetchColumn();
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $page_cat);
	
	if($user['auth_admin'])
	{
		/* === Hook === */
		foreach ($excursion->Hook('page.remove') as $pl)
		{
			include $pl;
		}
		/* ===== */
		
		$sql_page_delete = $db->delete(pages, "id=$id");
	}
	header('Location: list.php?c='.$page_cat);
}
if($action == 'queue')
{
	/* === Hook === */
	foreach ($excursion->Hook('page.queue.first') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$page_state = $db->query("SELECT state FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	$page_cat = $db->query("SELECT cat FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $page_cat);

	if($page_state > 0 && $user['auth_admin'])
	{
		/* === Hook === */
		foreach ($excursion->Hook('page.queue') as $pl)
		{
			include $pl;
		}
		/* ===== */
	
		$insert['state'] = 0;
		$sql_update_page_state = $db->update(pages, $insert, 'id=?', array($id));
		header('Location: list.php?c='.$page_cat);
	}
	else
	{
		$excursion->reportError('error_page_inactive');
	}
}

switch ($m)
{
	case 'edit':
	require('core/structure/pages/page.edit.php');
	break;
	
	case 'add':
	require('core/structure/pages/page.add.php');
	break;
	
	default:
	require('core/structure/pages/page.php');
	break;
}
 
?>