<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('users', 'recover')));

if($action == 'lostpass')
{
	if($step == 2)
	{
		$answer = $db->query("SELECT SQ_Answer FROM members WHERE email='" .$email. "'")->fetchColumn();
		
		if(strtoupper(trim($answer)) == strtoupper(trim($excursion->import('answer','P','TXT'))))
		{
			$member->lostPassword($email);
			header("Location: message.php?id=104");
		}
	}
	else
	{
		$email_exists = (bool)$db->query("SELECT id FROM members WHERE email='" .$email. "' LIMIT 1")->fetch();
		
		if($email_exists)
		{
			$SQ_index = $db->query("SELECT SQ_Index FROM members WHERE email='" .$email. "'")->fetchColumn();
			$SQ = $db->query("SELECT question FROM security_questions WHERE id='" .$SQ_index. "'")->fetchColumn();

			$xtpl->assign(array(
				'SECURITY_QUESTION' => $SQ,
				'EMAIL' => $email)
			);
			
			$xtpl->parse('MAIN.SECURITY_QUESTION');
		}
	}
}
if($action == 'validation')
{
	$member->sendValidationEmail($email);
}
if(empty($action) || $action == 'validation')
{
	$xtpl->parse('MAIN.RECOVERY_OPTIONS');
}

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>