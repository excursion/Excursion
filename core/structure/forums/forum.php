<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$ex['title'] = 'Community Forums';
 
require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('forums', 'sections')));

$fstlvl = array();
$nxtlvl = array();
foreach ($structure['forums'] as $i => $x)
{
	$parents = explode('.', $x['path']);
	$depth = count($parents);

	if ($depth < 2)
	{
		$fstlvl[$i] = $i;
	}
	elseif($depth < 4)
	{
		$nxtlvl[$parents[$depth-2]][$i] = $i;
	}
}

foreach ($fstlvl as $x)
{
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('forums', $x);
	if (is_array($nxtlvl[$x]) && $user['auth_read'])
	{
		$yy = 0;
		foreach ($nxtlvl[$x] as $y)
		{
			list($user['2']['auth_read'], $user['2']['auth_write'], $user['2']['auth_admin']) = $excursion->checkAuth('forums', $y);
			if($user['2']['auth_read'])
			{
				$yy++;
				
				$post_count = 0;
				$view_count = 0;
				$sql_count = $db->query("SELECT postcount, viewcount FROM forums_topics WHERE cat='".$y."'");
				while ($count = $sql_count->fetch())
				{
					$post_count = $post_count + $count['postcount'];
					$view_count = $view_count + $count['viewcount'];
				}
				
				$sql_pos = $db->query("SELECT t.*, p.*
							FROM forums_topics as t
							LEFT JOIN forums_posts AS p ON p.topicid=t.id
							WHERE t.cat='".$structure['forums'][$y]['code']."'
							ORDER BY p.updated DESC, p.creation DESC, p.id DESC 
							LIMIT 1");
				$pos = $sql_pos->fetch();

				if($pos)
				{
					$xtpl->assign(array(
						'TITLE' => $pos['title'],
						'AUTHOR' => $excursion->generateUser($pos['posterid']),
						'POST_ID' => $pos['id'],
						'TOPIC_ID' => $pos['topicid'],
						'SECTION' => $pos['cat']
					));
					$xtpl->parse('MAIN.CAT.SECTION.LAST_POST');
				}
				else
				{
					$xtpl->parse('MAIN.CAT.SECTION.NO_LAST_POST');
				}
				
				$xtpl->assign(array(
					'SECTION_TITLE' => $structure['forums'][$y]['title'],
					'SECTION_DESC' => $structure['forums'][$y]['desc'],
					'SECTION_CODE' => $structure['forums'][$y]['code'],
					'SECTION_POST_COUNT' => $post_count,
					'SECTION_VIEW_COUNT' => $view_count,
				));
				$xtpl->parse('MAIN.CAT.SECTION');
			}
		}
	}
	$xx++;
	
	if(!$nxtlvl[$x])
	{
		$xtpl->parse('MAIN.CAT.EMPTY');
	}
	
	if($user['auth_read'])
	{
		$xtpl->assign(array(
			'CAT_TITLE' =>  $structure['forums'][$x]['title']
		));
		$xtpl->parse('MAIN.CAT');
	}
}

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>