<?php

require_once('PowerState.php');

require_once('ConvertToMib.php');

require_once('Whmcs.php');

require_once('Resources.php');

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

    private $ssh = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes'";
    // ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes'
    // ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes'
    // ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes'
    // ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes'";

    public function __construct($username, $ipaddress)
    {
        $this->ip_address = $ipaddress;

        $this->login = $username . '@' . $ipaddress;
    }

    /**
     * https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/virtualization_deployment_and_administration_guide/sect-starting_suspending_resuming_saving_and_restoring_a_guest_virtual_machine-starting_a_defined_domain#sect-Shutting_down_rebooting_and_force_shutdown_of_a_guest_virtual_machine-Rebooting_a_guest_virtual_machine     
     */
    public function reboot($name, $mode = 'initctl') {
        $command = "$this->ssh $this->login 'virsh reboot $name --mode $mode'";
        
        exec ($command, $out, $ret);

        if ($ret != 255) {
            return true;
        }

        return false;
    }

    /**
     * https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/virtualization_deployment_and_administration_guide/sect-managing_guest_virtual_machines_with_virsh-shutting_down_rebooting_and_force_shutdown_of_a_guest_virtual_machine
     */
    public function reset($name) {
        $command = "$this->ssh $this->login 'virsh reset $name'";
            
        exec ($command, $out, $ret);

        if ($ret != 255) {
            return true;
        }

        return false;
    }

    /**
     * https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/virtualization_deployment_and_administration_guide/sect-starting_suspending_resuming_saving_and_restoring_a_guest_virtual_machine-starting_a_defined_domain#sect-start-vm
     */
    public function start($name) {
        $command = "$this->ssh $this->login 'virsh start $name'";
        
        exec ($command, $out, $ret);

        if ($ret != 255) {
            return true;
        }

        return false;
    }

    /**
     * https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/virtualization_deployment_and_administration_guide/sect-managing_guest_virtual_machines_with_virsh-shutting_down_rebooting_and_force_shutdown_of_a_guest_virtual_machine
     */
    public function shutdown($name, $mode = 'acpi') {
        $command = "$this->ssh $this->login 'virsh shutdown $name --mode $mode'";
        
        exec ($command, $out, $ret);

        if ($ret != 255) {
            return true;
        }

        return false;
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
     * This outputs table format IDs, names, and states and returns array with names/state/id
     * 
     * IDs might be empty in lieu of --all
     * 
     * This command should precede virsh list --all --name --uuid which then merges
     * the information with UUIDs
     */
    function virshListIdNameState() {
        $command = "$this->ssh $this->login 'virsh list --all'";

        exec($command, $output);
        
        $numberOfDomains = count($output) - 1;
        
        // Start at index item 2 to skip column header and dashed break line
        for ($i = 2; $i < $numberOfDomains; $i++) { 
            $result = preg_split('/ +/', $output[$i]);
                        
            $domains[$result[2]]['state'] = trim($result[3] . ' ' . $result[4]);

            $domains[$result[2]]['id'] = $result[1];
        }
        
        return $domains;
    }

    function virshListUuidName($domainsList) {
        $command = "$this->ssh $this->login 'virsh list --all --uuid --name'";

        exec($command, $output);
                                        
        foreach ($output as $domain) {             

            [$uuid, $name] = explode(' ', $domain);
                                    
            // The last line is empty so avoid capturing it
            if ($domain) {
                $domainsList[$name]['uuid'] = trim(preg_replace('/\t+/', '', $uuid)); // Trim tab
                $domainsList[$name]['name'] = $name;
            }
            
        }
        
        return $domainsList;
    }
    
    /**
     * Get the power on state of each VM
     */
    public function powerState($vmId)
    {
        $vm_state = $this->virshDomstate($vmId);

        return $vm_state[0];        
    }

    /**
     * Get extended information for a VM then flatten it to an XML string
     * 
     * $id can be UUID or vmID or name
     */
    public function virshDumpxml($id)
    {
        $command = "$this->ssh $this->login 'virsh dumpxml $id'";

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
    private function virshDomstate($id)
    {
        $command = "$this->ssh $this->login 'virsh -r domstate $id'";

        exec($command, $output);

        return $output;
    }

    /**
     * Fetch all VMs and store in mod_libvirt_domains
     * 
     * This is a multi-step process:
     * 
     *  1. Fetch id, name and state
     *  2. Fetch UUID and combine with name
     *  3. Store in database based on UUID
     * 
     *  Return the number of domain updated for totals storage
     */
    public function fetchAndStoreDomains()
    {
        $domainsList1 = $this->virshListIdNameState();

        $domainsList2 = $this->virshListUuidName($domainsList1);
                
        foreach ($domainsList2 as $domain) {
            // die(print_r($domain,1));

            $xml = $this->virshDumpxml($domain['uuid']);

            $vmwVmCpus = $xml->vcpu['current'];
            if (!isset($vmwVmCpus)) {
                $vmwVmCpus = $xml->vcpu;
            }

            // See https://developers.whmcs.com/provisioning-modules/module-logging/
            logModuleCall("libvirt", "fetchAndStoreDomains", $domain['uuid'], $domain['id'], $xml, "");

            Capsule::table('mod_libvirt_domains')->updateOrInsert(
                [
                    'uuid' => $domain['uuid'],
                ],
                [
                    'domain_id' => $domain['id'],
                    'name' => $xml->name,
                    'vcpus' => $vmwVmCpus,
                    'ram' => ConvertToMib($xml->memory),
                    'state' => $this->powerState($domain['uuid']),
                    'node_ip_address' => $this->ip_address,
                    'whmcs_service_id' => Whmcs::getServiceIdBasedOnCustomFieldValue('uuid|UUID', $domain['uuid']),
                ]
            );
        }

        return count($domainsList2);
    }

    /**
     * Fetch all VMs and store in mod_libvirt_domains
     */
    public function updateResourcesTotals($node)
    {
        $cpusInUse = Capsule::table('mod_libvirt_domains')
            ->where('node_ip_address', $node->ipaddress)
            ->where('state', 'running')
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

