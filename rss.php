<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$ex['location'] = 'rss';
require_once 'config.php';
require_once 'core/classes.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

header("Content-Type: application/xml; charset=ISO-8859-1");

$rss = new RSS();
echo $rss->GetFeed();

?>