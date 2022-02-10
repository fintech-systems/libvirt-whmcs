<?php

///////////// Start of Application ////////////////

$api = new Libvirt('root@172.168.1.42');

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