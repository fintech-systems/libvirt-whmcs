# Changelog

All notable changes to `whmcs-libvirt` will be documented in this file.

## 0.1.3 - 2022-02-11

- Added Start, Shutdown, Reboot, and Reset command in client area
- First packagist release
- Moved other templates to examples folder
- New client area based loosely on cPanel template

## 0.1.2 - 2022-02-10

- Show power state, vcpus, and ram in client and admin area
- Added a Test.php script in server lib directory
- Added a crude updater that pulls the add-on and module sources 

## 0.1.1 - 2022-02-07

- The addon module landing page will now display any linked WHMCS products, as long as the Domain ID matches
-- It will throw an exception if two IDs matches
- Split TODO away from README
- Client area has some dummy output
- Renamed repo from whmcs-libvirt to libvirt-whmcs
- CPU and RAM totals in use are now displaying
- Added hooks code to hide name servers

## 0.1.0 - 2022-02-07

- Display a table with all VM hosts and guests
- Ability to Refresh Guests (retrieve all running guests from the active hosts)

## 0.0.1 - 2022-02-06

- Initial pre-release