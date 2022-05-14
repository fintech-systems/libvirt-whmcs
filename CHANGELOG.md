# Changelog

All notable changes to `whmcs-libvirt` will be documented in this file.

## 0.2.3 - 2022-05-14

Added

- Ability to suspend and unsuspend domains from WHMCS
- Added to troubleshooting guide how to do generic language file and how to find front-end asset that uses this language file
- Added more information to troubleshooting guide for when WHMCS linking doesn't work
- Updated the README with information on updating the modules
- Added module logging for suspensions
- The Libvirt resource constructor will now throw an exception is no username or IP address was specified

## 0.2.2 - 2022-03-11

- Fixed bug where version update wanted to migrate the default demo database

## 0.2.1 - 2022-03-02

- Fixed CPUs in use by not counting non running CPUs

## 0.2.0 - 2022-03-02

- The module was rewritten to work with UUIDs instead of Domain IDs. This allows this module to also list VMs which are offline. This is breaking change since Product Definitions worked on domainid|Domain ID custom field before. It's essentially a two step process that first fetches state and name and then UUID
- Added a screenshot showing the sole important setting for custom product definitions
- Added a screenshot showing where to associate existing services (using the UUID)
- Removed sample code when activating the add-on module
- Removed some commented code
- Added total CPUs and RAM in Node display - needs to be manually input in database
- Some database column renaming to make things more consistent

## 0.1.4 - 2022-02-24

### Added
- Added module logging in Libvirt when fetching all domains using virshDumpxml

### Changed
- Removed WHMCS sample provisioning code at server module `libvirt_ConfigOptions` and converted to [] format

### Fixed
- Fixed bug on test connection where username and IP needed to be split

## 0.1.3 - 2022-02-11

- Added Start, Shutdown, Reboot, and Reset command in client area
- First release
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