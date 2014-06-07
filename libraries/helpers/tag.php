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
 * @version 1.2
 * @author DinuSV
 */

/** 
 * @ingroup helpers
 * @brief Html tag.
 */
class Tag{
	
	private
		/** 
		 * @var $type
		 * string : Tag type
		 */
		$type,
		
		/** 
		 * @var $attributes
		 * array : Tag Attributes
		 */
		$attributes = array(),
		
		/** 
		 * @var $content
		 * array : Tag content
		 */
		$content = array();
	
	/** Constructor
	 *
	 * @param string $type      : type of the tag
	 * @param array $attributes : [optional] attributes for the tag
	 */
	public function Tag( $type, $attributes = array() ){
		$this->type = $type;
		$this->attributes = $attributes;
	}
	
	/** Static constructor to support chaining
	 * 
	 * @param string $type
	 * @param array $attributes
	 * @see Tag/constructor
	 */
	public static function newInstance( $type, $attributes = array() ){
		return new Tag( $type, $attributes );
	}
	
	/** Get channel type
	 * 
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}
	
	/** Get the value of an attribute for this Tag
	 *
	 * @param string $name : name of the attribute
	 *
	 * @return string      : the value of the attribute
	 * @return null        : in case the attribute cannot be found
	 */
	public function getAttribute( $name ){
		if ( $this->hasAttribute( $name ) ) {
			return $this->attributes[$name];
		} else
			return null;
	}
	
	/** Get all attributes
	 *
	 * @return array
	 */
	public function getAttributes(){
		return $this->attributes;
	}
	
	/** Check if an attribute exists for this tag
	 *
	 * @param string $name : name of the attribute
	 *
	 * @return boolean     : true if the attribute exists, false otherwise
	 */
	public function hasAttribute( $name ){
		if ( isset( $this->attributes[$name] ) )
			return true;
		else return false;
	}
	
	/** Set an attribute or add a set of attributes
	 *
	 * @param array $attribute  : attributes to set as a hashmap
	 * @param string $attribute : name of the attribute
	 *
	 * @param string $value     : [optional] the value for a single attribute
	 *
	 * @return Tag : current object
	 */
	public function setAttribute( $attribute, $value = '' ){
		if ( !is_array( $attribute ) ){
			$this->attributes[$attribute] = $value;
		} else {
			$this->attributes = array_merge( $this->attributes, $attribute );
		}
		return $this;
	}
	
	/** Append content to this tag. This can be either another tag or a string value
	 *
	 * @param array $content  : append an array of tags and strings to this tag
	 * @param string $content : append a text to this tag
	 * @param Tag $content    : append a Tag child
	 *
	 * @return Tag : current object
	 */
	public function append( $content = array() ){
		if ( !is_array( $content ) ){
			$this->content[] = $content;
		} else {
			$this->content = array_merge( $this->content, $content );
		}
		return $this;
	}
	
	/** Append this tag to another one
	 *
	 * @param Tag $tag : the tag to append to
	 *
	 * @return Tag : current object
	 */
	public function appendTo( Tag $tag ){
		$tag->append( $this );
		return $this;
	}
	
	/** Get the current tags children
	 *
	 * @return array
	 */
	public function getChildren(){
		$children = array();
		foreach( $this->content as $item ){
			if ( is_object( $item ) )
				$children[] = $item;
		}
		return $children;
	}
	
	/** Get the html content for this tag
	 *
	 * @param integer $indent : [optional] in case you need to use indentation, set this to 0,
	 *      or to the number of tabs you want to start indenting at
	 *
	 * @return string
	 */
	public function html( $indent = -1 ){
		if ( !count( $this->content ) ) return '';
		$base = "";
		$tabs = "";
		if ( $indent >= 0 ){
			$indent++;
			for ( $i = 0; $i < $indent; $i++ )
				$tabs .= "\t";
		}
		foreach ( $this->content as $item ){
			if ( is_object( $item ) )
				$base .= $item->indent( $indent );
			else
				$base .= $tabs . $item;
			if ( $indent >= 0 ) $base .= "\n";
		}
		return $base;
	}
	
	/** Get this object and it's html string representation
	 *
	 * @param int $indent : [optional] in case you need to use indentation, set this to 0,
	 *      or to the number of tabs you want to start indenting at
	 *
	 * @return string
	 */
	public function indent( $indent = -1 ){
		$tabs = "";
		$nl = "";
		// Check indentation
		if ( $indent >= 0 ){
			for ( $i = 0; $i < $indent; $i++ )
				$tabs .= "\t";
			$nl = "\n";
			$indent++;
		}
		$base = $tabs . "<" . $this->type;
		// Build attributes
		foreach( $this->attributes as $name => $val ){
			$base .= " " . $name . "=\"" . $val . "\"";
		}
		// Get content
		if ( count( $this->content ) ){
			$base .= ">";
			$base .= $nl . $this->html( $indent - 1 );
			$base .= $tabs . "</" . $this->type . ">";
		} else {
			$base .= '/>';
		}
		return $base;
	}
	
	/** Tostring method
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->indent();
	}
	
}