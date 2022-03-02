<?php

use WHMCS\Database\Capsule;

use WHMCS\Service\Service; // Docs: https://classdocs.whmcs.com/7.0/WHMCS/Service/Service.html

/**
 * Helpers for interacting with WHMCS
 */
class Whmcs
{    
    /**
     * Used to synchronise foreigh system with a local system e.g. scroll through
     * VMs at a remote host and for each VM ID look up if a client has a custom
     * field that matches said id.
     */
    public static function getServiceIdBasedOnCustomFieldValue($fieldName, $value)
    {
        $result = Capsule::table('tblcustomfields')
            ->select(
                'type',
                'fieldname',
                'value',
                'tblcustomfieldsvalues.relid as service_id'                
            )
            ->join('tblcustomfieldsvalues', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
            ->whereType('product')
            ->whereFieldname($fieldName) // E.g. 'domainid|Domain ID'            
            ->whereValue($value)
            ->get();
        
        if (count($result) > 1) {
            throw new Exception("Duplicate fieldName $fieldName and value $value");
        }
        
        if (isset($result[0]->service_id)) {               
            return $result[0]->service_id;
        }

        return null;
    }

}
