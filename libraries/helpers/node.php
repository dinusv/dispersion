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
 * @brief General node for tree like structures.
 */
class Node{
	
	protected
		/** 
		 * @var $content
		 * mixed : Node content, will output before the nodes children in case of recursion
		 */
		$content = '',
		
		/** 
		 * @var $content_after
		 * mixed : Content to output after the nodes children in case of recursion
		 */
		$content_after = '',
		
		/** 
		 * @var $children
		 * array : Array of Node type elements
		 */
		$children = array(),
		
		/** 
		 * @var $parent
		 * Node : The parent of the current node, this field is optional
		 */
		$parent = null;
	
	/** Constructor
	 * 
	 * @param mixed $content
	 * @param Node $parent    : the parent of this node ( can be null )
	 * @param array $children : children of this node
	 */
	public function Node( $content, Node $parent = null, $children = array() ){
		$this->content = $content;
		$this->parent = $parent;
		$this->children = $children;
	}
	
	/** Add a child to this node
	 * 
	 * @param Node $child
	 * 
	 * @return Node : current object
	 */
	public function addChild( Node $child ){
		$this->children[] = $child;
		return $this;
	}
	
	/** Add a child at a specified position in this node
	 * 
	 * @param Node $child
	 * @param integer $index : the position to add the node at
	 * 
	 * @return Node          : current object
	 */
	public function addChildAt( Node $child, $index ){
		$this->children[$index] = $child;
		return $this;
	}
	
	/** Check if this node has a given child
	 * 
	 * @param Node $child
	 * 
	 * @return boolean : true if the child is found, false otherwise
	 */
	public function hasChild( Node $child ){
		foreach( $this->children as $val ){
			if ( $val === $child ) return true;
		}
		return false;
	}
	
	/** Find a child in this Node by searching recursively in all it's children
	 * 
	 * @param Node $child
	 * 
	 * @return boolean : true if the child is found, false otherwise
	 */
	public function findChild( Node $child ){
		foreach( $this->children as $val ){
			if ( $val === $child ) return true;
			if ( $val->findChild( $child ) ) return true;
		}
		return false;
	}
	
	/** Find a child in this Node, or in it's children that has the specified content
	 * 
	 * @param string $content
	 * 
	 * @return boolean : true if the child is found, false otherwise
	 */
	public function findChildWith( $content ){
		foreach( $this->children as $val ){
			if ( $val->__toString() === $content ) return true;
			if ( $val->findChildWith( $content ) ) return true;
		}
		return false;
	}
	
	/** Retrieve the node child at a given index
	 * 
	 * @param integer $index
	 * 
	 * @return Node
	 */
	public function getChildAt( $index ){
		if ( isset( $this->children[$index] ) ) return $this->children[$index];
		else throw new IndexOutOfBoundsException( 'There is no child at ' . $index );
	}
	
	/** Get the index of a child
	 * 
	 * @param Node $child
	 * 
	 * @return Node    : the child if it's found
	 * @return boolean : false if no child is found
	 */
	public function getChildIndex( Node $child ){
		foreach( $this->children as $key => $val ){
			if ( $val === $child ) return $key;
		}
		return false;
	}
	
	/** Get the child Node that contains the specified content.
	 * 
	 * @param string $content
	 * 
	 * @return Node    : the child if it's found
	 * @return boolean : false if no child is found
	 */
	public function getChildWith( $content ){
		foreach( $this->children as $key => $val ){
			if ( $val->__toString() === $content ) return $val;
		}
		return false;
	}
	
	/** Remove a given child
	 * 
	 * @param Node $child
	 * 
	 * @return Node : current object
	 */
	public function removeChild( Node $child ){
		foreach( $this->children as $key => $val ) 
			if ( $child === $val ) unset( $this->children[$key] );
		return $this;
	}
	
	/** Remove a child at a given position
	 * 
	 * @param integer $index : the position of the child to be removed
	 * 
	 * @return Node : current object
	 */
	public function removeChildAt( $index ){
		unset( $this->children[$index] );
		return $this;
	}
	
	/** Check if this node is a leaf ( has any children )
	 * 
	 * @return boolean : true if it's a leaf, false otherwise
	 */
	public function isLeaf(){
		if ( count( $this-children ) === 0 ) return true;
		else return false;
	}
	
	/** Check if this node is on top of the hierarchy
	 * 
	 * @return boolean : true if it's on top, false otherwise
	 */
	public function isTop(){
		if ( $this->parent === null ) return true;
		else return false;
	}
	
	/** Set the content of this node
	 * 
	 * @param string $content
	 * 
	 * @return Node : current object
	 */
	public function setContent( $content ){
		$this->content = $content;
		return $this;
	}
	
	/** Set the content to be printed in case of recursion
	 * 
	 * @param string $content
	 * 
	 * @return Node : current object
	 */
	public function setContentAfter( $content ){
		$this->content_after = $content;
		return $this;
	}
	
	/** To string method
	 * 
	 * @return string
	 */
	public function __toString(){
		return $this->content;
	}
	
	/** Recursive output of this node and it's children
	 * 
	 * @return string : the content and it's children
	 */
	public function recursive(){
		$s = '';
		foreach( $this->children as $child ){
			$s .= $child->recursive();
		}
		return $this->content . $s . $this->content_after;
	}
	
}