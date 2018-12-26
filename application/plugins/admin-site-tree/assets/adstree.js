$(document).ready(function(e){
	var CurrentObjType = 0, $CurSelector, CurentNode, AllNodes, PrevNode, ParentNode;

	$TREE.tree({
		dataUrl: TreeDataUrl, // объявлена в adstreefunc.js
		dragAndDrop: true,
		saveState: true,
		onCanMove: adstStartDraggable,
		useContextMenu: true,
		onIsMoveHandle: function($element) {
			// Only dom elements with 'jqtree-title' class can be used
			// as move handle.
			return ($element.is('.jqtree-title'));
		},
		onCreateLi: function(node, $li) {
			CurrentObjType = node.objType;
			// Add 'icon' before title
			var $ElemLiTitle = $li.find('.jqtree-title');
			if(TreeDataTypesIcons['icon_' + CurrentObjType]) {
				$li.addClass('st-' + node.objStatus);
				$ElemLiTitle.attr('data-type-obj', CurrentObjType).before('<img class="img-icon" src="' + TreeDataTypesIcons.icons_path + TreeDataTypesIcons['icon_' + CurrentObjType] + '"/>');
			}

			if(node.dataFields.length) {
				var tText = '';
				$.each(node.dataFields, function(idx, elem) {
					tText += '<i>' + elem + '</i>';
				});
				$ElemLiTitle.after(tText);
			}

			if(node.copyType == 'copy') {
				$ElemLiTitle.addClass('e-copy');
			}

			if(node.objectAccess !== 'ALL') {
				$ElemLiTitle.before('<i class="fa fa-lock" aria-hidden="true"></i>');
			}

			if(node.isPublish) {
				$ElemLiTitle.before('<i class="fa fa-clock-o" aria-hidden="true" title="' + node.isPublish + '"></i>');
			}
		}

    });

	/*#############
	context menu
	#############*/
	var TREECONTEXTMENU = $TREE.jqTreeContextMenu($('#adstreemenu'), {
		// изменить статус видимости
		'status': function (node) {
			add_loader();
			treeChangeStatus(node, function(DATA, nodes){
				//console.log(DATA.object_status);
				$.each(DATA.object_status, function(idx, status){
					Nodes = $TREE.tree('getNodesByProperty', 'objectID', idx);
					$.each(Nodes, function(idx, node){
						$(node.element).removeClass('st-publish st-hidden').addClass('st-' + status);
					});
				});
				remove_loader();
			});
		},
		// редактировать
		'edit': function (node) {
					//Показывать редактирование в новой вкладке
					window.open('/admin/admin-page/edit?node_id=' + node.id, '_blank');
				},
		"delete": function (node) { return treeDeleteNode(node); },
		// открыть на сайте в новой вкладке
		'on-site': function (node) {
			// соберем url
			var NodeUrl = treeGetUrlsTree(node, NodeUrl);
			// удалим последний слэш
			NodeUrl = NodeUrl.slice(0,NodeUrl.length-1);
			// в массив. Надо убрать index
			AllNodes = NodeUrl.split('/');
			AllNodes.map(function(o, el){
				if((o == 'index'))
					AllNodes.splice(el, 1);
			});
			// обратные порядок
			AllNodes.reverse();
			window.open('/' + AllNodes.join('/'), '_blank');

		},
		// создать копию ноды
		'copy_copy': function(node){
			return treeCopyObject(node, 'copy_copy', function(res){
				noty_info('information', DATA.info, 'topRight');
			});
		 },
		// создать копию ноды с подразделами
		'copy_copy_childs': function(node){
			noty_comfirm(cmsGetLang('astCopyGoupQ'), cmsGetLang('astBtnContinue'), cmsGetLang('astBtnCancel'), function(r){
				if(r){
					return treeCopyObject(node, 'copy_copy_childs', function(res){
						noty_info('information', DATA.info, 'topRight');
					});
				}
			});
		},
		// создать копию ноды и объекта
		'copy_obj': function(node){
			return treeCopyObject(node, 'copy_obj', function(res){
				noty_info('information', DATA.info, 'topRight');
			});
		 },
		// создать копию ноды и объекта со всеми потомками
		'copy_obj_childs': function(node){
			noty_comfirm(cmsGetLang('astCopyGoupQ'), cmsGetLang('astBtnContinue'), cmsGetLang('astBtnCancel'), function(r){
				if(r){
					return treeCopyObject(node, 'copy_obj_childs', function(res){
						noty_info('information', DATA.info, 'topRight');
					});
				}
			});
		},
		// экспорт всех выделенных нод
		'export_selected': function(node){
			AllNodes = $TREE.tree('getSelectedNodes');
			var UrlNodes = [];
			$.each(AllNodes, function(idx, elem){
				UrlNodes[UrlNodes.length] = elem.id;
			});

			window.open(TreeExportUrl + '?node_id=' + UrlNodes.join('-'), '_blank');
		},
		// экспорт всех вложенных нод
		'export_childs': function(node){
			window.open(TreeExportUrl + '?parent_node_id=' + node.id, '_blank');
		},
		// техническая информация о ноде
		'get-info': function (node){
			var HtmlData = '';
			HtmlData += '<div><span>' + cmsGetLang('astNodeID') + ':</span> <span>' + node.id + '</span></div>';
			HtmlData += '<div><span>' + cmsGetLang('astObjID') + ':</span> <span>' + node.objectID + '</span></div>';
			HtmlData += '<div><span>' + cmsGetLang('astNameObj') + ':</span> <span>' + node.name + '</span></div>';
			HtmlData += '<div><span>' + cmsGetLang('astStatusObj') + ':</span> <span>' + node.objStatus + '</span></div>';
			HtmlData += '<div><span>' + cmsGetLang('astTypeNode') + ':</span> <span>' + node.objType + '</span></div>';
			HtmlData += '<div><span>' + cmsGetLang('astUrlNode') + ':</span> <span>' + node.objURL + '</span></div>';

			$('#'+DialogBlockID).html(HtmlData);
			$('#'+DialogBlockID).dialog({
				minWidth: 600,
				title: cmsGetLang('astInfoTitle'),
				height: 300,
			});
		},
		'update': function(node) {
			$TREE.tree('loadDataFromUrl', node);
		},
		// Группировка нод для отправки
		'group-nodes': function (node){
			// группировать можно в пределах одного родителя
			AllNodes = $TREE.tree('getSelectedNodes');
			var NewGroupNode = {
				name: 'GROUP NODES',
				id: IDGroupNode,
				objStatus: 'publish',
				objType: node.objType,
				objectID: 'GROUP',
				objURL: '',
				copyType: 'copy',
				dataFields: {}
			};
			$TREE.tree('addNodeAfter', NewGroupNode, node);

			ParentNode = $TREE.tree('getNodeById', IDGroupNode);
			$.each(AllNodes, function(idx, elem){
				$TREE.tree('appendNode', elem, ParentNode);
			})

			// удалим из дерево выделенные ноды
			$.each(AllNodes, function(idx, elem){
				$TREE.tree('removeNode', elem);
			})

		},
		// сделать выделенные ноды оригиналами
		'orig-nodes': function (node){
			// все выделенные ноды
			TextConfirm = '<p>' + cmsGetLang('astInfoCrOrigNodeInfo1') + '.</p> <p><strong>' + cmsGetLang('astInfoCrOrigNodeInfo2') + '!</strong></p>';
			noty_comfirm(TextConfirm, cmsGetLang('astBtnContinue'), cmsGetLang('astBtnCancel'), function(r){
				if(r){
					add_loader();
					AllNodes = $TREE.tree('getSelectedNodes');
					treeMakeOriginal(AllNodes, function(result){
						if(result.status == '200'){
							$TREE.tree('reload');
							//document.location.reload();
						}else{
							noty_info('warning', result.info, 'topRight');
						}
						remove_loader();
					});
				}
			});
		},
		'sort_name_asc' : function(node) {
			add_loader();
			treeSortNodes(node.id, 'obj_name', 'ASC', function(DATA) {
				remove_loader();
				if(DATA.status == 200) {
					noty_info('success', DATA.info, 'topRight');
					$TREE.tree('loadDataFromUrl', node);
				} else {
					noty_info('error', DATA.info, 'center');
				}
			});
		},
		'sort_name_desc' : function(node) {
			add_loader();
			treeSortNodes(node.id, 'obj_name', 'DESC', function(DATA) {
				remove_loader();
				if(DATA.status == 200) {
					noty_info('success', DATA.info, 'topRight');
					$TREE.tree('loadDataFromUrl', node);
				} else {
					noty_info('error', DATA.info, 'center');
				}
			});
		},

	});

	TREECONTEXTMENU.disable('GROUP NODES', ['status','edit','delete','on-site','copy_copy','copy_copy_childs','copy_obj','copy_obj_childs','export-nodes','group-nodes','orig-nodes', 'sort_name_asc', 'sort_name_desc']);
	/*#############
	--//-- context menu
	#############*/



	/*multiselect*/
	$TREE.on('tree.click', function(event) {
		event.preventDefault();
		// если нажаn CTRL
		if(event.click_event.ctrlKey)
		{
			CurentNode = event.node;

			if($TREE.tree('isNodeSelected', CurentNode)){
				$TREE.tree('removeFromSelection', CurentNode);
			}else{
				$TREE.tree('addToSelection', CurentNode);
			}

			AllNodes = $TREE.tree('getSelectedNodes');
			noty_info('alert', cmsGetLang('astInfoSelElems') + ': ' + AllNodes.length, 'topRight');
		}
		// если нажат SHIFT
		else if(event.click_event.shiftKey)
		{
			if(!PrevNode){
				CurentNode = event.node;

				AllNodes = $TREE.tree('getSelectedNodes');
				$.each(AllNodes, function(idx, node){
					$TREE.tree('removeFromSelection', node);
				});
				$TREE.tree('addToSelection', CurentNode);
				PrevNode = CurentNode;
				return;
			}

			//PrevNode = CurentNode;
			CurentNode = event.node;

			// выделение по SHIFT должно быть в пределах одного родителя
			if(PrevNode.parent == CurentNode.parent)
			{
				ParentNode = CurentNode.parent;
				var StartStop = false;
				for(var i=0; i < ParentNode.children.length; i++) {
					if((ParentNode.children[i] == PrevNode) || (ParentNode.children[i] == CurentNode))
						StartStop = !StartStop;

					if(StartStop)
						$TREE.tree('addToSelection', ParentNode.children[i]);

					// текущую ноду тоже в селект
					$TREE.tree('addToSelection', CurentNode);
				}
				AllNodes = $TREE.tree('getSelectedNodes');
				noty_info('alert', cmsGetLang('astInfoSelElems') + ': ' + AllNodes.length, 'topRight');
			}
			else
			{
				noty_info('alert', cmsGetLang('astInfoElemsOneParent'), 'center');
				return;
			}
			// снимем выделение с текста
			if (window.getSelection) {
				window.getSelection().removeAllRanges();
			} else { // старый IE
				document.selection.empty();
			}
		}
		else
		{
			CurentNode = event.node;
			AllNodes = $TREE.tree('getSelectedNodes');
			$.each(AllNodes, function(idx, node){
				$TREE.tree('removeFromSelection', node);
			});
			$TREE.tree('addToSelection', CurentNode);

			// если клик по иконке замочка
			if($(event.click_event.originalEvent.target).hasClass('fa-lock')) {
				treeGetAccessObject(CurentNode.objectID, function(DATA) {
					if(DATA.status == '200') {
						noty_info('alert', DATA.info, 'center');
					} else {
						noty_info('error', DATA.info, 'center');
					}
				});
			}

			// если клик по иконке часов
			if($(event.click_event.originalEvent.target).hasClass('fa-clock-o')) {
				var text = $(event.click_event.originalEvent.target).attr('title');
				noty_info('alert', text, 'center');
			}
		}

		PrevNode = CurentNode;

	});

	/*двойной клик по ноде*/
	$TREE.on('tree.dblclick', function(event) {
		// event.node is the clicked node
		var node = event.node;
		add_loader();
		adstEditNodeDialog(node);
    });


	/* перемещение*/
	$TREE.on('tree.move',function(event){
		event.preventDefault();
		// если позиция внутрь(inside), то parent должен быть target_node.id, иначе target_node.parent.id,

		if(event.move_info.position == 'inside') {
			ParentNode = event.move_info.target_node.id;
		} else {
			ParentNode = event.move_info.target_node.parent.id;
		}

		if(!ParentNode) {
			ParentNode = '0';
		}

		// Если перетаскиваем группу нод для копирования
		if(event.move_info.moved_node.id == IDGroupNode)
		{
			noty_comfirm_sel(cmsGetLang('astMoveGroup'), cmsGetLang('astMoveGroupBut1'), cmsGetLang('astMoveGroupBut2'), cmsGetLang('astMoveButCancel'), function(r){
				// перемещаем оригиналы
				if(r == 'one')
				{
					var ChildrenNodesID = [];
					$.each(event.move_info.moved_node.children, function(idx, elem){
						ChildrenNodesID[ChildrenNodesID.length] = elem.id;
					});
					// замена родителя
					treeChangeParentNodes(ChildrenNodesID, ParentNode, function(data){
						remove_loader();
						if(data.status == '200') {
							noty_info('information', data.info, 'topRight');
						} else {
							noty_info('warning', data.info, 'topRight');
						}

						// перезагрузка дерева
						$TREE.tree('reload', function() {});
					});
					return true;
				}
				// перемещаем как копии
				else if(r == 'two')
				{
					var ChildrenNodesID = [];
					$.each(event.move_info.moved_node.children, function(idx, elem){
						ChildrenNodesID[ChildrenNodesID.length] = elem.id;
					});
					treeCopyCreateCopy(ChildrenNodesID, ParentNode, function(DATA){
						if(DATA.status == '200') {
							noty_info('information', DATA.info, 'topRight');
						} else {
							noty_info('warning', DATA.info, 'topRight');
						}
						// перезагрузка дерева
						$TREE.tree('reload');
					})
					return true;
				}
				else
				{
					return false;
				}
			})
		}
		else
		{
			noty_comfirm(cmsGetLang('astMoveContQ'), cmsGetLang('astBtnContinue'), cmsGetLang('astBtnCancel'), function(r){
				if(r) {
					add_loader();
					event.move_info.do_move();
					SaveOrderNodes(event.move_info.moved_node, ParentNode, function(DATA){
						if(DATA.status == '200'){
							noty_info('information', DATA.info, 'topRight');
						}else{
							noty_info('warning', DATA.info, 'topRight');
						}
						remove_loader();
					});
				}else{
					noty_info('notification', cmsGetLang('astBtnCancel'), 'topRight');
				}
			});
		}
	});
});
