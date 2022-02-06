<?php

/**
 * 
 * https://github.com/librenms/librenms/blob/master/includes/discovery/libvirt-vminfo.inc.php
 * 
 * virsh -rc qemu://root@172.168.1.41 list
 * virsh -r dumpxml 35
 */
class Libvirt
{
    private $server;

    public function __construct($server)
    {
        $this->server = $server;
    }

    /**
     * Get a list of VM IDs
     */
    function virshList()
    {
        $command1 = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes' $this->server 'virsh -r list'";

        exec($command1, $output);

        foreach ($output as $vm) {
            [$vm_id,] = explode(' ', trim($vm), 2);

            // Ignore first two lines of output
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
    public function powerState($vmId) {
        $vm_state = $this->virshDomstate($vmId);

        return PowerState::STATES[strtolower($vm_state[0])] ?? PowerState::UNKNOWN;
    }

    /**
     * Used by the powerState to see to each VMs `domstate`
     * 
     * See https://libvirt.org/manpages/virsh.html#domstate
     */
    private function virshDomstate($vmId) {
        $command = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes' $this->server 'virsh -r domstate $vmId'";

        exec($command, $output);
        
        return $output;
    }

    /**
     * Get extended information for a VM then flatten it to an XML string
     */
    function virshDumpxml($vmId) {
        $command = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes' $this->server 'virsh -r dumpxml $vmId'";
    
        exec($command, $vm_info_array);
    
        $vm_info_xml = '';
    
        foreach ($vm_info_array as $line) {
            $vm_info_xml .= $line;
        }
    
        return simplexml_load_string('<?xml version="1.0"?> ' . $vm_info_xml);
    }
}

///////////// Start of Application ////////////////

require_once('PowerState.php');
require_once('ConvertToMib.php');

$api = new Libvirt('root@172.168.1.41');

$vmList = $api->virshList();

$vm_info_array = [];
$vm_info_xml = '';

foreach ($vmList as $vmId) {
            
    $xml = $api->virshDumpxml($vmId);

    $vmwVmDisplayName = $xml->name;

    echo $vmwVmDisplayName . "\n";

    // libvirt does not supply this
    $vmwVmGuestOS = '';
    
    // $vm_state = $api->virshDomstate($vmId);
    $powerState = $api->powerState($vmId);
    
    echo $powerState . "\n";
        
    $vmwVmCpus = $xml->vcpu['current'];
    if (!isset($vmwVmCpus)) {
        $vmwVmCpus = $xml->vcpu;
    }
    echo $vmwVmCpus . "\n";

    $vmwVmMemSize = $xml->memory;

    echo $vmwVmMemSize . "\n";

    echo ConvertToMib($vmwVmMemSize) . "\n";
}

