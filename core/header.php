<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
ob_start();

$time['start'] = microtime();

$xtpl = new XTemplate($excursion->generateTPL('header'));

$title_params = array(
	'MAINTITLE' => $config['title'],
	'DESCRIPTION' => $config['subtitle'],
	'SUBTITLE' => $ex['title']
);
$title = ($ex['location'] == 'index' || empty($ex['title'])) ? '{MAINTITLE} - {DESCRIPTION}' : '{SUBTITLE} - {MAINTITLE}';
$xtpl->assign('HEADER_TITLE', $excursion->create_title($title, $title_params));

if ($user['id'] > 0)
{
	$xtpl->parse('HEADER.USER');
}
else
{
	$xtpl->parse('HEADER.GUEST');
}

$xtpl->parse('HEADER');
$xtpl->out('HEADER');
 
?>