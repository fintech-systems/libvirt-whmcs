<?php

use WHMCS\Database\Capsule;

class Resources
{    
    private $resource;

    public function __construct($serviceId) {
        $this->resource = Capsule::table('mod_libvirt_domains')
            ->select(
                'domain_id',
                'mod_libvirt_domains.name',
                'node_ip_address',                                
                'tblservers.username',
                'state',
                'ram',                
                'vcpus',                
            )
            ->where('whmcs_service_id', $serviceId)
            ->join('tblhosting', 'mod_libvirt_domains.whmcs_service_id', '=', 'tblhosting.id')
            ->join('tblservers', 'tblhosting.server', '=', 'tblservers.id')            
            ->first();
            // die(print_r($this->resource,1));
    }

    public function domainId() {
        return $this->resource->domain_id;
    }
    
    public function name() {
        return $this->resource->name;
    }

    public function nodeIpAddress() {
        return $this->resource->node_ip_address;
    }

    public function nodeUsername() {
        return $this->resource->username;
    }
                
    public function powerState() {
        return ucfirst($this->resource->power_state);
    }

    public function ram() {
        return $this->resource->ram;
    }

    public function vcpus()
    {        
        return $this->resource->vcpus;
    }

}
