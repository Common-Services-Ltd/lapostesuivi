{**
* @author    debuss-a <alexandre@common-services.com>
* @copyright Copyright (c) 2018 Common-Services
* @license   CC BY-SA 4.0
*}

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Documentation & Support' mod='lapostesuivi'}</h3>

	<div class="col-lg-9 col-sm-12 col-xs-12">
		<p>
			&raquo; {l s='You can get a PDF documentation to configure this module' mod='lapostesuivi'} :
		</p>
		<ul>
			<li>
				<a href="{$module_dir|escape:'htmlall':'UTF-8'}documentation/readme_fr.pdf" target="_blank">DOCUMENTATION</a>
				/ <a href="{$module_dir|escape:'htmlall':'UTF-8'}documentation/readme_fr.html" target="_blank">README</a>
				/ <a href="{$module_dir|escape:'htmlall':'UTF-8'}documentation/license.html" target="_blank">LICENSE</a>
			</li>
		</ul>
		<br>

		<p>
			&raquo; {l s='Bug report on GitHub only' mod='lapostesuivi'} : <a href="https://github.com/Common-Services/lapostesuivi/issues" target="_blank">https://github.com/Common-Services/lapostesuivi/issues</a><br>
			&nbsp;&nbsp;&nbsp;{l s='For any bug report, please follow the following process' mod='lapostesuivi'} : <a href="{$module_dir|escape:'htmlall':'UTF-8'}documentation/contributing.html" target="_blank">CONTRIBUTING</a>
		</p>
		<br>

		<p>
			&raquo; {l s='This is a free module powered by' mod='lapostesuivi'} <a href="https://blog.common-services.com" target="_blank">Common-Services</a>
			{l s='under the licence' mod='lapostesuivi'} <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">CC BY-SA 4.0</a>.<br>
			&nbsp;&nbsp;&nbsp;{l s='You will appreciate our other modules' mod='lapostesuivi'} : <a href="http://addons.prestashop.com/fr/58_common-services" target="_blank">http://addons.prestashop.com/fr/58_common-services</a>
		</p>
	</div>

	<div class="col-lg-3 visible-lg">
		<img src="{$module_dir|escape:'htmlall':'UTF-8'}logo.png" class="img-responsive pull-right " width="110px">
		<div class="clearfix">&nbsp;</div><br>
		<a href="http://addons.prestashop.com/fr/58_common-services" target="_blank">
			<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/common-services-banner.png" class="img-responsive pull-right " width="250px">
		</a>
	</div>

	<div class="clearfix"></div>

    {if !function_exists('curl_init')}
		<hr>
		<div class="alert alert-danger">
            {l s='The cURL extension must be available on your server, right now the module will not work.' mod='lapostesuivi'}
		</div>
    {/if}
</div>

<ul class="nav nav-pills" id="conf-nav">
	<li id="nav-authentication" role="presentation" class="active"><a href="#" rel="div-authentication"><i class="icon icon-unlock-alt"></i> {l s='Authentication' mod='lapostesuivi'}</a></li>
	<li id="nav-carriers" role="presentation"><a href="#" rel="div-carriers"><i class="icon icon-truck"></i> {l s='Carriers' mod='lapostesuivi'}</a></li>
	<li id="nav-status" role="presentation"><a href="#"  rel="div-status"><i class="icon icon-time"></i> {l s='Status' mod='lapostesuivi'}</a></li>
	<li id="nav-settings" role="presentation"><a href="#" rel="div-settings"><i class="icon icon-cog"></i> {l s='Settings' mod='lapostesuivi'}</a></li>
</ul>
