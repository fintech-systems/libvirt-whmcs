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
    private $ipAddress;

    private $login;

    private $ssh = "ssh -o 'StrictHostKeyChecking no' -o 'PreferredAuthentications publickey' -o 'IdentitiesOnly yes'";

    public function __construct($username, $ipAddress)
    {
        $this->ipAddress = $ipAddress;

        if (!$username) {
            throw new Exception("Constructing libvirt, username is empty");
        }

        if (!$ipAddress) {
            throw new Exception("Constructing libvirt, ipAddress is empty");
        }

        $this->login = $username . '@' . $ipAddress;
    }

    /**
     * Gracefully reboot a domain
     */
    public function reboot($name, $mode = 'initctl'): array
    {
        return $this->connect(
            "virsh reboot $name --mode $mode"
        );
    }

    /**
     * Force reset a domain
     */
    public function reset($name): array
    {
        return $this->connect(
            "virsh reset $name"
        );
    }

    /**
     * Resume a domain. This typically happens after suspending a domain.
     */
    public function resume($name): array
    {
        return $this->connect(
            "virsh resume $name"
        );
    }

    /**
     * Start a domain
     */
    public function start($name): array
    {
        return $this->connect(
            "virsh start $name"
        );
    }

    /**
     * Graceful shutdown a domain
     */
    public function shutdown($name, $mode = 'acpi'): array
    {
        return $this->connect(
            "virsh shutdown $name --mode $mode"
        );
    }

    /**
     * Suspend a domain. Used in conjunction with resume a domain.
     */
    public function suspend($name): array
    {
        return $this->connect(
            "virsh suspend $name"
        );
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
    function virshListIdNameState(): array
    {
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

    function virshListUuidName($domainsList): array
    {
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
     * Get the power state of each VM
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
     *  Return the number of domains updated for totals storage
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
            // module, action, input, response, result, replace
            logModuleCall("libvirt", "fetchAndStoreDomains", $domain['uuid'], $domain['id'], $xml, "");

            Capsule::table('mod_libvirt_domains')->updateOrInsert(
                [
                    'uuid' => $domain['uuid'],
                ],
                [
                    'domain_id'        => $domain['id'],
                    'name'             => $xml->name,
                    'vcpus'            => $vmwVmCpus,
                    'ram'              => ConvertToMib($xml->memory),
                    'state'            => $this->powerState($domain['uuid']),
                    'node_ip_address'  => $this->ipAddress,
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
                'ram_in_use'   => $ramInUse,
            ]
        );
    }

    /**
     * SSH to a server and execute a command
     *
     * @param string $script
     * @return string[]
     */
    protected function connect(string $script): array
    {
        $command = "$this->ssh $this->login '$script'";

        exec($command, $out, $ret);

        if ($ret != 255) {
            return
                ["result" => "success"];
        }

        return
            ["result"  => "failed",
             "command" => $command,
             "output"  => $out];

    }
}
