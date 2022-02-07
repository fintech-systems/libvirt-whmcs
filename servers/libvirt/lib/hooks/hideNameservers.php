<?php

/**
 * Hook to hide name servers
 * 
 * https://whmcs.community/topic/254393-dedicated-order-form-ns1-and-2-field-removal/
 */

add_hook('ShoppingCartConfigureProductAddonsOutput', 1, function ($vars) {
    add_hook('ClientAreaFooterOutput', 1, function ($vars) {
        return "
            <script>
                jQuery(document).ready(function() {
                    jQuery.each([ 'inputNs1prefix', 'inputNs2prefix' ], function(index, value) {
                        jQuery('#' + value).parent().hide();
                    });
                });
            </script>
        ";
    });
});


add_hook('ClientAreaPageCart', 1, function ($vars) {
    if ($_GET['a'] === 'confproduct' and (!empty($_GET['i']) || $_GET['i'] === '0')) {
        return array('server' => array('ns1prefix' => 'ns1', 'ns2prefix' => 'ns2'));
    }
});
