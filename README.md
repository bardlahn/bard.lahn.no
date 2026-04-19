# bard.lahn.no

### Website for Bård Lahn
Main branch used by production server
Dev branch live on development server

### Server automation

_Note: This is not yet set up correctly!_
    
PULL webhook:
```
{BOKS}/api/exec/pull/{SECRET}
```

PUSH webhook:
```
{BOKS}/api/exec/push/{TOTP}/[push-message]
```
