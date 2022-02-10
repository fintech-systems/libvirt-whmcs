<?php

use WHMCS\Database\Capsule;

use WHMCS\Service\Service; // Docs: https://classdocs.whmcs.com/7.0/WHMCS/Service/Service.html

/**
 * Helpers for interacting with WHMCS
 */
class Whmcs
{
    // /**
    //  * Get a service name based on a service ID
    //  */
    // public static function getServiceName($serviceId) {
    //     $service = Service::find($serviceId);

    //     $userid = $service->userid;

    //     $name = $service->name;
    //     // die(Service::all());
    //     // $service = Service::find($serviceId)->product->name;
    //     return Service::find($serviceId)->product->name;
    // }
    
    /**
     * Used to synchronise foreigh system with a local system e.g. scroll through
     * VMs at a remote host and for each VM ID look up if a client has a custom
     * field that matches said id.
     */
    public static function getServiceIdBasedOnCustomFieldValue($fieldName, $value)
    {
        $result = Capsule::table('tblcustomfields')
            ->join('tblcustomfieldsvalues', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
            ->whereType('product')
            ->whereFieldname($fieldName) // E.g. 'domainid|Domain ID'            
            ->whereValue($value)
            ->get();
        
        if (count($result) > 1) {
            throw new Exception("Duplicate fieldName $fieldName and value $value");
        }

        if (isset($result[0]->relid)) {            
            return $result[0]->relid;
        }

        return null;
    }

    // /**
    //  * Retrieve a specific field value for a service, for example:
    //  * 
    //  * A service Nextcloud 100 GB tied to a client has service ID 17.
    //  * When opening a page that displays service ID 17, check that
    //  * the domainid is 35 and links this.
    //  */
    // public function getCustomProductFieldValue($fieldName, $serviceId)
    // {
    //     $result = Capsule::table('tblcustomfields')
    //         ->join('tblcustomfieldsvalues', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
    //         ->whereType('product')
    //         ->whereFieldname($fieldName) // E.g. 'domainid|Domain ID'
    //         ->whereRelid($serviceId)
    //         ->get();

    //     die($result->value);
    // }

    // public function getCustomClientField()
    // {
    // }
}
