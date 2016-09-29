Vineyard Module 
========================================

Author : Bruno Généré      <bgenere@webiseasy.org>


The aim of this module is to manage the complete life cycle of vine cultivation.
You could track all operations and tasks done by plots of land, type of vine and collect results.

Using this module the vine cultivation could be followed and enhanced as needed year by year. 

Main features
--------------------

This module is under development.

Current list of features :

- Plots of land catalog (first draft)


Pre-requisites
-----------------------

This module targets the Dolibarr platform. (>= 4.0.x)


Install
-------

- Make sure Dolibarr is already installed and configured on your workstation or development server.

- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file

- Find the following lines:
    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment these lines (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root = 'http://localhost/Dolibarr/htdocs';
        $dolibarr_main_document_root = '/var/www/Dolibarr/htdocs';
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root = 'http://localhost/Dolibarr/htdocs';
        $dolibarr_main_document_root = 'C:/My Web Sites/Dolibarr/htdocs';
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```

    For more information about the ```conf.php``` file take a look at the conf.php.example file.

- Clone the repository in ```$dolibarr_main_document_root_alt/vignoble```

*(You may have to create the ```htdocs/custom``` directory first if it doesn't exist yet.)*
```sh
git clone git@github.com:GPCsolutions/dolibarr-module-template.git mymodule
```

- From your browser:

    - Log into Dolibarr as a super-administrator

    - Under "Setup" -> "Other setup", set ```MAIN_FEATURES_LEVEL``` to ```2```

    - Go to "Setup" -> "Modules"

    - The module is under one of the tabs

    - You should now be able to enable the new module and start using it ;)

Contributions
-------------

Feel free to contribute and report defects at <https://github.com/bgenere/vignoble/issues>

Licenses
--------

### Main code

![GPLv3 logo](dev/img/gplv3-127x51.png)

GPLv3 or (at your option) any later version.

See [COPYING](COPYING) for more information.

### Other Licenses

#### [Parsedown](http://parsedown.org/)

Used to display this README in the module's about page.
Licensed under MIT.

#### [GNU Licenses logos](https://www.gnu.org/graphics/license-logos.html)

Public domain


#### Documentation

All texts and readmes.

![GFDL logo](dev/img/gfdl-129x44.png)
