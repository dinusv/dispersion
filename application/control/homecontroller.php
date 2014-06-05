<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');

class homeController extends Controller{
	
	public function index(){
		echo '<h1>Welcome to the homepage.</h1>';
		echo '<p>This is the home controller, you can edit this in your application/controllers directory.</p>';
	}
	
}