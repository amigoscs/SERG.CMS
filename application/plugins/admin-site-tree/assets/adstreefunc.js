// объект дерево
var $TREE = $('#adst-tree');
var TreeDataUrl = '/admin/admin-site-tree/loadNodesData';
var TreeExportUrl = '/admin/exp-csv/export_node_id';
var IDGroupNode = '9999999998'; // ID групповой ноды

// упорядочить ноды на сервере
function SaveOrderNodes(node, NewParentID, CallBack)
{
	// ID предыдущего родителя
	var NodeID = node.id;

	// ID нового родителя
	//var NewParentID = node.parent.id;

	ParentNode = node.parent;
	// обновим текущего родителя у ноды
	$TREE.tree('updateNode', node, {parentID: NewParentID});

	var ChildrenNodesIdArr = [];
	for (var i=0; i < ParentNode.children.length; i++) {
		ChildrenNodesIdArr[ChildrenNodesIdArr.length] = ParentNode.children[i].id;
	}

	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=save-order',
		type: 'POST',
		data: { row_id: NodeID, new_parent: NewParentID, nodes_array: ChildrenNodesIdArr },
		dataType: 'json',
		success: function(DATA){
			if(CallBack) {
				CallBack(DATA);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}


// срабатывает при выделении ноды
function adstStartDraggable(node)
{
	//console.log(node);
	// разрешим перетаскивание ноды
	return true;
}


/*
##########################
###### функции контекстного меню
############################
*/

// изменить статус видимости
function treeChangeStatus(Node, CallBack)
{
	var AllSelectedNodes = $TREE.tree('getSelectedNodes');
	var SnodesID = [];
	$.each(AllSelectedNodes, function(idx, elem){
		SnodesID[SnodesID.length] = elem.objectID;
	});
	//var DATA;
	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=change-status',
		type: 'POST',
		data: { objects_id: SnodesID },
		dataType: 'json',
		success: function(DATA){
			if(CallBack) {
				CallBack(DATA, AllSelectedNodes);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}

// изменить статус видимости У ОБЪЕТА И ЕГО КОПИЙ
function treeChangeStatusCopy(Node, Status, CallBack)
{
	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=change-status-obj-copy',
		type: 'POST',
		data: { object_id: Node.objectID, status: Status },
		success: function(data){
			if(CallBack) {
				CallBack(data);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}


// копирование объекта
function treeCopyObject(node, typeCopy, CallBack)
{
	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=copy-object',
		type: 'POST',
		data: { type_copy: typeCopy, row_id: node.id },
		dataType: 'json',
		success: function(DATA) {
			$.each(DATA.node, function(index, elem){
				$TREE.tree('addNodeAfter', elem, node);
			});
			if(CallBack) {
				CallBack(DATA);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}

// удаление ноды
function treeDeleteNode(node, CallBack)
{
	var TextConfirm = '', NodeRemove = {};
	// если есть дочерние объекты, то не разрешаем удаление
	/*if(node.children.length){
		noty_info('information', 'Нода содержит вложенные ноды. Удаление невозможно');
		return true;
	}*/

	if(node.copyType == 'orig'){
		TextConfirm = 'Вы пытаетесь удалить оригинальный объект. Все копии будут удалены!';
		$TREE.tree('getNodeByCallback',function(otherNodes) {
			if (otherNodes.objectID == node.objectID) {
				// Node is found; add to selected
				$TREE.tree('addToSelection', otherNodes);
				NodeRemove[otherNodes.id] = otherNodes;
			}
		});
	}else{
		TextConfirm = 'Продолжить удаление?';
		NodeRemove[node.id] = node;
	}

	noty_comfirm(TextConfirm, 'Продолжить', 'Отмена', function(r){
		if(!r)
			return true;

		add_loader();
		$.ajax({
			url: '/admin/admin-site-tree/ajax?method=delete-node',
			type: 'POST',
			data: { row_id: node.id, row_type: node.copyType },
			dataType: 'json',
			success: function(DATA){
				$.each(NodeRemove, function(idx, elemNode){
					$TREE.tree('removeNode', elemNode);
				});
				remove_loader();
				noty_info('information', DATA.info);
			},
			error: function(a, b, c) {
				admin_dialog('Response error', 'Error', 350);
			}
		});
	});
}

// обновление ноды (обновление)
function treeUpdateNode(node, CallBack)
{
	$.ajax({
		url: TreeDataUrl + '?single_load=1&node=' + node.id,
		type: 'POST',
		data: { value: 'empty'},
		dataType: 'json',
		success: function(DATA){
			$TREE.tree('updateNode', node, DATA[0]);
			// обновим название всех копий ноды
			$TREE.tree('getNodeByCallback', function(otherNode){
				if(otherNode.objectID == node.objectID && otherNode.id != node.id)
					$TREE.tree('updateNode', otherNode, {name: DATA[0].name});
			});

			if(CallBack) {
				CallBack(DATA);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
			if(CallBack) {
				CallBack(a);
			}
		}
	});
}

// возвращает все URL от ноды до родителя первого уровня
function treeGetUrlsTree(node, urlCase)
{
	NodeUrl = '';
	NodeUrl = node.objURL + '/';
	if(node.parent.id)
		NodeUrl += treeGetUrlsTree(node.parent);

	return NodeUrl;
}

// заменить родиеля у массива нод (их ID)
function treeChangeParentNodes(NodesIdArray, NewPArentID, CallBack)
{
	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=change-parent',
		type: 'POST',
		data: { nodes_id: NodesIdArray, parent_id: NewPArentID},
		dataType: 'json',
		success: function(DATA) {
			if(CallBack) {
				CallBack(DATA);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}

// создание копий и присвоение для них нового родителя
function treeCopyCreateCopy(NodesIDArray, ParentID, CallBack)
{
	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=create-copy',
		type: 'POST',
		data: { nodes_id: NodesIDArray, parent_id: ParentID},
		dataType: 'json',
		success: function(DATA){
			if(CallBack) {
				CallBack(DATA);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}

// сделать массив нод оригиналами
function treeMakeOriginal(Nodes, CallBack)
{
	var NodesID = [];
	$.each(Nodes, function(idx, node){
		NodesID[NodesID.length] = node.id;
	});
	if(NodesID.length)
	{
		$.ajax({
			url: '/admin/admin-site-tree/ajax?method=make-original',
			type: 'POST',
			data: { nodes_id: NodesID},
			dataType: 'json',
			success: function(DATA){
				if(CallBack) {
					CallBack(DATA);
				}
			},
			error: function(a, b, c) {
				admin_dialog('Response error', 'Error', 350);
			}
		});
	}
}

// запрет на выполнение contextmenu
function treeCheckContext(Node)
{
	if(Node.id = '99999999')
		return false;

	return true;
}

// возвращает информацию о доступах к объекту
var treeGetAccessObject
treeGetAccessObject = function(objectID = 0, CallBack) {
	if(objectID) {
		$.ajax({
			url: '/admin/admin-site-tree/ajax?method=get-object-access',
			type: 'POST',
			data: { object_id: objectID},
			dataType: 'json',
			success: function(DATA){
				if(CallBack) {
					CallBack(DATA);
				}
			},
			error: function(a, b, c) {
				admin_dialog('Response error', 'Error', 350);
			}
		});
	}
}

// сортировка вложенных нод
var treeSortNodes = function(nodeID, sortKey, sortASC, CallBack) {
	$.ajax({
		url: '/admin/admin-site-tree/ajax?method=sort-nodes',
		type: 'POST',
		data: { node_id: nodeID, 'sort_key': sortKey, 'sort_asc': sortASC},
		dataType: 'json',
		success: function(DATA) {
			if(CallBack) {
				CallBack(DATA);
			}
		},
		error: function(a, b, c) {
			admin_dialog('Response error', 'Error', 350);
		}
	});
}

// возвращает форму редактирования для ноды
// retur JSON - DATA.node_info, DATA.node_form
var adstEditNodeDialog = function(node, callBack) {
	$.ajax({
		url: '/admin/admin-page/ajax_edit_node?node_id=' + node.id, // url до страницы редактирования
		type: 'POST',
		data: { args: 'empty' },
		dataType: 'json',
		success: function(DATA) {
			remove_loader();
			if(DATA.status == 'OK') {
				adstEditNodeDialogOpen(DATA.node_info.obj_name, DATA.node_form, node);
			} else {
				noty_info('error', DATA.info, 'center');
			}
		},
		error: function(a, b, c) {
			remove_loader();
			console.warn(a);
			console.warn(b);
			console.warn(c);
			admin_dialog('<p>Error response', 'Error', 350);
		}
	});
}

// Открытие диалога для редактирования страницы
// node - нода, по которую надо отобразить в диалоге
var adstEditNodeDialogOpen = function(dTitle, dContent, node) {

	$('#' + DialogBlockID).html(dContent);
	$('#' + DialogBlockID).dialog({
		minWidth: 1000,
		title: dTitle,
		height: 600,
		classes: {
			"ui-dialog": "edit-page"
		},
		buttons: [
			{
				text: "Сохранить",
				//icon: "ui-icon-check",
				class: "ui-btn-save-change",
				click: function() {
					add_loader();
					// сохраним контенты текстовых редакторов
					tinyMCE.triggerSave();
					var $submitForm = $('#' + DialogBlockID + ' form');
					var attrAction = $submitForm.attr('action');
					var formValues = $submitForm.serialize();
					$.ajax({
						url: attrAction,
						type: 'POST',
						data: {node_id: node.id, fom_values: formValues},
						dataType: 'json',
						success: function(DATA) {
							if(DATA.status == 'OK') {
								// уничтожим диалог
								$('#ui-dialog').dialog("destroy").empty();

								// откроем диалог
								adstEditNodeDialog(node);
								noty_info('success', DATA.info, 'topRight');

								// обновим ноду в дереве
								setTimeout(function(){
									treeUpdateNode(node);
								}, 400);
							} else {
								remove_loader();
								noty_info('error', DATA.info, 'center');
							}
						},
						error: function(a, b, c) {
							remove_loader();
							console.warn(a);
							console.warn(b);
							console.warn(c);
						}
					});
				}
			}
		],
		close: function(event, ui) {
			window.onbeforeunload = null;
			// уничтожим диалог
			$('#ui-dialog').dialog("destroy").empty();

			// удалим диалог от datepicker
			$('.date-picker-wrapper').remove();

			// обновим ноду после закрытия окна
			add_loader();
				treeUpdateNode(node, function(){
				remove_loader();
			});
		},
		modal: false,
		open: function( event, ui ) {
			setTimeout(function(){
				ElfinderViewImgInit();
				tinymceRun();
				admin_InitFieldsDate();
				appShSwitch();
				appChosenInit();
			},200);
		}
	});

	//adstEditFormNode(node);
}
/*
##########################
###### --//-- END функции контекстного меню
############################
*/
