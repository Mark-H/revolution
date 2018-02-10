<!doctype html>
<html {if $_config.manager_direction EQ 'rtl'}dir="rtl"{/if} lang="{$_config.manager_lang_attribute}" xml:lang="{$_config.manager_lang_attribute}">
    <head>
        <meta charset="{$_config.modx_charset}">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>{$_lang.login_title} | {$_config.site_name|strip_tags|escape}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="robots" content="noindex, nofollow">

        {if $_config.manager_favicon_url}
            <link rel="shortcut icon" type="image/x-icon" href="{$_config.manager_favicon_url}" />
            <link rel="apple-touch-icon" href="{$_config.manager_favicon_url}">
        {/if}

        <link rel="stylesheet" type="text/css" href="{$_config.manager_url}templates/default/css/login{if $_config.compress_css}-min{/if}.css" />

        {if isset($_config.ext_debug) && $_config.ext_debug}
            <script src="{$_config.manager_url}assets/ext3/adapter/ext/ext-base-debug.js" type="text/javascript"></script>
            <script src="{$_config.manager_url}assets/ext3/ext-all-debug.js" type="text/javascript"></script>
        {else}
            <script src="{$_config.manager_url}assets/ext3/adapter/ext/ext-base.js" type="text/javascript"></script>
            <script src="{$_config.manager_url}assets/ext3/ext-all.js" type="text/javascript"></script>
        {/if}
        <script src="{$_config.manager_url}assets/modext/core/modx.js" type="text/javascript"></script>
        <script src="{$_config.manager_url}assets/modext/modx.jsgrps-min.js" type="text/javascript"></script>
        <script src="{$_config.manager_url}assets/modext/sections/login.js" type="text/javascript"></script>

    </head>
    <body id="login">
        {$onManagerLoginFormPrerender}

        <main>
            <img alt="MODX CMS/CMF" src="{$logo}" class="modx-logo">
            <a href="#help" class="help-link">{$_lang.login_help_button_text}</a>

            <div id="help">
                <h2>{$_lang.login_help_title}</h2>
                {$_lang.login_help_text}
            </div>

            <h1>{$greeting}</h1>

            <form id="modx-login-form" class="can-toggle {if $_post.username_reset|default}is-hidden{/if}" action="" method="post">
                <input type="hidden" name="login_context" value="mgr" />
                <input type="hidden" name="modahsh" value="{$modahsh|default}" />
                <input type="hidden" name="returnUrl" value="{$returnUrl}" />

                <p class="lead">{$_lang.login_note}</p>

                {if $error_message|default}
                    <p class="error">{$error_message}</p>
                {/if}

                <label>
                    {$_lang.login_username}
                    <input type="text" id="modx-login-username" name="username" autocomplete="on" autofocus value="{$_post.username|default}" required>
                </label>

                <label>
                    {$_lang.login_password}
                    <input type="password" id="modx-login-password" name="password" autocomplete="on" required>
                </label>

                <label>
                    <input type="checkbox" id="modx-login-rememberme" name="rememberme" autocomplete="on" {if $_post.rememberme|default}checked="checked"{/if} value="1">
                    {$_lang.login_remember}
                </label>

                {$onManagerLoginFormRender}

                <button class="button" id="modx-login-btn" name="login" type="submit" value="1">{$_lang.login_button}</button>

                {if $allow_forgot_password|default}
                    <button class="button outline" id="modx-fl-link" name="forgotpassword">{$_lang.login_forget_your_login}</button>
                {/if}
            </form>

            {if $allow_forgot_password|default}
                <form action="" method="post" id="modx-forgot-login-form" class="can-toggle {if NOT $_post.username_reset|default}is-hidden{/if}">
                    <p class="lead">{$_lang.login_forgotpassword_note}</p>

                    {if $error_message|default}
                        <p class="error">{$error_message}</p>
                    {/if}

                    <label>
                        {$_lang.login_username_or_email}
                        <input type="text" id="modx-login-username-reset" name="username_reset" class="x-form-text x-form-field" value="{$_post.username_reset|default}">
                    </label>

                    <button class="button" name="forgotlogin" type="submit" value="1" id="modx-fl-btn">{$_lang.login_send_activation_email}</button>

                    {if $allow_forgot_password|default}
                        <button name="modx-fl-back-to-login-link" id="modx-fl-back-to-login-link" class="button outline">{$_lang.login_back_to_login}</button>
                    {/if}
                </form>
            {/if}

        </main>

        <div class="background" style="background-image:url({$background})"></div>
    </body>
</html>
