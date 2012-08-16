<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$ex['location'] = 'forums';
require_once 'config.php';
require_once 'core/classes.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

$section = $excursion->import('section', 'G', 'TXT');
$post_id = $excursion->import('post_id', 'G', 'INT');
$page = (!empty($page) ? $page : '1');

switch ($m)
{
	case 'newtopic':
	require('core/structure/forums/forum.newtopic.php');
	break;

	case 'edit':
	require('core/structure/forums/forum.edit.php');
	break;

	case 'post':
	require('core/structure/forums/forum.post.php');
	break;

	case 'topics':
	require('core/structure/forums/forum.topics.php');
	break;

	default:
	require('core/structure/forums/forum.php');
	break;
}
 
?>