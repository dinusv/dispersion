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
 * @brief Provides a paypal interface wrapper
 * 
 * Usage
 * 
 * @code
 * // Paypal form usage
 * 
 * $paypal = new PayPal();
 * $paypal->createForm( 'mybusiness', 'itemnumber33', 'USD', 20 );
 * // if you want a recurring payment type
 * $paypal->recurringPaymentType('M');
 * $paypal->redirectAfter( 0 );
 * $paypal->formIpn('myurl');
 * $paypal->formOutput();
 * @endcode
 * 
 * 
 * @code
 * // Paypal ipn usage
 * 
 * $paypal = new PayPal();
 * try {
 * 	if ( $paypal->validIpn() ){
 * 		//get id of the item the payment was made for
 * 		$id = $paypal->ipnPostedData( 'item_number' );
 * 	} else 
 * 		//payment not successful
 * } catch( Exception $e ){
 * 	// Invalid IPN
 * }
 * @endcode
 */
class PayPal{
	
	/** 
	 * @var SANDBOX
	 * string : Paypal sandbox post link
	 */
	const SANDBOX = 'https://sandbox.paypal.com/cgi-bin/webscr';
	
	/** 
	 * @var ACTIVE
	 * string : Paypal active post link
	 */
	const ACTIVE  = 'https://www.paypal.com/cgi-bin/websrc';
	
	/** 
	 * @var PPURL
	 * string : Paypal url
	 */
	const PPURL   = 'www.paypal.com';
	
	/** 
	 * @var PPSBURL
	 * string : Paypal developer url
	 */
	const PPSBURL = 'sandbox.paypal.com';
		
	private
	
	/* 
	 * Paypal Form Fields
	 * ----------------------------------------- */
		
		/** 
		 * @var $form_fields
		 * array : Fields that will be posted to paypal
		 */
		$form_fields       = array(),
		
		/** 
		 * @var $form_submit_image
		 * string : Custom submit button
		 */
		$form_submit_image = null,
		
		/** 
		 * @var $form_action
		 * string : Paypal form action, can be either the paypal sandbox url or the original one
		 */
		$form_action       = '',
		
		/** 
		 * @var $form_redirect
		 * string : Submit the form using javascript after a set number of seconds. Set this to 0 to
		 * disable autosubmitting
		 */
		$form_redirect     = 10,
		
	/* 
	 * Paypal Ipn Fields
	 * ----------------------------------------- */
	
		/** 
		 * @var $ipn_posted_data
		 * string : The posted data received by the ipn request
		 */
		$ipn_posted_data,
		
		/** 
		 * @var $ipn_payment_data
		 * string : Payment data processed from the posted data received by ipn
		 */
		$ipn_payment_data  = array();
	
	/** Construct
	 */
	public function PayPal(){
		$this->form_fields['cmd'] = '_xclick';
	}
	
	/* 
	 * Paypal Form Methods
	 * ----------------------------------------- */
	
	/** Creates a form based on the required fields
	 * 
	 * @param string $business      : email adress for the paypal account
	 * @param string $item_name     : name of the item or shopping cart( must be 127 characters max )
	 * @param string $currency_code : defines the currency in which the monetary variables are denoted
	 * @param string $price         : price of the item or the total price of all items in the shopping cart
	 * @param Tag $submit            : [optional] the button for the buyer to press in order to initiate the 
	 * 	process. The default will be the paypal button 'x-click-but01.gif' from their website.
	 * @param string $action        : [optional] the location the form will be submited to. The default value
	 * 	is https://www.paypal.com/cgi-bin/websrc
	 * 
	 * @return PayPal : current object
	 */
	public function createForm( $business, $item_name, $currency_code, $price, $submit = null, $action = null ){
		$this->headersNoCache();
		if ( $submit === null ) 
			$this->form_submit_image = new Tag( 'input',array(
				'type' => 'submit',
				'value' => 'Buy now'
			)); 
		else 
			$this->form_submit_image = $submit;
		if ( $action === null ) 
			$this->form_action = self::ACTIVE;
		else $this->form_action = $action;
		$this->formSet( array(
			'business'      => $business,
			'item_name'     => $item_name,
			'currency_code' => $currency_code,
			'amount'        => $price,
		));
		return $this;
	}
	
	/** Set fields for the form
	 * 
	 * @param string $fields : field name
	 * @param array $fields  : array of field names and values to set
	 * @param string $value  : [optional]field value
	 * 
	 * @return PayPal : current object
	 */
	public function formSet( $fields, $value = '' ){
		if ( !is_array( $fields ) ){
			$this->form_fields[$fields] = $value;
		} else {
			$this->form_fields = array_merge( $this->form_fields, $fields );
		}
		return $this;
	}
	
	/** Set form action, PayPal::SANDBOX, PayPal::ACTIVE can be used
	 * 
	 * @param string $action
	 * 
	 * @return PayPal : current object
	 */
	public function formAction( $action ){
		$this->form_action = $action;
		return $this;
	}
	
	/** Set the payment type : once a day, a month, a year or just one payment
	 * 
	 * @param string $type   : once / day / month / year
	 * @param integer $every : [optional] enable recurring payment for every number of years/months/days
	 */
	public function recurringPaymentType( $type, $every = 1 ){
		$this->formSet( 'src', '1' );
		$this->formSet( 'sra', '1' );
		$this->formSet( 'cmd', '_xclick-subscriptions' );
		if ( isset( $this->form_fields['amount'] ) ){
			$price = $this->form_fields['amount'];
			unset( $this->form_fields['amount'] );
		} else $price = 0;
		$this->formSet( 'a3', $price );
		$this->formSet( 'no_note', 1 );
		$this->formSet( 'no_shipping', 1 );
		$type = strtoupper( $type[0] );
		if ( $type === 'Y' || $type === 'M' || $type !== 'D' ){
			$this->formSet( 't3', $type );
			$this->formSet( 'p3', $every );
		} else throw new InvalidArgumentTypeException("Type must be day/month/year/once. Unexpected value");
		return $this;
	}
	
	/** Redirect the form to paypal after a set number of seconds
	 * 
	 * @param integer $seconds
	 * 
	 * @return PayPal : current object
	 */
	public function redirectAfter( $seconds ){
		$this->form_redirect = $seconds;
		return $this;
	}
	
	/** Set paypals instant payment notification
	 * 
	 * @param string $ipn_url
	 * 
	 * @return PayPal : current object
	 */
	public function formIpn( $ipn_url ){
		$this->formSet( 'notify_url', $ipn_url );
		return $this;
	}
	
	/** Set the paypal form to the extended version in order to support more arguments
	 * 
	 * @return PayPal : current object
	 */
	public function formExtend(){
		$this->formSet( 'redirect_cmd', $this->form_fields['cmd'] );
		$this->formSet( 'cmd', '_ext-enter' );
		return $this;
	}
	
	/** Generate the form and output it
	 */
	public function formOutput(){
		$form = new Form('post', $this->form_action );
		/* Set form name */
		if ( $this->form_redirect > 0 && !$form->hasAttribute('name') )
			$form->setAttribute( 'name', 'paypalsubmitform' );;
		/* Form */
		echo $form->create();
		foreach( $this->form_fields as $fieldname => $fieldvalue ){
			echo $form->input( 'hidden', $fieldname, $fieldvalue );
		}
		echo $this->form_submit_image;
		echo $form->end();
		if ( $this->form_redirect > 0 ){
			echo '<script>' . 
				'setTimeout("pp_submit()", ' . $this->form_redirect . ' * 100);' .
				'function pp_submit(){ document.' . $form>getAttribute('name') . '.submit(); }' .
				'</script>';
		}	
	}
	
	/** Function call recomended in order to disable caching before outputing the form
	 */
	public function headersNoCache(){
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header('Pragma: no-cache');
	}
	
	/* 
	 * Paypal Ipn Methods
	 * ----------------------------------------- */
		
	/** Get ipn payment data after the ipn has been processed
	 * 
	 * @param string $key : [optional]set to null in order to return all the values as an array
	 * 
	 * @throws IndexOutOfBoundsException
	 * 
	 * @return array  : returns an empty array if the fields haven't been set
	 * @return string : returns the value if a key is given
	 */
	public function ipnPaymentData( $key = null ){
		if ( $key === null )
			return $this->ipn_payment_data;
		else {
			if ( !isset( $this->ipn_payment_data[$key] ) )
				throw new IndexOutOfBoundsException( 'Cannot find index ' . $key . ' for IpnPaymentData' );
			return $this->ipn_payment_data[$key];
		}
	}
	
	/** Get posted data from paypal after the ipn has been processed
	 * 
	 * @param string $key : [optional]set to null in order to return all the values as an array
	 * 
	 * @throws IndexOutOfBoundsException
	 * 
	 * @return array  : returns an empty array if the fields haven't been set
	 * @return string : returns the value if a key is given
	 */
	public function ipnPostedData( $key = null ){
		if ( $key = null )
			return $this->ipn_posted_data;
		else {
			if ( !isset( $this->ipn_posted_data[$key] ) )
				throw new IndexOutOfBoundsException( 'Cannot find index ' . $key . ' for IpnPostedData' );
			return $this->ipn_posted_data[$key];
		}
	}
	
	/** Checks is the request is a valid ipn from paypal
	 * 
	 * @param string $paypalurl          : [optional] paypal url to post data to 
	 * 
	 * @throws PageNotFoundException     : in case the paypal connection can't be established
	 * @throws IncompleteActionException : in case the ipn verification failed
	 * 
	 * @return boolean : true if it's a valid Ipn, false otherwise
	 */
	public function validIpn( $paypalurl = self::PPURL ){
		if ( empty($_POST) ) return false;
		$price = 0; $postvars = 0;
		/* Return the data to paypal in order to check for the payment */
		foreach( $_POST as $key => $value ){
			$postvars .= $key . '=' . urlencode($value) . '&';
			$this->ipn_posted_data[$key] = $value;
		}
		$postvars .= 'cmd=_notify-validate';
		$errstr = ''; $errno = '';
		/* Open the connection */
		$fp= @ fsockopen( $paypalurl, 80, $errno, $errstr, 30 );
		if (!$fp) throw new PageNotFoundException( 'fsockopen error no. ' . $errno . ':' .  $errstr );
		/* Send data */
		@ fputs($fp, "POST /cgi-bin/webscr HTTP/1.1\r\n");
		@ fputs($fp, "Host: ".$paypalurl."\r\n");
		@ fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		@ fputs($fp, "Content-length: ".strlen($postvars)."\r\n");
		@ fputs($fp, "Connection: close\r\n\r\n");
		@ fputs($fp, $postvars . "\r\n\r\n");
		/* Get page contents */
		$str = '';
		while( !feof( $fp ) )
			$str .= @ fgets( $fp, 1024 );
		@ fclose($fp);
		/* Check if this payment has been verified */
		if( !preg_match( '/VERIFIED/i', $str ) )
			throw new IncompleteActionException( 'Ipn verification failed' );
		
		/* If everything ok, proceed with parsing the posted data */
		if( preg_match( '/subscr/', $this->ipn_posted_data['txn_type'] ) ) {
			$this->ipn_payment_data['type'] = 'subscription';
			if ( in_array( $this->ipn_posted_data['txn_type'], array('subscr_signup') ) )
				return false;
			if( $this->ipn_posted_data['txn_type'] == 'subscr_payment' ) {
				if( $this->ipn_posted_data['payment_status'] == 'Completed' ) {
					$this->ipn_payment_data['price'] = $this->ipn_posted_data['mc_amount3'];
					return true;
				}
			}
		} else {
			if( $this->ipn_posted_data['payment_status'] == 'Completed' ){
				$this->ipn_payment_data['type']  = 'payment';
				$this->ipn_payment_data['price'] = $this->ipn_posted_data['mc_gross'];
				return true;
			}
		}
		return false;
	}
}
