<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<h1><?= app_lang('H1_USERS_USERS_LIST') ?></h1>

<? require_once(__DIR__ . '/_menu.php'); ?>

<style>
.simple-table th a {}
.active-sort {text-decoration: underline;}
.active-sort::after {content: '';display: inline-block;vertical-align: middle;font-family: 'FontAwesome';font-size: 12px;margin-left: 8px}
.active-sort.active-asc::after {content:'\f176'}
.active-sort.active-desc::after {content:'\f175'}
</style>

<div class="block-widjet-elements">
    <div class="bwe-menu-top">
        <ul>
        	<? foreach($users_groups as $key => $val): ?>
            <? ($key == $activeGroup) ? $selected = ' class="selected"' : $selected = ''; ?>
			<li<?=$selected?>><a href="/admin/users?group=<?=$key?>"><span><?=$val['users_group_name']?></span></a></li>
            <? endforeach; ?>
        </ul>
    </div>

    <div class="bwe-content">
    	<table class="simple-table">
        	<tr>
            	<th>ID</th>
                <th><?= app_lang('INPUT_LABEL_USERS_LOGIN') ?></th>
                <th><?= app_lang('INPUT_LABEL_USERS_NAME') ?></th>
				<th><a href="<?= $sortLinkStatus ?>" class="<?= $sortLinkStatusActive ?>"><?= app_lang('INPUT_LABEL_USERS_STATUS_USER') ?></a></th>
                <th><?= app_lang('INPUT_LABEL_USERS_EMAIL') ?></th>
                <th><a href="<?= $sortLinkDateReg ?>" class="<?= $sortLinkDateRegActive ?>"><?= app_lang('INPUT_LABEL_USERS_DATEREGISTER') ?></a></th>
                <th><a href="<?= $sortLinkListVis ?>" class="<?= $sortLinkListVisActive ?>"><?= app_lang('INPUT_LABEL_USERS_LAST_VIZIT') ?></a></th>
            </tr>
   			<? foreach($users as $value): ?>
            <tr class="tr-<?= $value['users_status'] ?>">
            	<td><?= $value['users_id'] ?></td>
                <td><a href="/admin/users/edit/<?= $value['users_id'] ?>"><?= $value['users_login'] ?></a></td>
                <td><?= $value['users_name'] ?></td>
				<td><span class="user-status"><i><?= $value['users_status'] ?></i></span></td>
                <td><?=$value['users_email']?></td>
                <td><?= date_convert('d.m.Y', $value['users_date_registr']) ?></td>
                <td><?= date('d.m.Y H:i:s', $value['users_last_visit']) ?></td>
            </tr>
            <? endforeach ?>
        </table>
    </div>
</div>
