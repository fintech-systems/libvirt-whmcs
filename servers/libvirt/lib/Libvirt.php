<?php

require_once('PowerState.php');

require_once('ConvertToMib.php');

require_once('Whmcs.php');

// https://developers.whmcs.com/advanced/db-interaction/
use WHMCS\Database\Capsule;

/**
 * Libvirt
 * 
 * Inspired by:
 * https://github.com/librenms/librenms/blob/master/includes/discovery/libvirt-vminfo.inc.php
 * 
 * virsh list --all
 * virsh -rc qemu://root@a.b.c.d list
 * virsh -r dumpxml 35
 */
class Libvirt
{
    private $ip_address;

    private $login;

    public function __construct($username, $ipaddress)
    {
        $this->ip_address = $ipaddress;
        $this->login = $username . '@' . $ipaddress;
    }

    /**
     * Test basic connectivity using SSH
     * 
     * This will work if the key has been added
     */
    public function testConnection()
    {
        exec('ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" ' . $this->login . ' echo -e', $out, $ret);

        if ($ret != 255) {
            return true;
        }

        return false;
    }

    /**
     * Get a list of Domain IDs
     */
    function virshList()
    {
        $command1 = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes' $this->login 'virsh -r list'";

        exec($command1, $output);

        foreach ($output as $vm) {
            [$vm_id,] = explode(' ', trim($vm), 2);

            // Ignore the first two lines of the array output
            if (!is_numeric($vm_id)) {
                continue;
            }

            $vmIds[] = $vm_id;
        }

        return $vmIds;
    }

    /**
     * Get the power on state of each VM
     */
    public function powerState($vmId)
    {
        $vm_state = $this->virshDomstate($vmId);

        return $vm_state[0];

        // return PowerState::STATES[strtolower($vm_state[0])] ?? PowerState::UNKNOWN;
    }

    /**
     * Get extended information for a VM then flatten it to an XML string
     */
    public function virshDumpxml($vmId)
    {
        $command = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes' $this->login 'virsh -r dumpxml $vmId'";

        exec($command, $vm_info_array);

        $vm_info_xml = '';

        foreach ($vm_info_array as $line) {
            $vm_info_xml .= $line;
        }

        return simplexml_load_string('<?xml version="1.0"?> ' . $vm_info_xml);
    }

    /**
     * Used by the powerState to see to each VMs `domstate`
     * 
     * See https://libvirt.org/manpages/virsh.html#domstate
     */
    private function virshDomstate($vmId)
    {
        $command = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes' $this->login 'virsh -r domstate $vmId'";

        exec($command, $output);

        return $output;
    }

    /**
     * Fetch all VMs and store in mod_libvirt_domains
     */
    public function fetchAndStoreDomains()
    {
        $vmList = $this->virshList();

        foreach ($vmList as $vmId) {
            $xml = $this->virshDumpxml($vmId);

            $vmwVmCpus = $xml->vcpu['current'];
            if (!isset($vmwVmCpus)) {
                $vmwVmCpus = $xml->vcpu;
            }

            Capsule::table('mod_libvirt_domains')->updateOrInsert(
                [
                    'domain_id' => $vmId,
                ],
                [
                    'name' => $xml->name,
                    'vcpus' => $vmwVmCpus,
                    'ram' => ConvertToMib($xml->memory),
                    'power_state' => $this->powerState($vmId),
                    'node_ip_address' => $this->ip_address,
                    'whmcs_service_id' => Whmcs::getServiceIdBasedOnCustomFieldValue('domainid|Domain ID', $vmId),
                ]
            );
        }

        return count($vmList);
    }

    /**
     * Fetch all VMs and store in mod_libvirt_domains
     */
    public function updateResourcesTotals($node)
    {
        $cpusInUse = Capsule::table('mod_libvirt_domains')
            ->where('node_ip_address', $node->ipaddress)
            ->groupBy('node_ip_address')
            ->sum('vcpus');
            
        $ramInUse = Capsule::table('mod_libvirt_domains')
            ->where('node_ip_address', $node->ipaddress)
            ->groupBy('node_ip_address')
            ->sum('ram');

        Capsule::table('mod_libvirt_nodes')->updateOrInsert(
            [
                'ip_address' => $node->ipaddress,
            ],
            [
                'vcpus_in_use' => $cpusInUse,
                'ram_in_use' => $ramInUse,
            ]
        );
    }
}

///////////// Start of Application ////////////////



// $api = new Libvirt('root@172.168.1.41');

// $vmList = $api->virshList();

// $vm_info_array = [];
// $vm_info_xml = '';

// foreach ($vmList as $vmId) {
            
//     $xml = $api->virshDumpxml($vmId);

//     $vmwVmDisplayName = $xml->name;

//     echo $vmwVmDisplayName . "\n";

//     // libvirt does not supply this
//     $vmwVmGuestOS = '';
    
//     // $vm_state = $api->virshDomstate($vmId);
//     $powerState = $api->powerState($vmId);
    
//     echo $powerState . "\n";
        
//     $vmwVmCpus = $xml->vcpu['current'];
//     if (!isset($vmwVmCpus)) {
//         $vmwVmCpus = $xml->vcpu;
//     }
//     echo $vmwVmCpus . "\n";

//     $vmwVmMemSize = $xml->memory;

//     echo $vmwVmMemSize . "\n";

//     echo ConvertToMib($vmwVmMemSize) . "\n";
// }
