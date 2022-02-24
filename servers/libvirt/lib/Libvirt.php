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
     * Get a list of Domain IDs
     */
    function virshList()
    {
        $command1 = "$this->ssh $this->login 'virsh -r list'";

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
    }

    /**
     * Get extended information for a VM then flatten it to an XML string
     */
    public function virshDumpxml($vmId)
    {
        $command = "$this->ssh $this->login 'virsh -r dumpxml $vmId'";

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
        $command = "$this->ssh $this->login 'virsh -r domstate $vmId'";

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

            // See https://developers.whmcs.com/provisioning-modules/module-logging/
            logModuleCall("libvirt", "fetchAndStoreDomains", $vmId, $vmId, $xml, "");

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

