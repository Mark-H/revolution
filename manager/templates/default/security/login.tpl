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
            <img alt="MODX CMS/CMF" src="{$_config.manager_url}templates/default/images/modx-logo-color.svg" class="modx-logo">

            <form class="modx-login-form" action="" method="post">
                <h1>{$greeting}</h1>
                <p>{$_lang.login_note}</p>

                <label>
                    {$_lang.login_username}
                    <input type="text" name="username" autocomplete="on" autofocus value="{$_post.username|default}" required>
                </label>

                <label>
                    {$_lang.login_password}
                    <input type="text" name="password" autocomplete="on" required>
                </label>

                <label>
                    <input type="checkbox" name="rememberme" autocomplete="on" {if $_post.rememberme|default}checked="checked"{/if} value="1">
                    {$_lang.login_remember}
                </label>

                <button class="button" name="login" type="submit" value="1">{$_lang.login_button}</button>
            </form>
        </main>

        <div class="background" style="background-image:url({$_config.manager_url}templates/default/images/login/default-background.jpg)"></div>
    </body>
</html>
