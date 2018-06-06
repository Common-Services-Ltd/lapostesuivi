{**
* @author    debuss-a <alexandre@common-services.com>
* @copyright Copyright (c) 2018 Common-Services
* @license   CC BY-SA 4.0
*}

<li id="lapostesuivi_li">
    <a href="#lapostesuivi">
        <i class="icon-barcode"></i>
        La Poste Suivi
        <span class="badge">{if Validate::isLoadedObject($lps_tracking) && $lps_tracking->status}1{else}0{/if}</span>
    </a>
</li>

<div class="tab-pane" id="lapostesuivi">
    <h4 class="visible-print">
        La Poste Suivi
        <span class="badge">
            ({if Validate::isLoadedObject($lps_tracking) && $lps_tracking->status}1{else}0{/if})
        </span>
    </h4>

    {if Validate::isLoadedObject($lps_tracking) && $lps_tracking->status}
        <div class="form-horizontal">
            <div class="table-responsive">
                <table class="table" id="shipping_table">
                    <thead>
                        <tr>
                            <th>
                                <span class="title_box ">{l s='Last update' mod='lapostesuivi'}</span>
                            </th>
                            <th>
                                <span class="title_box ">{l s='Status' mod='lapostesuivi'}</span>
                            </th>
                            <th>
                                <span class="title_box ">{l s='Details' mod='lapostesuivi'}</span>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{date('d/m/Y', strtotime($lps_tracking->date))|escape:'htmlall':'UTF-8'}</td>
                            <td>{$lps_tracking->status|escape:'htmlall':'UTF-8'}</td>
                            <td>{$lps_tracking->message|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                <a href="{$lps_tracking->link|escape:'htmlall':'UTF-8'}" class="btn btn-default" target="_blank">
                                    <i class="icon-external-link"></i>
                                    {l s='Track' mod='lapostesuivi'}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    {else}
        <div class="list-empty hidden-print">
            <div class="list-empty-msg">
                <i class="icon-warning-sign list-empty-icon"></i>
                {l s='No tracking.' mod='lapostesuivi'}
            </div>
        </div>
    {/if}
</div>

<script>
    $(document).ready(function () {
        var carrier_tab = $('#myTab');

        if (carrier_tab.length) {
            $('#lapostesuivi_li').appendTo(carrier_tab);
            $('#lapostesuivi').appendTo(carrier_tab.next());

            carrier_tab.find('#lapostesuivi_li > a').click(function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        }
    });
</script>