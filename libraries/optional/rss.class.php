<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/****************************************************************************
**
** Copyright (C) 2010-2014 Dinu SV.
** (contact: mail@dinusv.com)
** This file is part of Dispersion framework.
** 
** The file may be used under the terms of the MIT license, appearing in the
** file LICENSE.MIT included in the packaging of this file.
**
****************************************************************************/

 /**
 * @version 1.2
 * @author DinuSV
 */

/** 
 * @ingroup libraries
 * @brief Rss file wrapper.
 * 
 * The first thing when creating an rss file is setting up a channel ( Rss::setChannel ). The channel 
 * would next require tags of different types : title, link, description, language, item, copyright, 
 * managingEditor, webMaster, pubDate, lastBuiltDate, category, generator, docs, cloud, ttl, image, 
 * rating, textInput, skipHours, skipDays, image, cloud. 
 * 
 * Here's an example of a small rss file :
 * 
 * @code
 * $rss = new Rss();
 * $rss->setChannel(
 *     'Rss feed title',
 *     'http://link-to-rss-feed',
 *     'Rss feed description'
 * );
 * 
 * // Create a pubDate tag
 * $pubDate = new Tag('pubDate');
 * $pubDate->append( date("D, d M Y H:i:s T") );
 *  
 * // Add tag to Rss
 * $rss->addChannelValue( $pubDate );
 * @endcode
 * 
 * Adding unsupported values will result in an InvalidArgumentTypeException. To support more values, 
 * namespaces need to be added :
 * 
 * @code
 * // Adding the follwing namespace will allow you to add 'sy' type tags
 * $rss->addNameSpace( 'xmlns:sy', 'http://purl.org/rss/1.0/modules/syndication/' );
 * 
 * $updatePeriod = new Tag('sy:updatePeriod');
 * $updatePeriod->append('hourly');
 * $rss->addChannelValue( $updatePeriod );
 * @endcode
 * 
 * ## Adding items
 * 
 * Items contain the actual feed data. They are also added as channel values. An item is required to have
 * at least a title or content. Rss::newItem creates a new tag item. To add just one of the parameters, 
 * set the other one to 'null'. If both are null, an InvalidArgumentTypeException will be thrown.
 * 
 * @code
 * // Add item with set title
 * $rss->addChannelValue( $rss->newItem( 'My Feed', null ) );
 *  
 * // Add item with set content
 * $rss->addChannelValue( $rss->newItem( null, 'About my feed' ) );
 *  
 * // Custom values can also be added to items
 * $pubDate = new Tag('pubDate');
 * $pubDate->append( date("D, d M Y H:i:s T" );
 * $item = $rss->newItem( 'My feed', 'About My Feed' );
 * $item->append( $pubDate );
 * $rss->addChannelValue( $item );
 * @endcode
 * 
 * Images and text input fields can also be created : 
 * 
 * @code
 * $rss->addChannelValue( $rss->newImage( 'myImage.jpg', 'My Image', 'http://image-href' ) );
 * $rss->addChannelValue( $rss->newTextInput( 'My Label', 'About My Input', 'name', 'http://submit' ) );
 * @endcode
 * 
 * ## Saving data
 * 
 * The object can either be echoed out, or written to a file :
 * 
 * @code
 * // Output content on screen
 * echo $rss;
 *  
 * // Output content to a file
 * $rss->outputToFile( 'rssFile.rss' );
 * @endcode
 * 
 */
class Rss{
	
	private 
		/** 
		 * @var $xml
		 * string : Rss header
		 */
		$xml,
		
		/** 
		 * @var $rss
		 * Tag : Rss main tag
		 */
		$rss,
		
		/** 
		 * @var $channel
		 * Tag : Channel tag
		 */
		$channel,
		
		/** 
		 * @var $channel_allowed_items
		 * array : Allowed nested items for the channel tag
		 */
		$channel_allowed_items = array(),
		
		/** 
		 * @var $namespaces
		 * array : Added namespaces
		 */
		$namespaces = array(),
		
		/** 
		 * @var $items
		 * array : Channel tag items
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
	 * @param string $title
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