/**
 * MODX.modules.Tree
 * A tree module, using the Kendo UI (web) TabStrip widget.
 */
(function( MODX ){
    "use strict";
    var $ = MODX.jQuery;
    MODX.modules.Tree = MODX.modules.Tree || {
        run: function() {
            $.each(this, function (index, tree) {
                if (tree.run) {
                    tree.run();
                }
            });
        },
        Base: {}
    };
    $.extend(true, MODX.modules.Tree.Base, MODX.modules.Base, {
        selector: 'div[data-role=modx-tree]',
        defaultOptions: {
            stateful: true,

            /* For remote data */
            remote: false,
            connectorUrl: '',
            connector: '',
            action: '',
            schema: {},

            /* For local data */
            data: null
        },

        // http://docs.kendoui.com/api/web/treeview#events
        events: {
            collapse: [],
            dataBound: [],
            drag: [],
            dragend: [],
            dragstart: [],
            drop: [],
            expand: [],
            select: [],
            navigate: []
        },

        getDataSource: function (options) {
            /**
             * Override this method for your data source. For an example of a remote datasource,
             * see the Resources tree below.
             */
            return [{
                text: "Item 1", expanded: true, items: [
                    { text: "Item 1.1" },
                    { text: "Item 1.2" },
                    { text: "Item 1.5" }
                ]},
                { text: "Item 2", items: [
                    { text: "Item 2.1" },
                    { text: "Item 2.2" },
                    { text: "Item 2.3" }
                ]},
                { text: "Item 3" }
            ];
        },
        iterate: function (element, options) {
            if (!options.connectorUrl.length) options.connectorUrl = MODX.config.connectors_url;
            options.dataSource = options.data || this.getDataSource(options);
            var tree = element.kendoTreeView(options).data('kendoTreeView');

            /** Assign events */
            $.each(this.events, function(eventName, functions) {
                tree.bind(eventName, functions);
            });

            element.data('tree', tree);
        }
    });
})( MODX );



(function( MODX ){
    "use strict";
    var $ = MODX.jQuery;
    MODX.modules.Tree.Resources = {};

    $.extend(true, MODX.modules.Tree.Resources, MODX.modules.Tree.Base, {
        selector: 'div[data-role=modx-tree-resources]',
        defaultOptions: {
            remote: true,
            connector: 'resource/index.php',
            action: 'getNodes',
            schema: {
                model: {
                    id: "id",
                    hasChildren: "hasChildren"
                }
            }
        },

        events: {
            select: function(e) {
                var node = e.node,
                    $node = $(node),
                    data = this.dataItem(node);
                var $menu = MODX.modules.Tree.Resources.getMenu(data);
                $menu.appendTo($node);
            }
        },

        getMenu: function(data) {
            var menu = $('<div />'),
                html = [];
            menu.attr('id', 'menu-' + data.id);

            html.push('ID: ' + data.pk);

            if (data.preview_url) {
                html.push('<a href="'+data.preview_url+'">View</a>');
            }
            if (data.page) {
                html.push('<a href="'+data.page+'">Edit</a>');
            }

            html = html.join(' | ');

            menu.html(html);
            return menu;
        },

        getDataSource: function(options) {
            var url = options.connectorUrl + options.connector,
                data = $.extend({action: options.action}, options.data);
            return new kendo.data.HierarchicalDataSource({
                transport: {
                    read: {
                        url: url,
                        dataType: 'json',
                        data: data
                    }
                },
                schema: options.schema
            });
        }
    });


}) ( MODX );
