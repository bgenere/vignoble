Dolibarr Module Vignoble
========================================

This is a full featured module for Dolibarr.
It's a tool for module developers to kickstart their project and give an hands-on sample of which features Dolibarr has to offer for module development.



Documentation
-------------

[Module tutorial](http://wiki.dolibarr.org/index.php/Module_development)

[Dolibarr development](http://wiki.dolibarr.org/index.php/Developer_documentation)

### Translations

Dolibarr uses [Transifex](http://transifex.com) to manage it's translations.

This template also contains a sample configuration for Transifex managed translations under the hidden [.tx](.tx) directory.

For more informations, see the [translator's documentation](http://wiki.dolibarr.org/index.php/Translator_documentation).

The Transifex project for this module is available at <http://transifex.com/projects/p/dolibarr-module-template>

Install
-------

- Make sure Dolibarr (>= 3.3.x) is already installed and configured on your workstation or development server.

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

*Note that for Dolibarr versions before 3.5, the ```$dolibarr_main_url_root_alt``` has to be an absolute path*

- Clone the repository in ```$dolibarr_main_document_root_alt/mymodule```

*(You may have to create the ```htdocs/custom``` directory first if it doesn't exist yet.)*
```sh
git clone git@github.com:GPCsolutions/dolibarr-module-template.git mymodule
```

- From your browser:

    - Log into Dolibarr as a super-administrator

    - Under "Setup" -> "Other setup", set ```MAIN_FEATURES_LEVEL``` to ```2```

    - Go to "Setup" -> "Modules"

    - The module is under one of the tabs

    - You should now be able to enable the new module and start coding ;)

Contributions
-------------

Feel free to contribute and report defects at <http://github.com/GPCsolutions/dolibarr-module-template/issues>

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
