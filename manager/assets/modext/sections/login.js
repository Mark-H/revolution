Ext.onReady(function() {
    if (top.frames.length !== 0) {
        top.location=self.document.location;
    }
    // Ext.override(Ext.form.Field,{
    //     defaultAutoCreate: {tag: "input", type: "text", size: "20", autocomplete: "on" }
    // });

    var forgotPassBtn = document.getElementById('modx-fl-link'),
        backToLoginBtn = document.getElementById('modx-fl-back-to-login-link'),
        loginForm = document.getElementById('modx-login-form'),
        resetForm = document.getElementById('modx-forgot-login-form');

    forgotPassBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addClass(loginForm, 'is-hidden');
        removeClass(loginForm, 'is-visible');
        addClass(resetForm, 'is-visible');
        removeClass(resetForm, 'is-hidden');
        return false;
    });

    backToLoginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addClass(loginForm, 'is-visible');
        removeClass(loginForm, 'is-hidden');
        addClass(resetForm, 'is-hidden');
        removeClass(resetForm, 'is-visible');
        return false;
    });

    // Ext.get('modx-login-language-select').on('change',function(e,cb) {
    //     var p = MODx.getURLParameters();
    //     p.cultureKey = cb.value;
    //     location.href = '?'+Ext.urlEncode(p);
    // });
});

function hasClass(el, className) {
    return el.classList ? el.classList.contains(className) : new RegExp('\\b'+ className+'\\b').test(el.className);
}

function addClass(el, className) {
    if (el.classList) el.classList.add(className);
    else if (!hasClass(el, className)) el.className += ' ' + className;
}

function removeClass(el, className) {
    if (el.classList) el.classList.remove(className);
    else el.className = el.className.replace(new RegExp('\\b'+ className+'\\b', 'g'), '');
}