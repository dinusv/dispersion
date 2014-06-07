<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** 
 * @author DinuSV
 * @version : 1.1
 */

/* Set up locations */

$locations = array(
	'libraries'        => ROOT . DS . 'libraries',
	'libraries_custom' => APPFILESROOT . DS . 'libraries',
	'helpers_custom'   => APPFILESROOT . DS . 'helpers_custom',
	'exceptions_custom'=> APPFILESROOT . DS . 'exceptions',
	'config'           => APPFILESROOT . DS . 'config',
	'controllers'      => APPFILESROOT . DS . 'control',
	'models'           => APPFILESROOT . DS . 'model',
	'views'            => APPFILESROOT . DS . 'views'
);

/* Require startup files */

require_once ($locations['libraries'] . DS . 'core' . DS . 'config.class.php'  );
require_once ($locations['libraries'] . DS . 'core' . DS . 'loader.class.php'  );
require_once ($locations['libraries'] . DS . 'core' . DS . 'debug.class.php'   );
require_once ($locations['libraries'] . DS . 'core' . DS . 'error.class.php'   );
require_once ($locations['libraries'] . DS . 'core' . DS . 'autoload.class.php');

/* Require configuration files */

require_once ($locations['config'] . DS . 'config.php' );
require_once ($locations['config'] . DS . 'errors.php' );

/* Load the required classes */

$loader = new Loader( $locations );

/* Start */

$loader->callUrl();