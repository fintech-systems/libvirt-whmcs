#!/bin/bash

# Usage
# pwd should be 'whmcs_installation/modules/servers/libvirt/lib'
# sh update.sh

echo "Libvirt Update Script"
echo "====================="

echo "Cloning repo"
git clone https://github.com/fintech-systems/libvirt-whmcs
echo "------------"

echo "Copying addons to WHMCS installation location..."
cp -r libvirt-whmcs/addons/libvirt/ ../../../addons/        
echo "------------------------------------------------"

echo "Copying servers to WHMCS installation location..."
cp -r libvirt-whmcs/servers/libvirt ../../../servers/ 
echo "-------------------------------------------------"

echo "Removing clone"
rm -rf libvirt-whmcs
echo "--------------"

echo "DONE"
