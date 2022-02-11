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

## Get Usage

ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" root@172.168.1.41 'virsh -r domifstat 35 macvtap5'

https://stackoverflow.com/questions/46326618/kvm-libvirt-how-do-i-get-total-traffic-used-by-kvm-libvirt-virtual-machine

## Connect via SSH and output virsh XML list
ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" root@172.168.1.41 'virsh -r list'

### XML Format
ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" root@172.168.1.41 'virsh -r dumpxml 35'

## Check if SSH is working
ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" root@172.168.1.41

## Read guestagent status

Before

<channel type='unix'>
    <source mode='bind' path='/var/lib/libvirt/qemu/channel/target/domain-35-nextcloud_a.b/org.qemu.guest_agent.0'/>
    <target type='virtio' name='org.qemu.guest_agent.0' state='disconnected'/>
    <alias name='channel0'/>
    <address type='virtio-serial' controller='0' bus='0' port='1'/>
</channel>

After

<channel type='unix'>
      <source mode='bind' path='/var/lib/libvirt/qemu/channel/target/domain-35-nextcloud_a.b/org.qemu.guest_agent.0'/>
      <target type='virtio' name='org.qemu.guest_agent.0' state='connected'/>
      <alias name='channel0'/>
      <address type='virtio-serial' controller='0' bus='0' port='1'/>
</channel>

<memory unit='KiB'>8388608</memory>
<currentMemory unit='KiB'>8388608</currentMemory>
<vcpu placement='static'>4</vcpu>



virsh -r dumpxml 35

/root]> virsh -r list
 Id    Name                           State
----------------------------------------------------
 35    nextcloud_a.119.36.74    running

x = hv4-41 = 172.168.1.41




## References Section

We keep them here because we don't want to clutter the readme

Virsh
See: https://help.ubuntu.com/community/KVM/Virsh