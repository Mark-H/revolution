"use strict";
/**
 * MODX.modules.Tabs
 * A tabs module, using the Kendo UI (web) TabStrip widget.
 */
(function( MODX ){
    var $ = MODX.jQuery;
    MODX.modules.Tabs = {};
    $.extend(MODX.modules.Tabs, MODX.modules.Base, {
        selector: 'div[data-role=modx-tabs]',
        defaultOptions: {
            stateful: true,
            events: {
                before: [],
                midTransition: [],
                after: []
            },

            /**
             * Defaults inherited from jQuery easytabs
             * For descriptions of each options, see http://os.alfajango.com/easytabs/#configuration
             */
            animate: false, // Changed default from true to false
            panelActiveClass: "active",
            tabActiveClass: "active",
            defaultTab: "li:first-child",
            animationSpeed: "normal",
            tabs: "> ul > li",
            updateHash: false, // Changed default from true to false
            cycle: false,
            collapsible: false,
            collapsedClass: "collapsed",
            collapsedByDefault: true,
            uiTabs: false,
            transitionIn: 'fadeIn',
            transitionOut: 'fadeOut',
            transitionInEasing: 'swing',
            transitionOutEasing: 'swing',
            transitionCollapse: 'slideUp',
            transitionUncollapse: 'slideDown',
            transitionCollapseEasing: 'swing',
            transitionUncollapseEasing: 'swing',
            containerClass: "",
            tabsClass: "",
            tabClass: "",
            panelClass: "",
            cache: true
        },
        iterate: function (element, options) {
            /**
             * Handle stateful tabs. With stateful tabs, the open tab is stored
             * in the state modRegistry through AJAX and the right tab is opened
             * when booting up the tab.
             */
            var id = element.attr('id');
            if (options.stateful && (id.length > 0)) {
                /* Get last active tab */
                var active = MODX.State.get(id);
                console.log(active);
                if (active != undefined) {
                    options.defaultTab = 'li#'+active;
                }
                console.log(options.defaultTab);

                options.events.after.push(function(event, $clicked, $targetPanel, settings) {
                    MODX.State.set(id, $clicked.parent('li').attr('id'));
                });
            }

            element.easytabs(options);

            $.each(options.events, function(eventName, functions) {
                //console.log(eventName, functions);
                $.each(functions, function(index, fn) {
                    element.on('easytabs:' + eventName, fn);
                    //console.log(index, fn);
                });
            });

            //element.kendoTabStrip(options);
        }
    });
})( MODX );
