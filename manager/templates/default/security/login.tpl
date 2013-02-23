<!DOCTYPE HTML>

<html xmlns="http://www.w3.org/1999/xhtml" {if $_config.manager_direction EQ 'rtl'}dir="rtl"{/if}
      lang="{$_config.manager_lang_attribute}"
      xml:lang="{$_config.manager_lang_attribute}"{if $_config.manager_html5_cache EQ 1}
      manifest="{$_config.manager_url}cache.manifest.php"{/if}>
<head>
    <title>{if $_pagetitle}{$_pagetitle} | {/if}{$_config.site_name}</title>
    <meta name="robots" content="noindex, nofollow"/>
    <meta http-equiv="Content-Type" content="text/html; charset={$_config.modx_charset}"/>

{if $_config.manager_favicon_url}
    <link rel="shortcut icon" type="image/x-icon" href="{$_config.manager_favicon_url}"/>{/if}

{if $_config.compress_css}
    <link rel="stylesheet" type="text/css"
          href="{$_config.manager_url}min/index.php?f={$_config.manager_url}templates/default/css/structure.css,{$_config.manager_url}templates/default/css/forms.css,{$_config.manager_url}templates/default/css/style.css,{$_config.manager_url}templates/default/css/login.css"/>
    {else}
    <link rel="stylesheet" type="text/css" href="{$_config.manager_url}templates/default/css/styles.css"/>
    <link rel="stylesheet" type="text/css" href="{$_config.manager_url}templates/default/css/login.css"/>
{/if}

{if $_config.compress_js}
    <script src="{$_config.manager_url}assets/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        alert('Note: this version (the jQuery one) of the manager does not yet support the compress_js setting.');
    </script>
    {else}
    <script src="{$_config.manager_url}assets/jquery/jquery-1.8.3.js" type="text/javascript"></script>
    <script src="{$_config.manager_url}assets/core/modx.js" type="text/javascript"></script>
    <script src="{$_config.manager_url}assets/core/modx.state.js" type="text/javascript"></script>
    <script src="{$_config.manager_url}assets/kendoui/src/js/kendo.web.js" type="text/javascript"></script>
    <script src="{$_config.manager_url}assets/core/modules/base.js" type="text/javascript"></script>
    <script src="{$_config.manager_url}assets/core/modules/tabs.js" type="text/javascript"></script>
    <script src="{$_config.manager_url}assets/core/modules/toggle.js" type="text/javascript"></script>
{/if}

    {$maincssjs}

    {foreach from=$cssjs item=scr}
        {$scr}
    {/foreach}

    <script type="text/javascript">
        $(document).on('ready', function() {
            MODX.init();
        });
    </script>
</head>
<body id="login">

{$onManagerLoginFormPrerender}

<div id="modx-login-container">
    <div id="modx-login-logo">
        <img src="{$_config.manager_url}templates/default/images/style/modx-logo-color.png" alt="" width="180" height="63" />
    </div>

    <div id="modx-login-box">
        <div id="modx-login-box-inner">
            <form id="modx-login-form" action="" method="post">
                <input type="hidden" name="login_context" value="mgr"/>
                <input type="hidden" name="modahsh" value="{$modahsh}"/>
                <input type="hidden" name="returnUrl" value="{$returnUrl}"/>


                <h2>{$_config.site_name}</h2>

                {if $error_message}<p class="error">{$error_message}</p>{/if}





                <div class="clear"></div>

                <div class="row">
                    <div class="half">
                        <label>
                            <input type="text" id="modx-login-username" name="username" tabindex="1"
                               autocomplete="on" value="{$_post.username}"
                               placeholder="{$_lang.login_username}"/>
                        </label>

                        <label class="checkbox">
                            <input type="checkbox" id="modx-login-rememberme" name="rememberme"
                               tabindex="3" autocomplete="on" {if $_post.rememberme}checked="checked"{/if}
                               value="1"/>
                            {$_lang.login_remember}
                        </label>
                    </div>
                    <div class="last half">
                        <label>
                            <input type="password" id="modx-login-password" name="password" tabindex="2"
                                 autocomplete="on" placeholder="{$_lang.login_password}"/>
                        </label>

                        {if $allow_forgot_password}
                        <a href="javascript:void(0);" id="modx-forgot-login-link"
                           style="{if $_post.username_reset}display:none;{/if}"
                           data-role="toggle" data-target="#modx-forgot-login" data-hide-trigger="true"
                                >{$_lang.login_forget_your_login}</a>
                        {/if}
                    </div>
                </div>

                {$onManagerLoginFormRender}

                <div class="clear"></div>

                <button class="modx-login-button" name="login" type="submit" value="1"
                    id="modx-login-btn" tabindex="4">{$_lang.login_button}</button>
            </form>

            <div class="clear"></div>

            {if $allow_forgot_password}
            <form id="modx-forgot-login" action="" method="post" {if NOT $_post.username_reset}style="display: none;"{/if}>
                <div class="row">
                    <div class="half">
                        <label>
                            <input type="text" id="modx-login-username-reset" name="username_reset"
                                   value="{$_post.username_reset}" placeholder="{$_lang.login_username}"/>
                        </label>
                    </div>
                    <div class="last half">
                        <button class="modx-login-button" name="forgotlogin" type="submit"
                                value="1" id="modx-forgot-login-button">{$_lang.login_send_activation_email}</button>
                    </div>
                </div>
            </form>
            {/if}

            <div class="clear"></div>
        </div>
    </div>

    <p class="loginLicense">{$_lang.login_copyright}</p>
</div>

<div id="modx-login-language">
    <form action="{$returnUrl}" method="get" id="modx-login-language-form" >
        <label>{$language_str}:
            <select name="cultureKey" onchange="document.getElementById('modx-login-language-form').submit();">
            {$languages}
            </select>
        </label>
    </form>
</div>

</body>
</html>
