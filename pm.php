<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$ex['location'] = 'pm';
require_once 'config.php';
require_once 'core/classes.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

$excursion->checkAuth($user['group'], $ex['location']);
$title = $excursion->import('title', 'G', 'TXT');

switch ($m)
{
	case 'send':
	require('core/structure/pm/pm.send.php');
	break;
	
	case 'remove':
	require('core/structure/pm/pm.remove.php');
	break;
	
	case 'details':
	require('core/structure/pm/pm.details.php');
	break;
	
	default:
	require('core/structure/pm/pm.php');
	break;
}
 
?>