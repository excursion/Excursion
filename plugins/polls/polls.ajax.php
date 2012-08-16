<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=ajax
[END_PLUGIN]
==================== */

$vote = $excursion->import('vote_option','P','INT');
$id = $excursion->import('id','G','INT');

if(isset($_POST) && !empty($vote)) 
{
	$insert['pollid'] = $id;
	$insert['userid'] = $user['id'];
	$db->insert(polls_voters, $insert);
	$count = $db->query("SELECT count FROM polls_options WHERE id='".$vote."' LIMIT 1")->fetchColumn();
	$count = $count + 1;
	$db->update(polls_options, array("count" => $count), "id='".$vote."'");
}

?>