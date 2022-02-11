<link href="modules/servers/cpanel/css/client.css" rel="stylesheet">
<script src="modules/servers/cpanel/js/client.js"></script>
<script>
$('#Primary_Sidebar-Service_Details_Actions-Custom_Module_Button_Shutdown_Server').click(function(){
    return confirm("Are you sure you want to shutdown the server?");
})

$('#Primary_Sidebar-Service_Details_Actions-Custom_Module_Button_Reboot_Server').click(function(){
    return confirm("Are you sure you want to reboot the server?");
})

$('#Primary_Sidebar-Service_Details_Actions-Custom_Module_Button_Reset_Server').click(function(){
    return confirm("WARNING: Resetting a server may lead to data loss. Are you sure you want to proceed with resetting the server?");
})
</script>

<div class="row">
    <div class="col-md-6">

        <div class="panel panel-default card mb-3" id="cPanelPackagePanel">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">{lang key='libvirtPackageDomain'}</h3>
            </div>
            <div class="panel-body card-body text-center">

                <div class="cpanel-package-details">
                    <em>{$groupname}</em>
                    <h4 style="margin:0;">{$product}</h4>
                    {$domain}
                </div>

                <p>
                    <a href="chrome-extension://iodihamcpbpeioajjeobimgagajmlibd/html/nassh.html#{$username}@{$domain}:22" class="btn btn-default btn-sm" target="_blank">{$LANG.libvirtSsh}</a><a title="SSH works using Google's Chrome SSH client" href='https://chrome.google.com/webstore/detail/secure-shell/iodihamcpbpeioajjeobimgagajmlibd'>*</a>
                    {if $domainId}
                        <a href="clientarea.php?action=domaindetails&id={$domainId}" class="btn btn-success btn-sm"
                            target="_blank">{$LANG.managedomain}</a>
                    {/if}                                        
                </p>

            </div>
        </div>

        {if $availableAddonProducts}
            <div class="panel panel-default card mb-3" id="cPanelExtrasPurchasePanel">
                <div class="panel-heading card-header">
                    <h3 class="panel-title card-title m-0">{lang key='addonsExtras'}</h3>
                </div>
                <div class="panel-body card-body text-center mx-auto">

                    <form method="post" action="{$WEB_ROOT}/cart.php?a=add" class="form-inline">
                        <input type="hidden" name="serviceid" value="{$serviceid}" />
                        <select name="aid" class="form-control custom-select w-100 input-sm form-control-sm mr-2">
                            {foreach $availableAddonProducts as $addonId => $addonName}
                                <option value="{$addonId}">{$addonName}</option>
                            {/foreach}
                        </select>
                        <button type="submit" class="btn btn-default btn-sm btn-block mt-1">
                            <i class="fas fa-shopping-cart"></i>
                            {lang key='purchaseActivate'}
                        </button>
                    </form>

                </div>
            </div>
        {/if}

    </div>

    <div class="col-md-6">

        <div class="panel card panel-default mb-3" id="cPanelBillingOverviewPanel">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">{lang key='billingOverview'}</h3>
            </div>
            <div class="panel-body card-body">

                <div class="row">
                    <div class="col-md-12">
                        {if $firstpaymentamount neq $recurringamount}
                            <div class="row" id="firstPaymentAmount">
                                <div class="col-xs-6 col-6 text-right">
                                    {$LANG.firstpaymentamount}
                                </div>
                                <div class="col-xs-6 col-6">
                                    {$firstpaymentamount}
                                </div>
                            </div>
                        {/if}
                        {if $billingcycle != $LANG.orderpaymenttermonetime && $billingcycle != $LANG.orderfree}
                            <div class="row" id="recurringAmount">
                                <div class="col-xs-6 col-6 text-right">
                                    {$LANG.recurringamount}
                                </div>
                                <div class="col-xs-6 col-6">
                                    {$recurringamount}
                                </div>
                            </div>
                        {/if}
                        <div class="row" id="billingCycle">
                            <div class="col-xs-6 col-6 text-right">
                                {$LANG.orderbillingcycle}
                            </div>
                            <div class="col-xs-6 col-6">
                                {$billingcycle}
                            </div>
                        </div>
                        <div class="row" id="paymentMethod">
                            <div class="col-xs-6 col-6 text-right">
                                {$LANG.orderpaymentmethod}
                            </div>
                            <div class="col-xs-6 col-6">
                                {$paymentmethod}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row" id="registrationDate">
                            <div class="col-xs-6 col-6 col-xl-5 text-right">
                                {$LANG.clientareahostingregdate}
                            </div>
                            <div class="col-xs-6 col-6 col-xl-7">
                                {$regdate}
                            </div>
                        </div>
                        <div class="row" id="nextDueDate">
                            <div class="col-xs-6 col-6 col-xl-5 text-right">
                                {$LANG.clientareahostingnextduedate}
                            </div>
                            <div class="col-xs-6 col-6 col-xl-7">
                                {$nextduedate}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{foreach $hookOutput as $output}
    <div>
        {$output}
    </div>
{/foreach}

{if $systemStatus == 'Active'}
    {if count($wpInstances) || $allowWpClientInstall}
        <div class="panel card panel-default mb-3" id="cPanelWordPress" data-service-id="{$serviceId}"
            data-wp-domain="{$wpDomain}">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">WordPress®</h3>
            </div>
            <div class="panel-body card-body">
                <div class="row{if !$allowWpClientInstall} no-margin{/if}" id="wordpressInstanceRow"
                    {if !count($wpInstances)}style="display: none" {/if}>
                    <div class="col-md-4">
                        <select class="form-control" id="wordPressInstances">
                            {foreach $wpInstances as $wpInstance}
                                <option value="{$wpInstance.instanceUrl}">
                                    {$wpInstance.blogTitle}
                                    {if $wpInstance.path} ({$wpInstance.path}){/if}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-default btn-block" id="btnGoToWordPressHome">
                            <i class="fab fa-wordpress"></i>
                            {lang key='wptk.goToWebsite'}
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-default btn-block" id="btnGoToWordPressAdmin">
                            <i class="fas fa-users-cog"></i>
                            {lang key='wptk.goToAdmin'}
                        </button>
                    </div>
                </div>
                <div class="row" {if !$allowWpClientInstall}style="display: none" {/if}>
                    <div class="col-md-12">
                        <h5>{lang key='wptk.createNew'}</h5>
                        <p class="small" id="newWordPressFullUrlPreview">https://{$wpDomain}/</p>
                    </div>
                    <div class="col-md-12" id="wordPressInstallResultRow" style="display: none">
                        <div class="alert alert-success" style="display: none">
                            {lang key='wptk.installationSuccess'}
                        </div>
                        <div class="alert alert-danger" style="display: none">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="wpNewBlogTitle" placeholder="New Blog Title" />
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="wpNewPath" placeholder="Path (optional)" />
                    </div>
                    <div class="col-md-3">
                        <input type="password" class="form-control" id="wpAdminPass" placeholder="Admin Password" />
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-default btn-block" id="btnInstallWordpress">
                            <i class="far fa-fw fa-plus"></i>
                            {lang key='wptk.installWordPressShort'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {/if}

{else}

    <div class="alert alert-warning text-center" role="alert" id="cPanelSuspendReasonPanel">
        {if $suspendreason}
            <strong>{$suspendreason}</strong><br />
        {/if}
        {$LANG.cPanel.packageNotActive} {$status}.<br />
        {if $systemStatus eq "Pending"}
            {$LANG.cPanel.statusPendingNotice}
        {elseif $systemStatus eq "Suspended"}
            {$LANG.cPanel.statusSuspendedNotice}
        {/if}
    </div>

{/if}

{* Libvrt *}
<div class="panel card panel-default mb-3" id="cPanelAdditionalInfoPanel">
    <div class="panel-heading card-header">
        <h3 class="panel-title card-title m-0">{$LANG.libvirtDomain}</h3>
    </div>
    <div class="panel-body card-body">
        <div class="row">
            <div class="col-sm-5 text-right">
                <strong>Power State</strong>
            </div>
            <div class="col-sm-7 text-left">
                {$resources->powerState()}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5 text-right">
                <strong>vCPUs</strong>
            </div>
            <div class="col-sm-7 text-left">
                {$resources->vcpus()}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5 text-right">
                <strong>Memory</strong>
            </div>
            <div class="col-sm-7 text-left">
                {$resources->ram()} MiB
            </div>
        </div>
    </div>
</div>
{* End Libvirt *}


{if $configurableoptions}
    <div class="panel card panel-default mb-3" id="cPanelConfigurableOptionsPanel">
        <div class="panel-heading card-header">
            <h3 class="panel-title card-title m-0">{$LANG.orderconfigpackage}</h3>
        </div>
        <div class="panel-body card-body">
            {foreach from=$configurableoptions item=configoption}
                <div class="row">
                    <div class="col-md-5 col-xs-6 col-6 text-right">
                        <strong>{$configoption.optionname}</strong>
                    </div>
                    <div class="col-md-7 col-xs-6 col-6 text-left">
                        {if $configoption.optiontype eq 3}{if $configoption.selectedqty}{$LANG.yes}{else}{$LANG.no}{/if}{elseif $configoption.optiontype eq 4}{$configoption.selectedqty}
                    x {$configoption.selectedoption}{else}{$configoption.selectedoption}
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
</div>
{/if}

{if $metricStats}
    <div class="panel card panel-default mb-3" id="cPanelMetricStatsPanel">
        <div class="panel-heading card-header">
            <h3 class="panel-title card-title m-0">{$LANG.metrics.title}</h3>
        </div>
        <div class="panel-body card-body">
            {include file="$template/clientareaproductusagebilling.tpl"}
        </div>
    </div>
{/if}

{if $customfields}
    <div class="panel card panel-default mb-3" id="cPanelAdditionalInfoPanel">
        <div class="panel-heading card-header">
            <h3 class="panel-title card-title m-0">{$LANG.additionalInfo}</h3>
        </div>
        <div class="panel-body card-body">
            {foreach from=$customfields item=field}
                <div class="row">
                    <div class="col-md-5 col-xs-6 col-6 text-right">
                        <strong>{$field.name}</strong>
                    </div>
                    <div class="col-md-7 col-xs-6 col-6 text-left">
                        {if empty($field.value)}
                            {$LANG.blankCustomField}
                        {else}
                            {$field.value}
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}