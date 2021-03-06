<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'members';

$grpid = $excursion->import('grpid', 'G', 'INT', 3);
$xtpl = new XTemplate($excursion->generateTPL('members', 'admin'));

switch($a)
{
	/* =============== */
	case 'permissions':
	/* =============== */
	
		if($action == 'send')
		{
			
		
			if(!$excursion->error_found())
			{
				$mask = array();
				$auth = $excursion->import('auth', 'P', 'ARR');
								
				foreach ($auth as $k => $v)
				{
					foreach ($v as $i => $j)
					{
						if (is_array($j))
						{
							$mask = 0;
							foreach ($j as $l => $m)
							{
								$mask += $excursion->authValue($l);
							}
							$db->update('auth', array('rights' => $mask),
								"groupid=? AND code=? AND area=?", array($grpid, $k, $i));
						}
					}
				}
				
				$excursion->reorderAuth();
				
				header('Location: admin.php?m=members&a=permissions&grpid=' . $grpid);
			}
		}
	
		$sql = $db->query("SELECT * FROM auth WHERE groupid = '".$grpid."' AND code = 'page' ORDER BY area ASC");
		foreach ($sql->fetchAll() as $row)
		{
			$sql_cat = $db->query("SELECT * FROM categories WHERE code = '".$row['area']."' LIMIT 1");
			$cat = $sql_cat->fetch();
			
			$form_page[$row['area']]['R'] = false;
			$form_page[$row['area']]['W'] = false;
			$form_page[$row['area']]['A'] = false;
			
			$rights_page = $excursion->getAuth($row['rights']);
			$masks_page = str_split($rights_page);
			foreach ($masks_page as $k_page)
			{
				$form_page[$row['area']][$k_page] = true;
			}
			$lock_page = $excursion->getAuth($row['rights_lock']);
			$masks_page = str_split($lock_page);
			foreach ($masks_page as $k_page)
			{
				$locked_page[$row['area']][$k_page] = true;
			}
			
			$form_page_r = (!$locked_page[$row['area']]['R']) ? $excursion->checkbox($form_page[$row['area']]['R'], 'auth[page]['.$cat['code'].'][R]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_page_w = (!$locked_page[$row['area']]['W']) ? $excursion->checkbox($form_page[$row['area']]['W'], 'auth[page]['.$cat['code'].'][W]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_page_a = (!$locked_page[$row['area']]['A']) ? $excursion->checkbox($form_page[$row['area']]['A'], 'auth[page]['.$cat['code'].'][A]') : '<img src="assets/images/authentication/auth_lock.png" />';
				
			$icon_file = 'assets/images/categories/icon-'.$cat['code'].'.png';
			
			$xtpl->assign(array(
				'CAT' => $cat['title'],
				'CAT_CODE' => $cat['code'],
				'ICON' => (file_exists($icon_file)) ? $icon_file : 'assets/images/categories/icon-default.png',
				'FORM_PAGE_R' => $form_page_r,
				'FORM_PAGE_W' => $form_page_w,
				'FORM_PAGE_A' => $form_page_a
			));
			
			$xtpl->parse('MAIN.PERMISSIONS.PAGE_ROW');
		}
		
		$sql2 = $db->query("SELECT * FROM auth WHERE groupid = '".$grpid."' AND code = 'plugin' ORDER BY area ASC");
		foreach ($sql2->fetchAll() as $row2)
		{
		
			$sql_plug = $db->query("SELECT * FROM plugins WHERE code = '".$row2['area']."' LIMIT 1");
			$plu = $sql_plug->fetch();
			
			$config[$row2['area']] = 'plugins/' . $row2['area'] . '/' . $row2['area'] . '.config.php';
			$info[$row2['area']] = $excursion->infoget($config[$row2['area']], 'PLUGIN_CONFIG');
			
			$form_plugin[$row2['area']]['R'] = false;
			$form_plugin[$row2['area']]['W'] = false;
			$form_plugin[$row2['area']]['A'] = false;
			
			$rights_plugin = $excursion->getAuth($row2['rights']);
			$masks_plugin = str_split($rights_plugin);
			foreach ($masks_plugin as $k_plugin)
			{
				$form_plugin[$row2['area']][$k_plugin] = true;
			}
			$lock_plugin = $excursion->getAuth($row2['rights_lock']);
			$masks_plugin = str_split($lock_plugin);
			foreach ($masks_plugin as $k_plugin)
			{
				$locked_plugin[$row2['area']][$k_plugin] = true;
			}
								
			$form_plugin_r = (!$locked_plugin[$row2['area']]['R']) ? $excursion->checkbox($form_plugin[$row2['area']]['R'], 'auth[plugin]['.$plu['code'].'][R]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_plugin_w = (!$locked_plugin[$row2['area']]['W']) ? $excursion->checkbox($form_plugin[$row2['area']]['W'], 'auth[plugin]['.$plu['code'].'][W]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_plugin_a = (!$locked_plugin[$row2['area']]['A']) ? $excursion->checkbox($form_plugin[$row2['area']]['A'], 'auth[plugin]['.$plu['code'].'][A]') : '<img src="assets/images/authentication/auth_lock.png" />';
				
			$icon_file = 'plugins/' . $plu['code'] . '/img/icon-' . $plu['code'] . '.png';
			
			$xtpl->assign(array(
				'TITLE' => $info[$row2['area']]['Name'],
				'CODE' => $info[$row2['area']]['Code'],
				'ICON' => (file_exists($icon_file)) ? $icon_file : 'assets/images/icon-plugin_default.png',
				'FORM_PLUGIN_R' => $form_plugin_r,
				'FORM_PLUGIN_W' => $form_plugin_w,
				'FORM_PLUGIN_A' => $form_plugin_a
			));
			
			$xtpl->parse('MAIN.PERMISSIONS.PLUGIN_ROW');
		}
		
		$sql3 = $db->query("SELECT * FROM auth WHERE groupid = '".$grpid."' AND code = 'admin' ORDER BY area ASC");
		foreach ($sql3->fetchAll() as $row3)
		{
			$form_admin[$row3['area']]['R'] = false;
			$form_admin[$row3['area']]['W'] = false;
			$form_admin[$row3['area']]['A'] = false;
			
			$rights_admin = $excursion->getAuth($row3['rights']);
			$masks_admin = str_split($rights_admin);
			foreach ($masks_admin as $k_admin)
			{
				$form_admin[$row3['area']][$k_admin] = true;
			}
			$lock_admin = $excursion->getAuth($row3['rights_lock']);
			$masks_admin = str_split($lock_admin);
			foreach ($masks_admin as $k_admin)
			{
				$locked_admin[$row3['area']][$k_admin] = true;
			}
			
			$form_admin_r = (!$locked_admin[$row3['area']]['R']) ? $excursion->checkbox($form_admin[$row3['area']]['R'], 'auth[admin]['.$row3['area'].'][R]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_admin_w = (!$locked_admin[$row3['area']]['W']) ? $excursion->checkbox($form_admin[$row3['area']]['W'], 'auth[admin]['.$row3['area'].'][W]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_admin_a = (!$locked_admin[$row3['area']]['A']) ? $excursion->checkbox($form_admin[$row3['area']]['A'], 'auth[admin]['.$row3['area'].'][A]') : '<img src="assets/images/authentication/auth_lock.png" />';
							
			$xtpl->assign(array(
				'FORM_ADMIN_R' => $form_admin_r,
				'FORM_ADMIN_W' => $form_admin_w,
				'FORM_ADMIN_A' => $form_admin_a
			));
			
			$xtpl->parse('MAIN.PERMISSIONS.ADMIN_ROW');
		}
		
		$sql4 = $db->query("SELECT * FROM auth WHERE groupid = '".$grpid."' AND code = 'forums' ORDER BY area ASC");
		foreach ($sql4->fetchAll() as $row4)
		{
			$sql_cat = $db->query("SELECT * FROM forums_sections WHERE code = '".$row4['area']."' LIMIT 1");
			$cat = $sql_cat->fetch();
			
			$form_forum[$row4['area']]['R'] = false;
			$form_forum[$row4['area']]['W'] = false;
			$form_forum[$row4['area']]['A'] = false;
			
			$rights_forum = $excursion->getAuth($row4['rights']);
			$masks_forum = str_split($rights_forum);
			foreach ($masks_forum as $k_forum)
			{
				$form_forum[$row4['area']][$k_forum] = true;
			}
			$lock_forum = $excursion->getAuth($row4['rights_lock']);
			$masks_forum = str_split($lock_forum);
			foreach ($masks_forum as $k_forum)
			{
				$locked_forum[$row4['area']][$k_forum] = true;
			}
			
			$form_forum_r = (!$locked_forum[$row4['area']]['R']) ? $excursion->checkbox($form_forum[$row4['area']]['R'], 'auth[forums]['.$cat['code'].'][R]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_forum_w = (!$locked_forum[$row4['area']]['W']) ? $excursion->checkbox($form_forum[$row4['area']]['W'], 'auth[forums]['.$cat['code'].'][W]') : '<img src="assets/images/authentication/auth_lock.png" />';
			$form_forum_a = (!$locked_forum[$row4['area']]['A']) ? $excursion->checkbox($form_forum[$row4['area']]['A'], 'auth[forums]['.$cat['code'].'][A]') : '<img src="assets/images/authentication/auth_lock.png" />';
				
			$icon_file = 'assets/images/categories/icon-'.$cat['code'].'.png';
			
			$xtpl->assign(array(
				'CAT' => $cat['title'],
				'CAT_CODE' => $cat['code'],
				'ICON' => (file_exists($icon_file)) ? $icon_file : 'assets/images/categories/icon-default.png',
				'FORM_FORUM_R' => $form_forum_r,
				'FORM_FORUM_W' => $form_forum_w,
				'FORM_FORUM_A' => $form_forum_a
			));
			
			$xtpl->parse('MAIN.PERMISSIONS.FORUM_ROW');
		}
		
		$xtpl->assign('FORM_ACTION', $excursion->url('admin', 'm=members&a=permissions&grpid='.$grpid.'&action=send'));
		
		$xtpl->parse('MAIN.PERMISSIONS');
	
	break;
	
	/* =============== */
	default:
	/* =============== */
	
		if($action == 'update')
		{
			if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');
			
			if(!$excursion->error_found())
			{
				$rstructuretitle = $excursion->import('rstructuretitle', 'P', 'ARR');
				$rstructuredesc = $excursion->import('rstructuredesc', 'P', 'ARR');

				foreach ($rstructuretitle as $i => $k)
				{
					$oldrow = $db->query("SELECT id FROM groups WHERE id=".(int)$i)->fetch();
					$rstructure['title'] = $excursion->import($rstructuretitle[$i], 'D', 'TXT');
					$rstructure['desc'] = $excursion->import($rstructuredesc[$i], 'D', 'TXT');

					$db->update(groups, $rstructure, "id=".(int)$i);
				}

				header('Location: admin.php?m=members');
			}
		}
		
		if($action == 'save')
		{
			$insert['title'] = $excursion->import('title','P','TXT');
			$insert['desc'] = $excursion->import('desc','P','TXT');

			if (empty($insert['title'])) $excursion->reportError('admin_error_title_missing');
			if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');

			if(!$excursion->error_found())
			{
				$db->insert(groups, $insert);
				$new_grpid = $db->lastInsertId();
				
				$sql = $db->query("SELECT * FROM auth WHERE groupid = '3' LIMIT 1");
				foreach ($sql->fetchAll() as $row)
				{
					$db->query("INSERT INTO auth (groupid, code, area, rights, rights_lock) 
						Select ".$new_grpid.", code, area, rights, rights_lock from auth WHERE groupid = 3");
				}
				
				$update_auth['rights'] = 0;
				$update_auth['rights_lock'] = 0;
				
				$db->update(auth, $update_auth, "groupid='".$new_grpid."' AND code='admin'");
				
				$excursion->reorderAuth();
				
				header('Location: admin.php?m=members');
			}
		}
		
		if($action == 'remove')
		{
			if (!$user['auth_admin']) $excursion->reportError('error_insufficient_rights');
			if ($id <= 4) $excursion->reportError('admin_error_required_groups');
			if (empty($id)) $excursion->reportError('error_unknown');
			
			if(!$excursion->error_found())
			{
				$db->delete(groups, "id='".$db->prep($id)."'");
				$db->delete(auth, "groupid='".$db->prep($id)."'");

				header('Location: admin.php?m=members');
			}
		}

		$sql = $db->query("SELECT * FROM groups ORDER BY id ASC");
		foreach ($sql->fetchAll() as $row)
		{
			$structure_id = $row['id'];
			
			if($row['id'] == '1' || $row['id'] == '2' || $row['id'] == '4')
			{
				$xtpl->assign(array('FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['title'], 'maxlength="64"', 'input_text_disabled')));
			}
			else
			{
				$xtpl->assign(array('FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['title'], 'maxlength="64"', 'input_text_medium')));
			}
		
			$xtpl->assign(array(
				'FORM_DESC' => $excursion->inputbox('text', 'rstructuredesc['.$structure_id.']', $row['desc'], 'maxlength="64"'),
				'ID' => $row['id'],
				'TITLE' => $row['title'],
				'DESC' => $row['desc'],
				'ICON' => $row['icon']
			));
			
			$xtpl->parse('MAIN.DEFAULT.ROW');
		}

		$xtpl->assign(array(
			'FORM_ACTION_SAVE' => $excursion->url('admin', 'm=members&action=save'),
			'FORM_ACTION_UPDATE' => $excursion->url('admin', 'm=members&action=update'),
			'FORM_TITLE' => $excursion->inputbox('text', 'title', '', array('size' => 24, 'maxlength' => 64)),
			'FORM_DESC' => $excursion->inputbox('text', 'desc', '', array('size' => 24, 'maxlength' => 64)),
		));
		
		$xtpl->parse('MAIN.DEFAULT');
		
	break;
}

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>