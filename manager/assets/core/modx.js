"use strict";
/**
 * jQuery-using MODX JavaScript API.
 * This API handles all the dynamic functionality you'll need to use within the MODX
 * manager and uses jQuery for a bunch of the heavy lifting.
 * @type {Object}
 */
var MODX = {};
(function(MODX, $){

    $.extend(MODX, {
        jQuery: $,

        /**
         * Initiate stuff after all the modules were loaded.
         */
        init: function() {
            /**
             * Add a listener to resize the main container when the
             * window is resized.
             */

            $(window).resize(function() {
                MODX.setContainerHeight()
            }).resize();

            /**
             * Prepare $.ajax to make sure the required modAuth header is set in
             * future $.ajax calls.
             */
            $.ajaxSetup({
                headers: {
                    'modAuth': MODX.auth,
                    'Powered-By': 'MODX Revolution'
                }
            });

            /**
             * Iterate over modules and run each of them. Modules can be anything,
             * but will usually be a certain type of widget (eg tabs, grids, etc).
             */
            $.each(this.modules, function(index, module) {
                module.run();
            });
        },

        /**
         * Request data from MODX-style connector/processors using {jQuery.ajax}.
         *
         * @param connector
         * @param params
         * @param ajaxOptions
         * @return {*}
         * @constructor
         */
        ajax: function(connector, params, ajaxOptions) {
            params = params || {};
            ajaxOptions = $.extend(ajaxOptions,{
                data: params,
                type: 'POST'
            });


            var url = MODX.config.connectors_url;
            if (ajaxOptions.connectors_url) {
                url = ajaxOptions.connectors_url;
            }
            url = url + connector;
            return $.ajax(url, ajaxOptions);
        },

        config: {},

        /**
         * Simple console.log wrapper which only logs if the console is available.
         * This prevents breaking javascript in older versions of IE and allows
         * more advanced logging methods in the future.
         *
         * Multiple arguments are treated as individual console.log statements.
         *
         * If you want use a specific console method (the default is "log"), you can pass
         * it as the first parameter. The following are allowed:
         * - log
         * - debug
         * - info
         * - warn
         * - error
         */
        log: function() {
            var method = 'log';
            if (console) {
                for(var i=0; i<arguments.length; i++) {
                    var val = arguments[i];
                    if ((i == 0) && (-1 < this.jQuery.inArray(val, ['log','debug', 'info', 'warn', 'error']))) {
                        method = val;
                    } else {
                        console[method](val);
                    }
                }
            }
        },

        modules: {},

        setContainerHeight: function() {
            var windowHeight = $(window).outerHeight();
            var topHeight = $('#modx-top').outerHeight();
            var containerHeight = windowHeight - topHeight;
            $('#modx-container').css('height', containerHeight);
        }
    });
})( MODX, jQuery );


/**
 * Attempt to make testing during development somewhat easier by preventing
 * a bunch of errors that simply still need to get fixed.
 */

var Ext = {
    namespace: function() {},
    onReady: function() {}
};
var MODx = $.extend(true, Ext,{
    lang: {},
    config: {},
    load: function() {}
});
