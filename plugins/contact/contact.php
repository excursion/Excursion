<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=standalone
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'search');
$excursion->blockAuth($user['auth_read']);

if($action == 'send')
{
	$insert['name'] = $excursion->import('name', 'P', 'TXT');
	$insert['email'] = $excursion->import('email', 'P', 'TXT');
	$insert['subject'] = $excursion->import('subject', 'P', 'TXT');
	$insert['text'] = $excursion->import('text', 'P', 'HTM');
	
	if (mb_strlen($insert['name']) < 4) $excursion->reportError('error_name_length');
	if (!filter_var($insert['email'], FILTER_VALIDATE_EMAIL )) $excursion->reportError('reg_email_format');
	if (mb_strlen($insert['subject']) < 2) $excursion->reportError('error_subject_length');
	if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
		
	if(!$excursion->error_found())
	{
		$semail = $config['admin_email'];
		$headers = ("From: \"" . $insert['name'] . "\" <" . $insert['email'] . ">\n");
		$context = array(
			'author' => $insert['name'],
			'email' => $insert['email'],
			'subject' => $insert['subject'],
			'text' => $insert['text']	
		);
		$rtextm = $excursion->rc($R['contact_message'], $context);
		$excursion->sendMail($semail, $insert['subject'], $rtextm, $headers);
	}
}

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('plugin', 'p=contact&amp;action=send'),
	'FORM_NAME' => $excursion->inputbox('text', 'name', $insert['name'], 'size="32"'),
	'FORM_SUBJECT' => $excursion->inputbox('text', 'subject', $insert['subject'], 'size="32"'),
	'FORM_EMAIL' => $excursion->inputbox('text', 'email', $insert['email'], 'size="32"'),
	'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor')
));

$excursion->display_messages($xtpl);

?>