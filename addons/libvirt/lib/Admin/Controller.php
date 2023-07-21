<?php

namespace WHMCS\Module\Addon\Libvirt\Admin;

require_once __DIR__ . '/../../../../servers/libvirt/lib/Libvirt.php';

use Libvirt;
use Whmcs;
use \WHMCS\Product\Server;

use WHMCS\Database\Capsule;

use WHMCS\Service\Service;

/**
 * Libvirt Admin Area Controller
 */
class Controller
{

    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function index($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule

        // $tab = $_GET['tab'];
        // if ($tab == 'nodes') $nodesTabSelected = "active";
        // if ($tab == 'domains') $domainsTabSelected = "active";
        // if ($tab == 'settings') $settingsTabSelected = "active";

        $version = $vars['version']; // eg. 1.0

        $LANG = $vars['_lang']; // an array of the currently loaded language variables

        $scriptPreConnection = Whmcs::setting('ScriptPreConnection');

        // Get a list of libvirt servers
        $nodes = Server::whereType('libvirt')
            ->join('mod_libvirt_nodes', 'tblservers.ipaddress', '=', 'mod_libvirt_nodes.ip_address')
            ->select(
                'name',
                'ipaddress',
                'username',
                'disabled',
                'cpu_total',
                'ram_total',
                'disk_total',
                'vcpus_in_use',
                'ram_in_use',
                'disk_in_use',
            )
            ->get();

        $nodesTableBody = "";

        // Construct the body for the list of Nodes (Servers)
        foreach ($nodes as $node) {
            $nodesTableBody .= "<tr><td>"
                . $node->name . "</td><td>"
                . $node->ipaddress . "</td><td>"
                . $node->cpu_total . "</td><td>"
                . $node->vcpus_in_use . "</td><td>"
                . $node->ram_total . "</td><td>"
                . $node->ram_in_use . "</td><td>"
                . $node->disabled . "</td>"
                . "</tr>";
        }

        $domainsTableBody = "";

        // Construct the body for the list of Domains (VMs)
        // These  are reloaded with the Refresh Nodes and Domains command
        // See 
        foreach (Capsule::table('mod_libvirt_domains')
            ->orderBy('node_ip_address')
            ->get() as $domain) {

            $service = Service::find($domain->whmcs_service_id);

            $domainsTableBody .= "<tr><td>"
                . $domain->uuid . "</td><td>"
                . $domain->domain_id . "</td><td>"
                . $domain->name . "</td><td>"
                . "<a href='clientsservices.php?userid=$service->userid&productselect=$domain->whmcs_service_id'>"
                . $service->product->name . "</a></td><td>"
                . $domain->vcpus . "</td><td>"
                . $domain->ram . "</td><td>"
                . $service->amount . "</a></td><td>"
                . $domain->node_ip_address . "</td><td>"
                . ucfirst($domain->state) . "</td>"
                . "</tr>";
        }

        return <<<EOF

        <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>

        <script>

        $(document).ready(function () {
            $("#settingsForm").submit(function (event) {
                var formData = {                
                scriptPreConnection: $("#scriptPreConnection").val(),
            };
          
            $.ajax({
                type: "POST",
                url: "../modules/addons/libvirt/lib/saveSettings.php",
                data: formData,
                dataType: "json",
                encode: true,                
            }).done(function (data) {
                console.log(data);
            });
          
            event.preventDefault();

            });
        });
        
        $(function() {
            $( "#tabs" ).tabs({                
                activate: function (event, ui) {
                    $('#tabs li').removeClass('active');                    
                    $(ui.newTab).addClass('active');                               
                    
                    var scrollTop = $(window).scrollTop(); // save current scroll position                
                    window.location.hash = ui.newPanel.attr('id'); // add hash to url                    
                    $(window).scrollTop(scrollTop); // keep scroll at current position                    
                }                
            });    
        });
        
        </script>
        
        <!--
        <div id="clienttabs">
                <ul class="nav nav-tabs admin-tabs">                
                <li class="tab{$nodesTabSelected}"><a href="addonmodules.php?module=libvirt&tab=nodes">Nodes</a></li>
                <li class="tab{$domainsTabSelected}"><a href="addonmodules.php?module=libvirt&tab=domains">Domains</a></li>
                <li class="tab{$settingsTabSelected}"><a href="addonmodules.php?module=libvirt&tab=settings">Settings</a></li>                    
            </ul>
        </div>
        -->

        <div id="tabs">
        
            <!-- <ul class="nav nav-tabs admin-tabs"> -->
            <ul class="nav nav-tabs client-tabs" role="tablist">
                <li class="tab"><a href="#nodes">Nodes</a></li>
                <li class="tab"><a href="#domains">Domains</a></li>
                <li class="tab"><a href="#settings">Settings</a></li>
                <li class="tab"><a href="#test">Test</a></li>
            </ul>

            <div id="nodes">
                <h2>Nodes</h2>
                <div class="tablebg">
                    <table id="sortabletbl1" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
                        <tr>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Total CPUs</th>
                            <th>vCPUs in Use</th>
                            <th>Total RAM</th>
                            <th>RAM in Use</th>
                            <th>Disabled</th>
                        </tr>        
                    {$nodesTableBody}                
                    </table>
                </div>
                <p>
                    <a href="{$modulelink}&action=refreshDomains" class="btn btn-success">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        Refresh Nodes & Domains
                    </a>    
                </p>
            </div>

            <div id="domains">            
                <h2>Domains</h2>
                <div class="tablebg">
                    <table id="sortabletbl1" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
                        <tr>
                            <th>UUID</th>    
                            <th>ID</th>
                            <th>Name</th>            
                            <th>WHMCS Service</th>            
                            <th>vCPUs</th>
                            <th>RAM</th>            
                            <th>Amount</th>
                            <th>Node</th>
                            <th>State</th>
                            
                        </tr>        
                    {$domainsTableBody}
                    </table>
                </div>
            </div>

            <div id="settings">            
                <form method='post' id='settingsForm'>
                    <h2>Settings</h2>                    
                    <table class="form">
                        <tr class=''>
                        <td class='fieldlabel'>
                            <label for='script_pre_connection'>Script Pre-Connection</label>
                        </td>
                        <td class='fieldarea'>
                            <input type='text' id='scriptPreConnection' name='scriptPreConnection' value='{$scriptPreConnection}' />
                            <p class='description'>Type the name of the script you want to launch before connecting to Nodes.</p></td></tr>
                    </table>

                    <button type="submit" class="btn btn-success">
                        Save Settings
                    </button>
                    
                </form>            
            </div>

            <div id="test">
                <h2>This is a test</h2>
                <p>
                    Hello World!
                </p>
            </div>

        </div>
        


EOF;
    }

    /**
     * Fetch the Domains and also update the CPU and RAM in use counts
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function refreshDomains($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables

        // Fetch all enabled libvirt nodes
        $nodes = Server::whereType('libvirt')
            ->select('id', 'name', 'ipaddress', 'username', 'disabled')
            ->whereDisabled(false)
            ->get();

        $result = "";
        foreach ($nodes as $node) {
            $libvirt = new Libvirt($node->username, $node->ipaddress);

            $result .= "Fetched " . $libvirt->fetchAndStoreDomains() . " Domains from Node $node->ipaddress<br>";

            $libvirt->updateResourcesTotals($node);
        }

        return <<<EOF

<h2>Fetch VMs</h2>

<p>$result</p>

<p>
    <a href="{$modulelink}" class="btn btn-info">
        <i class="fa fa-arrow-left"></i>
        Back to Libvirt Nodes and VMs
    </a>
</p>

EOF;
    }
}
