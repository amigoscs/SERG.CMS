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
		success: function(data){
			console.log(data);
			if(CallBack) {
				CallBack(data);
			}
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
		success: function(data){

			if(CallBack) {
				CallBack(data, AllSelectedNodes);
			}
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
		success: function(data){
			DATA = JSON.parse(data);
			$.each(DATA.node, function(index, elem){
				$TREE.tree('addNodeAfter', elem, node);
			});
			if(CallBack) {
				CallBack(DATA);
			}
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
			success: function(data){
				DATA = JSON.parse(data);
				$.each(NodeRemove, function(idx, elemNode){
					$TREE.tree('removeNode', elemNode);
				});
				remove_loader();
				noty_info('information', DATA.info);
			}
		});
	});
}

// обновление ноды (обновление)
function treeUpdateNode(node, CallBack)
{
	add_loader();
	$.ajax({
		url: TreeDataUrl + '?single_load=1&node=' + node.id,
		type: 'POST',
		data: { value: 'empty'},
		success: function(data){
			DATA = JSON.parse(data);
			$TREE.tree('updateNode', node, DATA[0]);
			// обновим название всех копий ноды
			$TREE.tree('getNodeByCallback', function(otherNode){
				if(otherNode.objectID == node.objectID && otherNode.id != node.id)
					$TREE.tree('updateNode', otherNode, {name: DATA[0].name});
			});
			remove_loader();
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
		success: function(data){
			DATA = JSON.parse(data);
			if(CallBack) {
				CallBack(DATA);
			}
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
		success: function(data){
			DATA = JSON.parse(data);
			if(CallBack) {
				CallBack(DATA);
			}
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
			success: function(data){
				DATA = JSON.parse(data);
				if(CallBack) {
					CallBack(DATA);
				}
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

// возвращает информацию о достапах к объекту
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
			}
		});
	}
}
/*
##########################
###### --//-- END функции контекстного меню
############################
*/
