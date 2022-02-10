#!/bin/bash

# pwd
# .../whmcs/modules/servers/libvirt/lib

git clone https://github.com/fintech-systems/libvirt-whmcs
cp -r libvirt-whmcs/addons/libvirt/ ../../../addons/        
cp -r libvirt-whmcs/servers/libvirt ../../../servers/ 
rm -rf libvirt-whmcs
