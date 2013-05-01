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
 * @license   : http://dispersion.inevy.com/license
 * @namespace : optional
 * @file      : libraries/optional/rss.class.php
 * @requires  : class Tag
 * @version   : 1.1
 */ 
 
class Rss{
	
	private 
		/** Rss header
		 * 
		 * @var string
		 */
		$xml,
		
		/** Rss main tag
		 * 
		 * @var Tag
		 */
		$rss,
		
		/** Channel tag
		 * 
		 * @var Tag
		 */
		$channel,
		
		/** Allowed nested items for the channel tag
		 * 
		 * @var array
		 */
		$channel_allowed_items = array(),
		
		/** Added namespaces
		 * 
		 * @var array
		 */
		$namespaces = array(),
		
		/** Channel tag items
		 * 
		 * @var array
		 */
		$items = array();
	
	/** Constructor 
	 */
	public function Rss(){	
		/* xml head */
		$this->xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
		
		/* create the rss tag */
		$this->rss = new Tag('rss');
		$this->rss->setAttribute( array('version' => '2.0') );
		
		/* create the channel and add it in the rss */
		$this->channel = new Tag('channel');
		$this->channel_allowed_items = array('title', 'link', 'description', 'language', 'item', 'copyright', 'managingEditor', 'webMaster',
			'pubDate', 'lastBuildDate', 'category', 'generator', 'docs', 'cloud', 'ttl', 'image', 'rating',
			'textInput', 'skipHours', 'skipDays', 'image', 'cloud');
		$this->rss->append( $this->channel );
	}	
	
	/** Function to be used for adding a namespace
	 * 
	 * @param string $namespace
	 * @param string $link       : the link to the namespace 
	 */
	public function addNameSpace( $namespace, $link ){
		list( $pref, $namesp ) = explode( ':', $namespace );
		$this->channel_allowed_items[] = $namesp;
		$this->rss->setAttribute( $namespace, $link );
		return $this;
	}
	
	/* 
	 * Functions for the channel field
	 * ----------------------------------------- */
	 
	/** Set the channel field
	 * 
	 * @param string $title
	 * @param string $link
	 * @param string $description
	 */ 
	public function setChannel( $title, $link, $description) {
		$title_tag = new Tag( 'title' );
		$title_tag->append( $title );
		$desc_tag = new Tag( 'description' );
		$desc_tag->append( $description );
		$link_tag = new Tag( 'link' );
		$link_tag->append( $link );
		$this->channel->append( array( $title_tag, $desc_tag, $link_tag ) );
	}
	
	/** Add an optional channel field
	 * 
	 * @param Tag $value
	 */
	public function addChannelValue( Tag $value ){
		$namesp = explode(':', $value->getType(), 2 );
		if ( in_array( $namesp[0], $this->channel_allowed_items ) )
			$this->channel->append( $value );
		else throw new InvalidArgumentTypeException( "Tag " . $value->getType() . " not allowed" );
		return $this;
	}
	
	/** Create a new rss text input field
	 * 
	 * @param string $title
	 * @param string $description
	 * @param string $name
	 * @param string $link
	 * 
	 * @return Tag : returns the new text input field
	 */
	public function newTextInput( $title, $description, $name, $link ){
		$textInput = new Tag('textInput');
		$title_tag = new Tag('title');
		$title_tag->append( $title );
		$desc_tag = new Tag('description');
		$desc_tag->append( $description );
		$name_tag = new Tag( 'name' );
		$name_tag->appendd( $name );
		$link_tag = new Tag( 'link' );
		$link_tag->append( $link );
		$textInput->append( array( $title_tag, $desc_tag, $name_tag, $link_tag ) );
		return $textInput;
	}
	
	/** Create a new rss image field
	 * 
	 * @param string $url
	 * @param string $description
	 * @param string $link
	 * @param string $width       : optional
	 * @param string $height      : optional
	 * @param string $description : optional
	 * 
	 * @return Tag             : returns the new image field
	 */
	public function newImage( $url, $title, $link, $width = null, $height = null, $description = null ){
		$image = new Tag('image');
		$image->addValue( array(
			new Tag('url', $url, 1),
			new Tag('title', $title, 1),
			new Tag('link', $link, 1)
		));
		/* add optional parameters */
		if ( $width != null ) $image->addValue( new Tag( 'width', $width, 1 ) );
		if ( $height != null ) $image->addValue( new Tag( 'height', $height, 1) );
		if ( $description != null ) $image->addValue( new Tag( 'description', $description, 1 ) );
		return $image;
	}
	
	/** Create a new item
	 * 
	 * @param string $title       : optional if $description is added
	 * @param string $description : optional if $title is added
	 * 
	 * @return Tag             : return the new item
	 */
	public function newItem( $title = null, $description = null ){
		if ( $title === null && $description === null ){
			throw new InvalidArgumentTypeException('At least one argument is needed. Returning null.');
		}
		$item = new Tag('item');
		if ( $title !== null) {
			$title_tag = new Tag('title');
			$title_tag->append( $title )->appendTo($item);
		}
		if ( $description !== null ) {
			$desc_tag = new Tag('description');
			$desc_tag->append( $description )->appendTo( $item );
		}
		return $item;
	}
	
	/* 
	 * Validate and output the rss file
	 * ----------------------------------------- */
	 
	/** toString method
	 * 
	 * @throws InvalidArgumentTypeException
	 */
	public function __toString(){
		$return = $this->xml . "\n";
		$return .= $this->rss->indent(1);
		return $return;
	}
	
	/** Output the object to the specified file
	 * 
	 * @param string $filename : the full path of the file
	 */
	public function outputToFile( $filename ){
		$fh = fopen( $filename, 'w');
		fprintf($fh, $this);
		fclose($fh);
	}
	
	
}