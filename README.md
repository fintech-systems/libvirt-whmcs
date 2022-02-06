# WHMCS Libvirt

An open source module to interface with a libvirt system using WHMCS

PRE-RELEASE SOFTWARE

## Planned Features

Start

- Get a list of all VMs
- Associate VM with client's account
- See number of vCPUs and memory
- Reboot VM
- See if Guest Agent is running
- See Guest OS (<libosinfo:os id="http://ubuntu.com/ubuntu/20.04"/>)

Later

- Show interface traffic

## Installation

You need virsh
apt install libvirt-clients

## VM Guest Notes

### Debian/Ubuntu

In order for the module to read the QEMU guest agent status, the following software must be installed on the guest:

`sudo apt-get install qemu-guest-agent`

Then start it:

`sudo /etc/init.d/qemu-guest-agent start`

### Developer Notes

#### Ioncube issues

WHMCS is not PHP 8 compatile because Ioncube is not PHP 8 compatible.

Use this command to change your PHP version:

`sudo update-alternatives --config php`

If you're using Laravel Valet, you might have to also do this:

`/etc/init.d/php8.0-fpm stop`

`valet install`
