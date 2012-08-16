<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$xtpl = new XTemplate($excursion->generateTPL('footer'));

$time['end'] = microtime();
$time = $time['end'] - $time['start'];
$time = round($time, 4);
$xtpl->assign('CREATION_TIME', $time);

$xtpl->parse('FOOTER');
$xtpl->out('FOOTER');

ob_flush();
 
?>