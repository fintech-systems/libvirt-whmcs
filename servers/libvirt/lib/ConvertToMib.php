<?php

/**
 * Convert memory size to MiB
 */
function ConvertToMib($vmwVmMemSize)
{ 
    switch ($vmwVmMemSize['unit']) {
        case 'T':
        case 'TiB':
            $vmwVmMemSize = $vmwVmMemSize * 1048576;
            break;
        case 'TB':
            $vmwVmMemSize = $vmwVmMemSize * 1000000;
            break;
        case 'G':
        case 'GiB':
            $vmwVmMemSize = $vmwVmMemSize * 1024;
            break;
        case 'GB':
            $vmwVmMemSize = $vmwVmMemSize * 1000;
            break;
        case 'M':
        case 'MiB':
            break;
        case 'MB':
            $vmwVmMemSize = $vmwVmMemSize * 1000000 / 1048576;
            break;
        case 'KB':
            $vmwVmMemSize = $vmwVmMemSize / 1000;
            break;
        case 'b':
        case 'bytes':
            $vmwVmMemSize = $vmwVmMemSize / 1048576;
            break;
        default:
            // KiB or k or no value
            $vmwVmMemSize = $vmwVmMemSize / 1024;
            break;
    }

    return $vmwVmMemSize;
}
