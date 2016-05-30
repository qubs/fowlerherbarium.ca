# Fowler Herbarium

This web application backs the Queen's University Biological Station [Fowler Herbarium](http://fowlerherbarium.ca).
It is written in PHP, uses MySQL as a database, Composer for PHP package management, and Bower for front-end library
management.

## Installation

1. [Install Composer](https://getcomposer.org/download/)
2. [Install Node.js](https://nodejs.org/en/download/)
3. [Install MySQL](http://dev.mysql.com/downloads/mysql/) and make sure it's running.
3. Run `npm install -g bower` to install [Bower](http://bower.io/)
4. Run `composer install` or `php composer.phar` (depending on how you installed composer) to install required PHP
packages.
5. Run `bower install` to install frontend packages.
6. TODO: INSTALL SCRIPTS ETC

## Configuration

All configuration is stored in the `config.php` file, and the variables should be set as follows:

### Database

`$__DATABASE["host"]`: Your MySQL host URL. If you do not know what to put here, it should probably be set to `localhost`.

`$__DATABASE["user"]`: The MySQL user that the software will use to access the herbarium database.

`$__DATABASE["password"]`: The password corresponding to the above user which will be used to connect to the database.

`$__DATABASE["name"]`: The name of the database for the herbarium data.

### Passwords and Security

    TODO: THIS WILL NOT BE AVAILABLE IN THE FUTURE AFTER THE SWITCH TO BUILT-IN PHP PASSWORD HASHING IS COMPLETED.

`$_HASH_COST`: This has to do with how password hashes are calculated. Don't worry too much about this, leave it at its
default value of `8`.

`$_HASH_PORTABLE`: Portable hashes should be turned on (set to `true`) only if the version of PHP being used is older
than version 5.3. This should probably not be the case, so leave it as `false`.

### Website Content

`$__UPLOAD_DIRECTORY`: The directory that the website will let administrators upload files into (typically image files).
The directory should *not* be writable by anyone except `www-data` (or whatever user your web server software uses). It
should be readable by a group you and www-data are in, and should not be readable by others.

## Updating

To update the web software, download a copy of the code in this repository. Remove all files and folders in the root
directory of the server **EXCEPT** for the `uploads` folder and your `config.php` file. Any changes to configuration
will be alluded to with changelogs and you should manually port over changes to make sure your site does not break.

    TODO: Finish updating instructions.
