<?php

namespace WHMCS\Module\Addon\Libvirt\Admin;

require_once __DIR__ . '/../../../../servers/libvirt/lib/Libvirt.php';

// Model documentation for Server (search Server): https://classdocs.whmcs.com/8.4/WHMCS/Service/Service.html

use Libvirt;
use \WHMCS\Product\Server;

use WHMCS\Database\Capsule;

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
        $hosts = Server::whereType('libvirt')
            ->select('id', 'name', 'ipaddress', 'username', 'disabled')
            ->get();

        $hostsTableBody = "";
        foreach ($hosts as $server) {
            $hostsTableBody .= "<tr><td>"
                . $server->name . "</td><td>"
                . $server->ipaddress . "</td><td>"
                . "" . "</td><td>"
                . "" . "</td><td>"
                . "</tr>";
        }

        $vmsTableBody = "";
        foreach (Capsule::table('mod_libvirt')
                ->orderBy('host_ip_address')
                ->get() as $vm) {
            $vmsTableBody .= "<tr><td>"
                . $vm->vm_id . "</td><td>"
                . $vm->name . "</td><td>"
                . $vm->vcpus . "</td><td>"
                . $vm->memory . "</td><td>"
                . $vm->power_state . "</td><td>"
                . $vm->host_ip_address . "</td><td>"
                . "" . "</td><td>"
                . "</tr>";
        }
        //$vms = 

        // Construct VMs table body


        return <<<EOF

        <h2>Hosts</h2>

        <div class="tablebg">
        <table id="sortabletbl1" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr>
            <th>Server</th>
            <th>IP Address</th>            
            <th>Total CPUs</th>
            <th>Total RAM</th>                        
        </tr>        
        {$hostsTableBody}
        </table>
        </div>

        <h2>VMs</h2>

        <div class="tablebg">
        <table id="sortabletbl1" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr>
            <th>VM ID</th>
            <th>Name</th>            
            <th>vCPUs</th>
            <th>Memory</th>
            <th>Power State</th>
            <th>Host</th>
            <th>WHMCS Service</th>
        </tr>        
        {$vmsTableBody}
        </table>
        </div>
        
<p>
    <a href="{$modulelink}&action=fetchVms" class="btn btn-success">
        <i class="fa fa-check" aria-hidden="true"></i>
        Refresh VMs
    </a>    
</p>

EOF;
    }

    /**
     * Show action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function fetchVms($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables

        // Fetch all enabled libvirt hosts
        $hosts = Server::whereType('libvirt')
            ->select('id', 'name', 'ipaddress', 'username', 'disabled')
            ->whereDisabled(false)
            ->get();

        $result = "";
        foreach ($hosts as $server) {
            $libvirt = new Libvirt($server->username, $server->ipaddress);

            $result .= "Fetched " . $libvirt->fetchAndStoreVms() . " VMs from server $server->ipaddress<br>";
        }

        return <<<EOF

<h2>Fetch VMs</h2>

<p>$result</p>

<p>
    <a href="{$modulelink}" class="btn btn-info">
        <i class="fa fa-arrow-left"></i>
        Back to Libvirt hosts and VMs
    </a>
</p>

EOF;
    }
}
