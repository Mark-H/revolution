document.addEventListener('DOMContentLoaded', function () {
    // Break out of frames
    if (top.frames.length !== 0) {
        top.location = self.document.location;
    }

    var forgotPassBtn = document.getElementById('modx-fl-link'),
        backToLoginBtn = document.getElementById('modx-fl-back-to-login-link'),
        loginForm = document.getElementById('modx-login-form'),
        loginFormUser = document.getElementById('modx-login-username'),
        resetForm = document.getElementById('modx-forgot-login-form'),
        resetFormUser = document.getElementById('modx-login-username-reset'),
        changeLanguage = document.getElementById('modx-login-language-select'),
        errors = document.querySelectorAll('.is-error');

    // When clicking on the forgot password button, swap out the forms
    if (forgotPassBtn) {
        forgotPassBtn.addEventListener('click', function (e) {
            e.preventDefault();
            addClass(loginForm, 'is-hidden');
            removeClass(loginForm, 'is-visible');
            addClass(resetForm, 'is-visible');
            removeClass(resetForm, 'is-hidden');
            resetFormUser.focus();
            removeErrors();
            return false;
        });
    }

    // Also swap out in the reverse direction when clicking the back to login button
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function (e) {
            e.preventDefault();
            addClass(loginForm, 'is-visible');
            removeClass(loginForm, 'is-hidden');
            addClass(resetForm, 'is-hidden');
            removeClass(resetForm, 'is-visible');
            loginFormUser.focus();
            removeErrors();
            return false;
        });
    }

    // Change language
    if (changeLanguage) {
        changeLanguage.addEventListener('change', function (e) {
            var params = {};
            location.search.substr(1).split('&').forEach(function (item) {
                if (item != '') {
                    params[item.split('=')[0]] = item.split('=')[1]
                }
            });
            params['manager_language'] = e.target.value;
            var url = [];
            for (var i in params) {
                if (params.hasOwnProperty(i)) {
                    url.push(i + '=' + params[i]);
                }
            }
            document.location = document.location.pathname + '?' + url.join('&');
        });
    }


    function addClass(el, className) {
        if (el.classList) {
            el.classList.add(className);
        }
        else if (!hasClass(el, className)) {
            el.className += ' ' + className;
        }
    }

    function removeClass(el, className) {
        if (el.classList) {
            el.classList.remove(className);
        }
        else {
            el.className = el.className.replace(new RegExp('\\b' + className + '\\b', 'g'), '');
        }
    }

    function removeErrors() {
        errors.forEach(function (item) {
            item.remove()
        });
    }
});