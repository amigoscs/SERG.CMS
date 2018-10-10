<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= $h1 ?></h1>

<div id="adst-tree"></div>

<script>
	var TreeDATA;


	// иконки типов данных и патч до иконок
	var TreeDataTypesIcons = {};
	TreeDataTypesIcons['icons_path'] = '/application/plugins/admin-site-tree/assets/tree-icons/';
	<?
		$objTypes = '';
		foreach($all_obj_types as $val)
		{
			?>
			TreeDataTypesIcons['icon_<?= $val['obj_types_id'] ?>'] = '<?= $val['obj_types_icon'] ?>';
			<?
		}
	?>
</script>


 <ul id="adstreemenu" class="tree-context-menu menu-root" role="menu" aria-labelledby="dLabel" style="z-index:1000;">
	<li class="status tree-context-menu-item" style="z-index:1000;">
		<i class="fa fa-eye" aria-hidden="true"></i> <a href="#status"><?= app_lang('ADST_MENU_CH_STATUS') ?></a>
	</li>
	<li class="edit tree-context-menu-item" style="z-index:1000;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <a href="#edit"><?= app_lang('ADST_MENU_ED_NW_WINDOW') ?></a></li>
	<li class="copy tree-context-menu-item tree-context-menu-submenu"><i class="fa fa-files-o" aria-hidden="true"></i> <a href="#copy"><?= app_lang('ADST_MENU_COPY') ?></a>
			<ul class="tree-context-menu-list">
				<li><i class="fa fa-clone" aria-hidden="true"></i> <a href="#copy_copy"><?= app_lang('ADST_MENU_CR_COPY') ?></a></li>
				<li><i class="fa fa-clone" aria-hidden="true"></i> <a href="#copy_copy_childs"><?= app_lang('ADST_MENU_CR_COPY_CH') ?></a></li>
				<li><i class="fa fa-files-o" aria-hidden="true"></i> <a href="#copy_obj"><?= app_lang('ADST_MENU_CRNEW_COPY') ?></a></li>
				<li><i class="fa fa-files-o" aria-hidden="true"></i> <a href="#copy_obj_childs"><?= app_lang('ADST_MENU_CRNEW_COPY_ALL') ?></a></li>
			</ul>
		</li>
	<li class="on-site tree-context-menu-item"><i class="fa fa-external-link" aria-hidden="true"></i> <a href="#on-site"><?= app_lang('ADST_MENU_OPEN_SITE') ?></a></li>

	<li class="export-nodes tree-context-menu-item tree-context-menu-submenu"><i class="fa fa-file-text-o" aria-hidden="true"></i> <a href="#export-nodes"><?= app_lang('ADST_MENU_EXPORT') ?></a>
		<ul class="tree-context-menu-list">
			<li><i class="fa fa-file-text-o" aria-hidden="true"></i> <a href="#export_selected"><?= app_lang('ADST_MENU_EXPORT_SELECTED') ?></a></li>
			<li><i class="fa fa-file-text-o" aria-hidden="true"></i> <a href="#export_childs"><?= app_lang('ADST_MENU_EXPORT_CHILD') ?></a></li>
		</ul>
	</li>

	<li class="export-nodes tree-context-menu-item tree-context-menu-submenu"><i class="fa fa-sort" aria-hidden="true"></i> <a href="#"><?= app_lang('ADST_MENU_SORTED') ?></a>
		<ul class="tree-context-menu-list">
			<li><i class="fa fa-sort-alpha-asc" aria-hidden="true"></i> <a href="#sort_name_asc"><?= app_lang('ADST_MENU_SORTED_NAME_ASC') ?></a></li>
			<li><i class="fa fa-sort-alpha-desc" aria-hidden="true"></i> <a href="#sort_name_desc"><?= app_lang('ADST_MENU_SORTED_NAME_DESC') ?></a></li>
		</ul>
	</li>

	<li class="group-nodes tree-context-menu-item"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> <a href="#group-nodes"><?= app_lang('ADST_MENU_CR_GROUP') ?></a></li>
	<li class="group-nodes tree-context-menu-item"><i class="fa fa-diamond" aria-hidden="true"></i> <a href="#orig-nodes"><?= app_lang('ADST_MENU_CR_ORIG') ?></a></li>
	<li class="divider tree-context-menu-item"><?= app_lang('ADST_MENU_OTHER') ?></li>
	<li class="delete tree-context-menu-item"><i class="fa fa-trash-o" aria-hidden="true"></i> <a href="#delete"><?= app_lang('ADST_MENU_DEL') ?></a></li>
	<li class="get-info tree-context-menu-item"><i class="fa fa-info" aria-hidden="true"></i> <a href="#get-info"><?= app_lang('ADST_MENU_INFO') ?></a></li>
	<li class="get-info tree-context-menu-item"><i class="fa fa-refresh" aria-hidden="true"></i> <a href="#update"><?= app_lang('ADST_MENU_UPDATE_NODE') ?></a></li>
 </ul>
