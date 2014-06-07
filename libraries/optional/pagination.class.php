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
 * @ingroup libraries
 * @brief Provides automatic pagination generation. Modifies sql query to limit items.
 */
class Pagination extends Dispersion{
	
	private
	
	/* 
	 * Pagination links fields
	 * ----------------------------------------- */
	 
	 	/** 
		 * @var $pagination_links_per_page
		 * int : Total allowed links per page
		 */
		$pagination_links_per_page   = 10,
		
	 	/** 
		 * @var $pagination_link_attr
		 * array : Attributes for the pagination link
		 */
		$pagination_link_attr        = array(),
		
	 	/** 
		 * @var $pagination_link_attr_active
		 * int : Attributes for the active pagintion link
		 */
		$pagination_link_attr_active = array(),
		
	 	/**
		 * @var $pagination_link_href
		 * int : Path to the current page, without the counter
		 */
		$pagination_link_href        = '',
	
	/* 
	 * Select options
	 * ----------------------------------------- */
	 	
	 	/** 
		 * @var $current_page
		 * int : Page counting starts from 1
		 */
		$current_page = 1,
		
		/** 
		 * @var $items_per_page
		 * int : Maximum number of items per page
		 */
		$items_per_page = 20,
		
		/** 
		 * @var $total_items
		 * int : Total number of items
		 */
		$total_items = null,
		
		/** 
		 * @var $select_query
		 * mixed : Selection query, can be either options, either string
		 */
		$select_query = null;
	 
	 public
		/** 
		 * @var $total_pages
		 * int : Stores the number of pages the query has
		 */
		$total_pages = null;
	
	/** Constructor
	 * 
	 * @param integer $current_page : the current page index, > 1
	 * @param string  $page_path    : the page url
	 */
	public function Pagination( $current_page = 1, $page_path = null ){
		parent::__construct();
		$this->setCurrentPage( $current_page );
		if ( $page_path !== null )
			$this->setPagePath( $page_path );
	}
	
	/* 
	 * Query options
	 * ----------------------------------------- */
	 
	/** Set the query to be used in order to determining the page items and adding the selection
	 * 
	 * @param mixed $select_query : the query to be used, can be either string or array of options
	 * @see Core/Model/select
	 * 
	 * @return Pagination : current object
	 */
	public function useQuery( $select_query ){
		$this->select_query = $select_query;
		return $this;
	}
	 
	/** Change the number of items to be displayed on a page
	 * 
	 * @param integer $items
	 * 
	 * @return Pagination : current object
	 */
	public function itemsPerPage( $items ){
		$this->items_per_page = $items;
		return $this;
	}
	
	/* 
	 * Link options
	 * ----------------------------------------- */
	
	/** Set the max number of pagination links per page
	 * 
	 * @param integer $nr
	 * 
	 * @return Pagination : current object
	 */
	public function linksPerPage( $nr ){
		$this->pagination_links_per_page = $nr;
		return $this;
	}
	
	/** Set link attributes
	 * 
	 * @param array $attributes : keys => attribute name, values => attribute value
	 * 
	 * @throws InvalidArgumentTypeException
	 * 
	 * @return Pagination       : current object
	 */
	public function linkAttributes( $attributes ){
		if ( is_array($attributes ) ){
			$this->pagination_link_attr = $attributes;
		} else throw new InvalidArgumentTypeException(array( 'array' ));
		return $this;
	}
	
	/** Set attributes to the active link
	 * 
	 * @param array $attributes : keys => attribute name, values => attribute value
	 * 
	 * @throws InvalidArgumentTypeException
	 * 
	 * @return Pagination       : current object
	 */
	public function activeLinkAttributes( $attributes ){
		if ( is_array($attributes ) ){
			$this->pagination_link_attr_active = $attributes;
		} else throw new InvalidArgumentTypeException(array( 'array' ));
		return $this;
	}
	
	/** Sets the href the links will have
	 * 
	 * @param string $page_path
	 * 
	 * @return Pagination          : current object
	 */
	public function setPagePath( $page_path ){
		if ( is_object($page_path) )
			$page_path = $page_path->__toString();
		if ( $page_path[strlen($page_path) - 1] !== '/' )
			$this->pagination_link_href = $page_path . '/';
		else $this->pagination_link_href = $page_path;
		return $this;
	}
	
	/** Gets the current page
	 * 
	 * @return integer
	 */
	public function getCurrentPage(){
		return $this->current_page;
	}
	
	/** Sets the current page
	 * 
	 * @param integer $current_page
	 * 
	 * @return Pagination          : current object
	 */
	public function setCurrentPage( $current_page ){
		$this->current_page = $current_page;
		return $this;
	}
	
	/** Set the number of items
	 * 
	 * @param integer $items
	 * 
	 * @return Pagination          : current object
	 */
	public function setTotalItems( $items ){
		$this->total_items = $items;
		return $this;
	}
	
	/* 
	 * Create the pagination links
	 * ----------------------------------------- */
	
	/** Get the anchor link attributes
	 * 
	 * @param boolean $active : set to true if the link is active
	 * 
	 * @return array          : the pagination link attributes
	 */
	protected function getAnchorAttr( $active = false ){
		if ( !$active )
			return $this->pagination_link_attr;
		else return $this->pagination_link_attr_active;
	}
	
	/** Returns the anchor link as text form. This function is intended to be easily 
	 * 
	 * @param integer $i      : the page index
	 * @param boolean $active : true if this is the current page
	 * 
	 * @return string         : the page link as html form
	 */
	protected function anchorLink( $i, $active = false ){
		/* get attributes */
		$attr_array = $this->getAnchorAttr( $active );
		$attr = " ";
		if ( count( $attr_array ) )
			foreach( $this->pagination_link_attr as $key => $value )
				$attr .= $key . "=\"" . $value . "\" ";
		if ( $active ) 
			$type = 'span';
		else $type = 'a';
		/* generate output */
		return "<" . $type . " href=\"" . $this->pagination_link_href . $i . "\"" . $attr . ">" . $i . "</" . $type . ">";
	}
	
	/** Prints pagination links on the left side of the active link
	 * 
	 * @param integer $links_on_side : number of links on one side
	 */
	private function leftPaginationLinks( $links_on_side ){
		if ( $links_on_side < $this->current_page ){
			for ( $i = $this->current_page - $links_on_side; $i < $this->current_page; $i++ )
				echo $this->anchorLink( $i );
		} else {
			for ( $i = 1; $i < $this->current_page; $i++ )
				echo $this->anchorLink( $i );
		}
		
	}
	
	/** Prints pagination links on the right side of the active link
	 * 
	 * @param integer $links_on_side : number of links on one side
	 */
	private function rightPaginationLinks( $links_on_side ){
		if ( $this->current_page + $links_on_side > $this->total_pages ){
			if ( $this->current_page < $this->total_pages ){
				for ( $i = $this->current_page + 1; $i <= $this->total_pages; $i++ )
					echo $this->anchorLink( $i );
			}
		} else {
			for ( $i = $this->current_page + 1; $i <= $this->current_page + $links_on_side; $i++ )
				if ( $i <= $this->total_pages )
					echo $this->anchorLink( $i );
		}
	}
	
	/** Prints the active page link
	 */
	private function activePageLink(){
		if ( $this->total_pages >= 1 ) echo $this->anchorLink( $this->current_page, true );
	}
	
	/** 
	 * @return total pages either set or received by query
	 */
	public function getTotalPages(){
		if ( $this->total_pages === null ){
			if ( $this->total_items === null ) 
				$this->total_items = $this->countItems();
			/* We don't want to divide by zero */
			if ( $this->items_per_page === 0 ) 
				return null;
			/* Get total pages */
			$this->total_pages = ceil( $this->total_items / $this->items_per_page );
		}
		return $this->total_pages;
	}
	
	/** Prints the pagination links usign the helper functions above
	 */
	public function paginationLinks(){
		/* Check for the page_path to be set */
		if ( $this->pagination_link_href === null ) return '';
		/* Get total pages */
		if ( $this->getTotalPages() === null )
			return null;
		/* Check for an invalid page */
		if ( $this->current_page > $this->total_pages ) return null;
		/* First page starts from 1 */
		$links_on_side = floor( $this->pagination_links_per_page / 2 );
		/* Add more links to one side if the other one has less than required */
		if ( $this->current_page < 1 + $links_on_side ) 
			$links_on_side += ( 1 + $links_on_side ) - $this->current_page;
		if ( $this->current_page > $this->total_pages - $links_on_side ) 
			$links_on_side += $links_on_side - ( $this->total_pages - $this->current_page );
		/* Generate links for the left and right side */
		$this->leftPaginationLinks( $links_on_side );
		$this->activePageLink();
		$this->rightPaginationLinks( $links_on_side );
	}
	
	/* 
	 * Result fetcher
	 * ----------------------------------------- */
	 
	/** Returns the number of rows in the query
	 * 
	 * @return integer : number of rows
	 */
	private function countItems(){
		if ( $this->select_query === null ) throw new InvalidArgumentTypeException( 'The query has not been specified' );
		if ( is_array( $this->select_query ) ) {
			if ( isset($this->select_query['nr_items'] ) )        unset( $this->options['nr_items'] );
			if ( isset($this->select_query['start_from_items']) ) unset( $this->options['start_from_items'] );
			return $this->model->countRows( $this->select_query );
		} else
			return $this->model->countRows( $this->select_query );
	}
	
	/** Fetches the items, executing the query
	 * 
	 * @throws IndexOutOfBoundsException
	 * 
	 * @return resource
	 */
	public function getItems(){
		/* Check to see if the current page is set */
		if ( $this->current_page <= 0 ) throw new IndexOutOfBoundsException( 'Cannot get current page index' );
		/* Get total items if they have not been set */
		if ( $this->total_items === null )
			$this->total_items = $this->countItems();
		/* Get results for the current page */
		$select_from = ( $this->current_page - 1 ) * $this->items_per_page;
		
		if ( $this->select_query === null ) throw new InvalidArgumentTypeException( 'The query has not been specified' );
		if ( is_array( $this->select_query ) ) {
			$this->select_query['nr_items'] = $this->items_per_page;
			$this->select_query['start_from_items'] = $select_from;
			return $this->model->selectRows( $this->select_query );
		} else
			return $this->model->select( $this->select_query . " LIMIT " . $select_from . ", " . $this->items_per_page );
	}
	
}