<?php

// die(print_r($_POST));

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    require_once __DIR__ . '/../../../../whmcs/init.php';    
}

require_once __DIR__ . '/../../../servers/libvirt/lib/Libvirt.php';

use WHMCS\Database\Capsule;



Capsule::table('mod_libvirt_settings')->updateOrInsert(
    [
        'setting' => 'ScriptPreConnection'
    ],
    [
        'value' => $_POST['scriptPreConnection']
    ]
);

// echo "hello";
// echo ":" . $_POST['scriptPreConnection'];
//  echo print_r($_POST,1);
