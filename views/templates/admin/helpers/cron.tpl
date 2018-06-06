{**
* @author    debuss-a <alexandre@common-services.com>
* @copyright Copyright (c) 2018 Common-Services
* @license   CC BY-SA 4.0
*}

<input type="text" id="module_cron_url" value="{$cron_url|escape:'htmlall':'UTF-8'}" title="Cron URL"><br>

{if $cronjobs_is_installed}
    <input type="hidden" id="cronjobs_url" value="{$cronjobs_url|escape:'htmlall':'UTF-8'}">
    <input type="hidden" id="cronjobs_success" value="{l s='Cron task set up successfully !' mod='lapostesuivi'}">
    <input type="hidden" id="cronjobs_error" value="{l s='Error :' mod='lapostesuivi'} ">
    <input type="hidden" id="cronjobs_not_valide_ajax" value="{l s='Fail ! The script returned an invalid response.' mod='lapostesuivi'} ">

    <button type="button" id="cronjobs_install" class="btn btn-default">
        {l s='Set cron task in Cronjobs module' mod='lapostesuivi'}
    </button>

    <p class="help-block">
        {l s='You can set the cron task in the Cronjobs module of PrestaShop.' mod='lapostesuivi'}<br>
        {l s='Or use the link to set the cron task by yourself on your server or another online tool.' mod='lapostesuivi'}
    </p>
{else}
    <p class="help-block">
        {l s='Please select carriers that can be tracked by La Poste.' mod='lapostesuivi'}<br>
        {l s='Only orders made with these selected carriers will be tracked.' mod='lapostesuivi'}
    </p>
{/if}