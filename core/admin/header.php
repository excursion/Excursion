<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
ob_start();

$xtpl = new XTemplate($excursion->generateTPL('header', 'admin'));

$xtpl->parse('ADMIN_HEADER');
$xtpl->out('ADMIN_HEADER');
 
?>