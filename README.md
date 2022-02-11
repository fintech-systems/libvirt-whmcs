# Libvirt module for WHMCS

An open source module to interface with a libvirt system using the WHMCS hosting billing solution

* https://developer.ibm.com/tutorials/l-libvirt/
* https://www.whmcs.com/

## Features

### WHMCS Addon Module

- Display libvirt nodes and guests
- Display number of vCPUs and RAM in use by all domains
- Display the total number of vCPUs and RAM commissioned on the nodes
- Button to fetch nodes and domains based on libvirst server defined in WHMCSnodes

### Client Area Features

- View amount of vCPUs and RAM
- Start Server
- Shutdown Server
- Reboot Server
- Reset Server

## Installation

* We highly recommend using a VPN to connect to Libvirt nodes so that they are not publically accessible
* Add your WHMCS server's public key to the Libvirt nodes
* Copies all the files under /whmcs_installation/modules/
* First activate the module. This will create the database tables required by the module
* Then add Libvirt servers using WHMCS Systems Settings -> Server
* You only have have to specify the user and IP address since you've already added a key
* Go back to the module and press the 'Refresh Domains' button

## Screenshots

### Client Area - Reboot Server Confirmation

![Libvirt Server Module Client Area Reboot Server](./servers/libvirt/lib/Screenshots/client_area_reboot_server.png)

### Add on Module

![Libvirt Add On Module Landing Page](./addons/libvirt/lib/Screenshots/addon_module_landing_page.png)

## VM Guest Notes

### Debian/Ubuntu

In order for the module to read the QEMU guest agent status, the following software must be installed on the guest:

`sudo apt-get install qemu-guest-agent`

Then start it:

`sudo /etc/init.d/qemu-guest-agent start`

### Developer Notes

#### Linking to WHMCS

If you have a local installed of WHMCS, e.g. in `/home/username/code/whmcs`, you can symbolically link to it so:

```bash
ln -s /home/username/code/libvirt-whmcs/addons/libvirt /home/username/code/whmcs/modules/addons/libvirt
ln -s /home/username/code/libvirt-whmcs/servers/libvirt /home/username/code/whmcs/modules/servers/libvirt
```

#### Ioncube issues

WHMCS is not PHP 8 compatile because Ioncube is not PHP 8 compatible.

Use this command to change your PHP version:

`sudo update-alternatives --config php`

If you're using Laravel Valet, you might have to also do this:

`/etc/init.d/php8.0-fpm stop`

`valet install`

## Credits

* LibreNMS kickstarted the libvirt virsh calls
* SolusVM has a great WHMCS module for virtualization which gave me lots of ideas
