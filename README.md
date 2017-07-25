Zikula Dizkus Forums
====================

Dizkus is a discussion forum module for the Zikula Application Framework

### Branch 4
 Basic forum module.
 This version **requires** Zikula Core 1.4.6+
 The code is currently under development, but typically is in a functional
 state. Feel free to test and report issues. thank you.

 Current release 4.0.0
 This version **requires** Zikula Core 1.4.3+

### Branch 5
 Fully featured forum
 This version **requires** Zikula Core 1.5.0+
 The code is currently under development, but typically is in a functional
 state. Feel free to test and report issues. thank you.

### master
 Newest core features
 This version **requires** Zikula Core 2.0.0+
 The code is currently under development, but typically is in a functional
 state. Feel free to test and report issues. thank you.

#### Before you pull:

  1. uninstall and delete the module.
  2. delete your local repo and all the files.
  3. clone the repo into `/modules/zikula/dizkus-module`

## Instalation

Your directory structure should look like so:

```
  /modules
      /zikula
          /dizkus-module
            /Block
            /Command
            etc...
```

#### From zip

    1. Extract zip file to modules/zikula.
    2. Change directory that contains Dizkus module to dizkus-module
    3. Install module in Zikula extensions.

#### Composer

    todo...

## Upgrade

    Avoid any data loss by creating full backup before upgrade! 

#### Dizkus version 4.0.0
```
    UPGRADING from Dizkus 3.1
    You must add to `personal_config.php`:
    `$ZConfig['System']['prefix'] = 'pn';`
    (or whatever your table prefix is from your old installation)
    note that upgrading can take a long time and may require updating your `.htaccess` or `php.ini` file to increase 
    time limits and memory allowed.
```
#### Dizkus version > 4.1.0

    Upgrade from version 3 is handled using import facility.
    Upgrade from version 4 is done normal way.

