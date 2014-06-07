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
 * @ingroup helpers
 * @brief Html form wrapper.
 */
class Form extends Tag{
	
	/** Constructor
	 *
	 * @param string $method : [optional] post/get
	 * @param string $action : [optional] form action
	 */
	public function Form( $method = 'post', $action = '' ){
		parent::__construct();
		$this->setAttribute['method'] = $method;
		$this->setAttribute['action'] = $action;
	}
	
	/** Set the action for the form
	 *
	 * @param string $action
	 *
	 * @return Form : current object
	 */
	public function setAction( $action ){
		$this->setAttribute['action'] = $action;
		return $this;
	}
	
	/** Enable file uploading for this form
	 *
	 * @return Form : current object
	 */
	public function enableUpload(){
		$this->setAttribute['enctype'] = 'multipart/form-data';
		return $this;
	}
	
	/** Create the form
	 *
	 * @return string : open form tag
	 */
	public function create(){
		$attr = ' ';
		$attributes = $this->getAttributes();
		if ( !isset( $attributes['method'] ) || !isset( $attributes['action'] ) )
			throw new InvalidArgumentTypeException( 'Form must have method and action attributes set.' );
		foreach ( $attributes as $key => $val )
			$attr .= $key . '="' . $val . '" ';
		return '<form ' . $attr . '>'; 
	}
	
	/** Close the form
	 *
	 * @return string : form closure
	 */
	public function end(){
		return '</form>';
	}
	
	/** Create an input tag by specifying it's type
	 * 
	 * @param string $type
	 * 
	 * @return Tag
	 */
	public function inputTag( $type ){
		$tag = new Tag('input');
		$tag->setAttribute( 'type', $type );
		return $tag;
	}
	
	/** Create input tag by type, name and value
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $value : [optional]
	 *
	 * @return Tag
	 */
	public function input( $type, $name, $value = null ){
		$tag = $this->inputTag( $type )->setAttribute('name', $name );
		if ( $value !== null )
			$tag->setAttribute( 'value', $value );
		return $tag;
	}
	
	/** Create a set of input tags
	 * 
	 * @param string $type
	 * @param string $name
	 * @param array $values : in case values is a string, the standard input
	 * 	method will be called
	 * 
	 * @return array : array of Tag types
	 */
	public function inputSet( $type, $name, $values ){
		if ( !is_array( $values ) ) return $this->input( $type, $name, $values );
		$tags = array();
		foreach( $values as $value ){
			$tags[] = $this->inputTag( $type )->setAttribute( array(
				'name' => $name,
				'value' => $value 
			) );
		}
		return $tags;
	}
	
	/** Create a textarea tag
	 * 
	 * @param string $name
	 * @param string $content : [optional]
	 * @param array $content : [optional] array of Tags
	 * 
	 * @return Tag
	 */
	public function textarea( $name, $content = null ){
		$tag = new Tag('textarea');
		$tag->setAttribute( 'name', $name );
		if ( $content !== null )
			$tag->append( $content );
		return $tag;
	}
	
	/** Create a select Tag
	 * 
	 * @param string $name
	 * @param array $options   : [optional] list of options for the selection. Each key of the array
	 * 	is the value given to the option and each value will be the content for the option tag.
	 * @param string $selected : set one of the options to be selected by default, The $selected value
	 * 	has to be equal to the key for the option
	 * 
	 * @return Tag
	 */
	public function select( $name, $options = array(), $selected = '' ){
		$tag = new Tag('select');
		if ( count( $options ) ){
			foreach( $options as $value => $content ){
				if ( $selected !== '' && $value === $selected ) $value = $value . '" selected="selected"'; 
				$tag->append( '<option value="' . $value . '">' . $content . '</option>' );
			}
		}
		return $tag;
	}
	
	/** Create a label for an input field
	 * 
	 * @param string $content  : label content
	 * @param mixed $field     : the field this label is for, can be either a given tag, or a string representing
	 * 	the id of the field. If the field is a Tag object, then a default id will be generated containing the 
	 * 'field-' string and it's name.
	 * @param string $customid : [optional] If this value is present, then a custom id will be generated for the
	 * $field Tag
	 * 
	 * @return Tag
	 */
	public function label( $content, $field, $customid = null ){
		$label = new Tag( 'label' );
		$label->append( $content );
		if ( is_object( $field ) )
			if ( $field instanceof Tag ){
				if ( $customid === null )
					$customid = 'field-' + $field->getAttribute('name');
				$field->setAttribute( 'id', $customid );
				$label->setAttribute( 'for', $customid );
			} else throw new InvalidArgumentTypeException( "Expecting field object type to be Tag" );
		else {
			$label->setAttribute( 'for', $field );
		}
		return $label;
	}
	
	/** Create a submit button
	 * 
	 * @param string $value     : [optional]
	 * @param array $attributes : custom attributes for this submit button
	 * 
	 * @return Tag
	 */
	public function submit( $value = 'Submit', $attributes = array() ){
		$tag = $this->inputTag( 'submit' );
		$tag->setAttribute( 'value', $value );
		return $tag;
	}
	
}