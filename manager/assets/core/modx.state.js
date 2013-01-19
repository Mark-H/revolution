"use strict";

var MODX = MODX || {};
MODX.State = MODX.State || {};
MODX.State._construct = function(modx) {
    this.modx = modx;
    this.data = this.data || {};
    this.set = function(key, value) {
        this.modx.ajax('system/registry/register.php',{
            action: 'send',
            register: 'state',
            topic: '/ys/user-' + this.modx.user.id + '/',
            message: '{"'+key+'":"'+value+'"}',
            message_format: 'json'
        }, {})
    };

    this.get = function(key, def) {
        def = def || undefined;
        if (this.data[key]) {
            return this.data[key];
        } else {
            return def;
        }
    };

    return this;
};

//MODX.State = MODX.State._construct();
