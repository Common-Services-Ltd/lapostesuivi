{**
* @author    debuss-a <alexandre@common-services.com>
* @copyright Copyright (c) 2018 Common-Services
* @license   CC BY-SA 4.0
*}

<div class="box">
    <table class="table table-bordered">
        <thead class="thead-default">
            <tr>
                <th>{l s='Tracking number' mod='lapostesuivi'}</th>
                <th>{l s='Last update' mod='lapostesuivi'}</th>
                <th>{l s='Status' mod='lapostesuivi'}</th>
                <th></th>
            </tr>
        </thead>
        <tbody><tr>
                <td>
                    {$lps_tracking->code|escape:'htmlall':'UTF-8'}
                </td>
                <td>
                    {date('d-m-Y', strtotime($lps_tracking->date))|escape:'htmlall':'UTF-8'}
                </td>
                <td>
                    {$lps_tracking->message|escape:'htmlall':'UTF-8'}
                </td>
                <td class="text-xs-right">
                    <a href="{$lps_tracking->link|escape:'htmlall':'UTF-8'}" class="btn btn-sm btn-primary" target="_blank">
                        <i class="icon-external-link"></i>
                        {l s='Track' mod='lapostesuivi'}
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>