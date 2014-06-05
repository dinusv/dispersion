<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
	
	/*
	 * Inevy Dispersion -  main configuration file
	 * 
	 * @version : 1.0
	 * @file    : application/config/config.php
	 * 
	 * --------------------------------------------
	 */
	
	/** Configure database options
	 * 
	 * driver   : only 'mysql' is available for now
	 * database : the database you want to use
	 * host     : the name of the host ( this is ussually 'localhost' )
	 * user     : the username to connect with
	 * password : password for the user
	 */
	Config::db( 'driver',       'mysql' );
	Config::db( 'database',     '' );
	Config::db( 'host',         '' );
	Config::db( 'user',         '' );
	Config::db( 'password',     '' );	
	Config::db( 'table_prefix', '' );
	
	/** Set up the base url of your application
	 * 
	 * @example
	 * Config::baseurl('http://localhost/')
	 * 
	 * 
	 * To add custom urls to your application as well
	 * 
	 * @example
	 * Config::url( 'images', 'http://localhost/images');
	 */
	Config::baseurl('');
	
	/** Set the default timezone
	 * 
	 * @example
	 * Config::timezone( 'America/Los_Angeles' )
	 */
	Config::timezone('America/Los_Angeles');
	
	/** The default controller to load if none is specified in the url.
	 * 
	 * @example
	 * Load the 'home' controller.
	 * Config::autoload( 'defaultcontroller', 'home' );
	 */
	Config::autoload( 'defaultcontroller', 'home' );
	
	/** These are the standard view files to be loaded throughout your application. These view 
	 * files can be eliminated for custom pages through the controller with $this->emptyView().
	 * The view files will be loaded in the order you set them. The custom locations where you
	 * can add your own files should be replaced by a number. All the numbers will be ignored
	 * when rendering the files.
	
	 * @example : 
	 * Config::autoload( 'viewfiles', array(0, 'header', 1, 'footer') );
	 * 
	 * The '0' represents the first place where you can add a file, for example you might use
	 * that to add the head of the page, and you can set the content in place of '1', depending
	 * on the page.
	 * @note Make sure the numbers you add are consecutive. Their order matters.
	 */
	Config::autoload( 'viewfiles', array( 0 ) );
	
	/** By default, the framework loads the model with the same name as the controller. To change
	 * that, you can map each controller to a model the way you want, or you can disable them by 
	 * adding the controller name with an empty field after.
	 * 
	 * @example
	 * This will load 'usermodel' for the 'users' controller
	 * Config::models( 'users' , 'usermodel' );
	 */
	Config::models( 'home', '' );
	
	/** The framework is set up to load a model for each controller. If you don't want to, or
	 * you don't like having empty models, you can disable them in 2 ways. First is adding them
	 * to the list of models above, with an empty string as their value, but to make things
	 * more organised and easier to read, you can just add them to the list below.
	 * 
	 * @example
	 * Config::models( 'home', '' );
	 * 
	 * @example
	 * Config::disablemodels( array( 'users' ) );
	 */
	Config::disablemodels( array() );
	
	/** The libraries to be loaded throughout your application. These can be both the libraries 
	 * provided by the framework and the libraries added by you in your application/libraries 
	 * directory.
	 * 
	 * @note The libraries you will want to autoload must have a no-argument constructor.
	 * 
	 * @example :
	 * Config::autoload( 'libraries', array('session') )
	 * 
	 * If you will be using sessions within your application, you can just add the session 
	 * library like above, and you will be able to access it anywhere with $this->session.
	 */
	Config::autoload('libraries', array() );
	
	/** Configure routes. The standard way the framework handles a route is 
	 * /controller/action/parameter1/parameter2/parameter3
	 * 
	 * You can change that by adding new routes to the config class. A route is added by
	 * specifying a string to match the url in the browser, and an array of strings containing
	 * the controller, action, and parameters to activate when the matched url is found. You can 
	 * use the symbol '[*]' to match any number of parameters in the url.
	 * 
	 * @example
	 * This will match routes like 'home/photos/albums', 'home/music/categories' and send it to
	 * 'mycollections' controller, 'get' method, with parameters 'photos', 'albums' or 'music',
	 * 'categories'
	 * Config::addRoute( 'home/[*]', array('mycollections', 'get', '[*] ) );
	 * 
	 * You can also limit the number of parameters
	 * 
	 * @example
	 * Minimum 2 
	 * Config::addRoute( 'home/[*>2]', array('mycollections', 'get', '[*] ) );
	 * 
	 * @example
	 * Maximum 2
	 * Config::addRoute( 'home/[*<2]', array('mycollections', 'get', '[*] ) );
	 * 
	 * @example
	 * Exactly 2
	 * Config::addRoute( 'home/[*2]', array('mycollections', 'get', '[*] ) );
	 * 
	 * @example
	 * Between 2 and 5
	 * Config::addRoute( 'home/[*>2]/[*<5]', array('mycollections', 'get', '[*] ) );
	 * 
	 * For a full documentation on adding routes check the documentation online at the
	 * configuration section.
	 */
	