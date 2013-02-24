"use strict";
/**
 * MODX.modules.Toggle
 * Simple module to create links that toggle visibility of another element.
 */
(function( MODX ){
    var $ = MODX.jQuery;
    MODX.modules.Toggle = {};
    $.extend(true, MODX.modules.Toggle, MODX.modules.Base, {
        selector: '[data-role=toggle]',
        defaultOptions: {
            hideTrigger: false,
            animated: true,
            target: null
        },
        iterate: function (element, options) {
            element.click(function() {
                var $target = $(options.target);

                if ($target) {
                    if (options.animated) {
                        $target.slideToggle();
                        if (options.hideTrigger) {
                            element.fadeOut();
                        }
                    }
                    /** Not animated */
                    else {
                        $target.toggle();
                        if (options.hideTrigger) {
                            element.hide();
                        }
                    }
                } else {
                    MODX.log('error','Toggle target not found. Trigger: ' + element + ' | Target: ' + options.target);
                }
            });
        }
    });
})( MODX );
