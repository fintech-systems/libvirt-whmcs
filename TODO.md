## Fix

- Bigger font issue - discuss with design team
- English language stuff missing, append array as per module example on internet

## Upcoming Work

- If no username, then don't display SSH button
- Reloading of all VMs based on name and not Domain ID, including powered down ones
-- DUP: Graceful handling for powered off machines (virsh list --all shows these)
- In Client Area, figure out where 500 GB add-on is coming from, maybe test with extra vCPU
- jQuery to poll machine state every 30 seconds

## Ongoing

- Remove extra buttons and stuff on both server and addon module

### Later

- Show interface traffic
- Display disabled state inverted as green check mark on add-on module landing page
-- images/icons/disabled.png
-- images/icons/tick.png

#### Depriorited

- Find font-awesome fresh icon as fa-refresh doesn't seem to work on Addon module Refresh button
- See if Guest Agent is running
- See Guest OS (<libosinfo:os id="http://ubuntu.com/ubuntu/20.04"/>)

##### Stretch Goals

