<?php 
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

require_once 'core/header.php';

$xtpl = new XTemplate($excursion->generateTPL(array('users', 'edit')));

$sql = $db->query("SELECT * FROM members WHERE id = ".$id);
$row = $sql->fetch();

if($action == 'send')
{
	/* === Hook === */
	foreach ($excursion->Hook('user.edit.send') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$insert['theme'] = $excursion->import('themes','P','TXT');
	$insert['username'] = $excursion->import('username','P','TXT');
	$insert['gender'] = $excursion->import('gender','P','TXT');
	$insert['groupid'] = $excursion->import('groupid','P','INT');
	$insert['email'] = $excursion->import('email','P','TXT');
	$insert['birthdate'] = (int) $excursion->import_date('birthdate', false);
	$insert['birthdate'] = ($insert['birthdate'] > $sys['now_offset']) ? ($sys['now_offset'] - 31536000) : $insert['birthdate'];
	$insert['birthdate'] = ($insert['birthdate'] == '0') ? '0000-00-00' : $excursion->stamptodate($insert['birthdate']);
	$new_pass1 = $excursion->import('new_password1','P','TXT',16);
	$new_pass2 = $excursion->import('new_password2','P','TXT',16);
	
	if($_FILES['avatar'])
	{
		$file = $_FILES['avatar'];
	
		$gd_supported = array('jpg', 'jpeg', 'png', 'gif');
		$file_ext = strtolower(end(explode(".", $file['name'])));
		$fcheck = $excursion->file_check($file['tmp_name'], $file['name'], $file_ext);
		if(in_array($file_ext, $gd_supported) && $fcheck == 1)
		{
			$file['name']= $excursion->safename($file['name'], true);
			$filename_full = $row['id'].'-'.strtolower($file['name']);
			$filepath = 'assets/avatars/'.$filename_full;

			if(file_exists($filepath))
			{
				unlink($filepath);
			}

			move_uploaded_file($file['tmp_name'], $filepath);
			$excursion->imageresize($filepath, $filepath, 100, 100, 'fit', '', 100);
			@chmod($filepath, $config['file_perms']);
			$sql = $db->update(members, array("avatar" => $filepath), "id='".$row['id']."'");
		}
	}
	
	if(!empty($new_pass1) || !empty($new_pass2))
	{
		if ($new_pass1 != $new_pass2) $excursion->reportError('profile_error_nosame');
		if (mb_strlen($new_pass1) < 4) $excursion->reportError('reg_pwd_length');
	}
	
	if(!$excursion->error_found())
	{
		if(!empty($new_pass1) && !empty($new_pass2))
		{
			$db->update(members, array('password' => md5($new_pass1)), "id='".$row['id']."'");
		}
	
		$db->update(members, $insert, "id='".$row['id']."'");
		header('Location: users.php?id='.$row['id']);
	}
}	

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('users', 'm=edit&id='.$row['id'].'&action=send'),
	'FORM_THEMES' => $excursion->selectbox_theme($row['theme'], 'themes'),
	'FORM_USERNAME' => $excursion->inputbox('text', 'username', $row['username'], array('size' => 24, 'maxlength' => 64)),
	'FORM_GENDER' => $excursion->selectbox_gender($row['gender'] ,'gender'),
	'FORM_GROUP' => $excursion->selectbox_groups($row['groupid'],'groupid'),
	'FORM_EMAIL' => $excursion->inputbox('text', 'email', $row['email'], array('size' => 24, 'maxlength' => 64)),
	'FORM_NEWPASSWORD' => $excursion->inputbox('password', 'new_password1', '', array('size' => 12, 'maxlength' => 32)),
	'FORM_REPEAT_NEWPASSWORD' => $excursion->inputbox('password', 'new_password2', '', array('size' => 12, 'maxlength' => 32)),
	'FORM_AVATAR' => $excursion->inputbox('file', 'avatar', '', array('size' => 24)),
	'FORM_BIRTHDATE' => $excursion->selectbox_date($excursion->datetostamp($row['birthdate']), 'short', 'birthdate', $excursion->date('Y', $sys['now_offset']), $excursion->date('Y', $sys['now_offset']) - 77, false),
));

/* === Hook === */
foreach ($excursion->Hook('user.edit.tags') as $pl)
{
	include $pl;
}
/* ===== */

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>