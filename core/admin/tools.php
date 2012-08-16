<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'plugins';


$plugin = $excursion->import('plugin', 'G', 'ALP', 24);
$dir = 'plugins';

$admin_file = $dir . '/' . $plugin . '/' . $plugin . '.admin.php';
if(file_exists($admin_file))
{
	require($admin_file);
}
else
{
	header('Location: admin.php?m=plugins&a=details&plugin='.$plugin);
}

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>