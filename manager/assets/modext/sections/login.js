Ext.onReady(function() {
    if (top.frames.length !== 0) {
        top.location=self.document.location;
    }
    Ext.override(Ext.form.Field,{
        defaultAutoCreate: {tag: "input", type: "text", size: "20", autocomplete: "on" }
    });    
    var fl = Ext.get('modx-fl-link');
    if (fl) { fl.on('click',MODx.loadFLForm); }
    var ll = Ext.get('modx-fl-link');
    if (ll) { ll.on('click',MODx.loadFLForm); }

    // Ext.get('modx-login-language-select').on('change',function(e,cb) {
    //     var p = MODx.getURLParameters();
    //     p.cultureKey = cb.value;
    //     location.href = '?'+Ext.urlEncode(p);
    // });
});

MODx.loadFLForm = function(a) {
    Ext.get('modx-fl-link').set({'class':'is-hidden'});
    Ext.get('modx-login-form').set({'class':'is-hidden'});
    Ext.get('modx-forgot-login-form').set({'class':'is-visible'});
};

MODx.hideFLForm = function(a) {
    Ext.get('modx-fl-link').set({'class':'is-visible'});
    Ext.get('modx-login-form').set({'class':'is-visible'});
    Ext.get('modx-forgot-login-form').set({'class':'is-hidden'});
};