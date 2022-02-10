<?php

use WHMCS\Database\Capsule;

class Resources
{    
    private $resource;

    public function __construct($serviceId) {
        $this->resource = Capsule::table('mod_libvirt_domains')
            ->where('whmcs_service_id', $serviceId)
            ->first();
    }
    
    public function getVcpus()
    {        
        return $this->resource->vcpus;
    }

    public function getRam() {
        return $this->resource->ram;
    }

    public function getPowerState() {
        return ucfirst($this->resource->power_state);
    }

}
