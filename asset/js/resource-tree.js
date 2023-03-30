$(document).ready( function() {

    // Initialize the navigation tree
    $.jstree.plugins.removenode = function(options, parent) {
        var removeIcon = $('<i>', {
            class: 'jstree-icon jstree-removenode-remove',
            attr:{role:'presentation'}
        });
        this.redraw_node = function(node, deep, is_callback, force_render) {
            node = parent.redraw_node.apply(this, arguments);
            if (node) {
                // Add remove/undo icons to every node.
    //            var nodeJq = $(node);
    //            var anchor = nodeJq.children('.jstree-anchor');
    //            var removeIconClone = removeIcon.clone();
    //            anchor.append(removeIconClone);
            }
            return node;
        };
    };
    var itemSetTree = $('#item-set-tree');
    var initialTreeData;
    loadJsTree();
    var moveToResource = function (itemUrl, resourceId) {
        startSearch();
        var url = itemUrl;
        url += "/" + resourceId;
        window.location.href = url;
    }
    var openNode = function (treeData, targetDepth, currentDepth, selectedId) {
        for (var i = 0; i < treeData.length; i++) {
            if (treeData[i].data.data.id == selectedId) {
                continue;
            }
            // select node
            if (targetDepth > currentDepth) {
                itemSetTree.jstree(true).open_node(treeData[i].id);

            } else {
                continue;
            }
            if (treeData[i].children.length > 0) {
                var nextDepth = currentDepth + 1;
                openNode(treeData[i].children, targetDepth, nextDepth);
            }
        }
    }
    var checkSelectedNode = function (treeData, selectedId) {
        for (var i = 0; i < treeData.length; i++) {
            var selected = false;
            // select node
            if (treeData[i].data.data.id == selectedId) {
    //            treeData[i].state.selected = true;
                itemSetTree.jstree(true).select_node(treeData[i].id);
                return true
            }
            if (treeData[i].children.length > 0) {
                if (checkSelectedNode (treeData[i].children, selectedId)) {
    //                treeData[i].state.selected = true;
    //                treeData[i].state.opened = true;
                    itemSetTree.jstree(true).select_node(treeData[i].id);
                    itemSetTree.jstree(true).open_node(treeData[i].id);
                    return true;
                }
            }
        }

    }
    var loaded = false;
    function loadJsTree() {
        $.getJSON(
                itemSetTree.data('jstree-data'),
                function (data) {
                    itemSetTree.jstree({
                        'animation' : 1,
                        'core': {
                            'check_callback': true,
                            'data': data,
                        },
    //				    'types' : {
    //				        'default' : { 'icon' : 'folder' },
    //				        'file' : { 'valid_children' : [], 'icon' : 'file' }
    //				    },
                        'plugins': ['removenode']
                    }).on('loaded.jstree', function() {
                        // Open all nodes by default.
                        itemSetTree.jstree(true).close_all();
                        initialTreeData = JSON.stringify(itemSetTree.jstree(true).get_json());
                        openNode(itemSetTree.jstree(true).get_json(), itemSetTree.data('default-depth'), 1, itemSetTree.data('selected-id'));
                        checkSelectedNode(itemSetTree.jstree(true).get_json(), itemSetTree.data('selected-id'));
                        loaded = true;
                    }).on('changed.jstree', function(e, data) {
                        // Open node after moving it.
                        if (loaded) {
                            if (data.action != 'ready') {
                                if (itemSetTree.data('selected-id') != data.node.data.data.id) {
                                    var url = itemSetTree.data('item-url');
    //				                if (data.node.data.type == itemSetTree.data('folder-class') ||
    //				                        data.node.data.type == itemSetTree.data('search-folder-class') ) {
    //				                    url = itemSetTree.data('item-children-url');
    //				                }
                                    moveToResource(url,
                                            data.node.data.data.id);
                                }
                            }
                        }
                    });
                }
            );
    }





    });
