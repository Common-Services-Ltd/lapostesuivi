{**
* @author    debuss-a <alexandre@common-services.com>
* @copyright Copyright (c) 2018 Common-Services
* @license   CC BY-SA 4.0
*}

<div id="status_mapping">
    {foreach $status_list as $status}
        <div class="form-group status-margin-fix">
            <label class="control-label col-lg-3" for="availableCarriers">
                {l s='Status' mod='lapostesuivi'} <span style="color: {$status.color|escape:'htmlall':'UTF-8'};">[ {$status.name|escape:'htmlall':'UTF-8'} ]</span>
            </label>
            <div class="col-lg-9">
                <div class="form-control-static row">
                    <div class="col-xs-6">
                        <p>{l s='Available status' mod='lapostesuivi'}</p>
                        <select class="availableStatus" multiple="multiple">
                            {foreach $laposte_status_list as $lp_status_id => $lp_status_name}
                                <option value="{$lp_status_id|escape:'htmlall':'UTF-8'}">{$lp_status_name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                        <a class="btn btn-default btn-block addStatus">{l s='Add' mod='lapostesuivi'} <i class="icon-arrow-right"></i></a>
                    </div>
                    <div class="col-xs-6">
                        <p>{l s='Selected status' mod='lapostesuivi'}</p>
                        <select class="selectedStatus" name="selected_status[{$status.id_order_state|intval}][]" multiple="multiple">
                            {if is_array($selected_status_list) && array_key_exists($status.id_order_state, $selected_status_list)}
                                {foreach $selected_status_list[$status.id_order_state] as $key => $state}
                                    <option value="{$key|escape:'htmlall':'UTF-8'}">{$state|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <a class="btn btn-default btn-block removeStatus"><i class="icon-arrow-left"></i> {l s='Remove' mod='lapostesuivi'}</a>
                    </div>
                </div>

                <p class="text-primary help-block">
                    {l s='Select the La Poste delivery state that will pass the order to the state "%s".' sprintf=[$status.name] mod='lapostesuivi'}
                </p>
            </div>
        </div>

        <div class="clearfix">&nbsp;</div>
    {/foreach}
</div>