# Dispersion Framework

Dispersion is a lightweight php framework. It was converted to a model-view-controller based architecture from a set of pluginable libraries implemented to speed up work on already existing websites. Although the code is not updated much lately, it's perfectly compatible with the latest versions of php, and can be used as a good lightweight startup for custom projects.

The website is available at [http://dispersion.dinusv.com](http://dispersion.dinusv.com)

## Features

 * Url routing
 * Debugging Module
 * Automatic session and user management
 * Download and upload module
 * Pagination management
 * Error and Exception handling
 * Form validation
 * Image conversion and watermarking
 * Facebook and twitter integration
 * Paypal ipn and form integration
 * Rss management module

## Quick setup

Get the latest version from github : 

```
git clone git@github.com:dinusv/dispersion.git dispersion
```

Copy it to your *web-server* or *htdocs* folder. Go to *dispersion/config/config.php* and set up the following settings : 

```PHP
Config::db( 'driver', 'mysql' ); // database you will be using
Config::db( 'database', '' ); //the database you will use
Config::db( 'host', '' ); //the database host, usually this is set to localhost
Config::db( 'user', '' ); //the user of the database
Config::db( 'password', '' ); //password for the user

```

Test the setup by visiting the homepage of Dispersion, you should see the following message : 

```
Welcome to the homepage.
This is the home controller, you can edit this file in "application/controllers" directory. 
```

## Documentation

A start-up tutorial, together with the rest of the API is available at [http://dispersion.dinusv.com/documentation](http://dispersion.dinusv.com/documentation).
