<?php

namespace WHMCS\Module\Addon\Libvirt\Admin;

require_once __DIR__ . '/../../../../servers/libvirt/lib/Libvirt.php';

// Model documentation for Server (search Server): https://classdocs.whmcs.com/8.4/WHMCS/Service/Service.html

use Libvirt;

use \WHMCS\Product\Server;

use WHMCS\Database\Capsule;

use WHMCS\Service\Service; // Docs: https://classdocs.whmcs.com/7.0/WHMCS/Service/Service.html

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
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables

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
                . $node->total_cpus . "</td><td>"
                . $node->vcpus_in_use . "</td><td>"
                . $node->total_ram . "</td><td>"
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
        
<p>
    <a href="{$modulelink}&action=refreshDomains" class="btn btn-success">
        <i class="fa fa-check" aria-hidden="true"></i>
        Refresh Nodes & Domains
    </a>    
</p>

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
