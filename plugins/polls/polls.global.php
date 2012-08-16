<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=global
[END_PLUGIN]
==================== */

class Polls {

	function getPoll($id)
	{
		global $db, $excursion;
		
		list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'polls');
		
		$sql_poll = $db->query("SELECT * FROM polls WHERE id='$id' LIMIT 1");
		$row = $sql_poll->fetch();
		
		$total_votes = 0;
		$sql_count = $db->query("SELECT count FROM polls_options WHERE pollid='$id'");
		foreach ($sql_count->fetchAll() as $cnt)
		{
			$total_votes = $total_votes + $cnt['count'];
		}
		$total_votes = ($total_votes == 0 ? 1 : $total_votes);
						
		$poll = new XTemplate('plugins/polls/tpl/polls.index.xtpl');
		
		$poll->assign(array(
			'POLL_ID' => $row['id'],
			'POLL_TITLE' => $row['text'],
			'POLL_CODE' => $row['code'],
			'POLL_DATE' => date($config['date_medium'], $row['creationdate'])
		));
		
		if(!$this->checkVoted($id) && $user['auth_write'])
		{
			$sql_options = $db->query("SELECT * FROM polls_options WHERE pollid='$id'");
			foreach ($sql_options->fetchAll() as $opt)
			{
				$poll->assign(array(
					'ID' => $opt['id'],
					'TITLE' => $opt['text'],
					'INPUT' => $excursion->radiobox($opt['id'], 'vote[]', $opt['text'])
				));
				$poll->parse('MAIN.NOT_VOTED.OPTIONS');
			}
			$poll->parse('MAIN.NOT_VOTED');
			$poll->assign('POLL_DISPLAY', 'class="poll_hide"');
		}
		
		$sql_results = $db->query("SELECT * FROM polls_options WHERE pollid='$id'");
		foreach ($sql_results->fetchAll() as $res)
		{
			$poll->assign(array(
				'RESULT_ID' => $res['id'],
				'RESULT_TITLE' => $res['text'],
				'RESULT_PERCENT' => $this->getPercent($res['count'], $total_votes)
			));
			$poll->parse('MAIN.RESULTS');
		}
		
		$poll->parse('MAIN');
		
		return ($row && $user['auth_read']) ? $poll->text('MAIN') : '';
	}
	
	function getPercent($amount, $total) 
	{
		$count1 = $amount / $total;
		$count2 = $count1 * 100;
		$count = number_format($count2, 0);
		
		return $count;
	}
	
	function getLatest()
	{
		global $db;
	
		$id = $db->query("SELECT id FROM polls ORDER BY creationdate DESC LIMIT 1")->fetchColumn();
		
		return $id;	
	}
	
	function checkVoted($id)
	{
		global $db, $user;
	
		$check = $db->query("SELECT COUNT(*) FROM polls_voters WHERE pollid='$id' AND userid='".$user['id']."' LIMIT 1")->fetchColumn();
		
		if($check > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
}

$header_css[] = 'plugins/polls/css/polls.css';
$footer_js[] = 'plugins/polls/js/polls.js';

$polls = new Polls();

$plugin['polls_display'] = $polls->getPoll($polls->getLatest());

?>