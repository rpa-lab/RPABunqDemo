#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Zxing\QrReader;

use bunq\tinker\BunqLib;
use bunq\tinker\SharedLib;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Model\Generated\Endpoint\TokenQrRequestIdeal;
use bunq\Model\Generated\Endpoint\BunqResponseTokenQrRequestIdeal;
use bunq\Model\Generated\Endpoint\RequestResponse;
use bunq\Model\Generated\Object\Amount;
use bunq\Exception\BadRequestException;
use bunq\Exception\NotFoundException;
use bunq\Exception\TooManyRequestsException;


$imgfile = 'c:/bunq/' . "Untitled.png";
$text = "";
if (file_exists($imgfile)) {
	$qrcode = new QrReader($imgfile);
    $text = $qrcode->text(); //return decoded text from QR Code
    echo $text . PHP_EOL;
}

if (!empty($text)) {
	
	$allOption = getopt('', SharedLib::ALL_OPTION_KEY);
	$environment = SharedLib::determineEnvironmentType($allOption);

	$bunq = new BunqLib($environment);

/**
 * Get current user and print it.
 */
	$user = $bunq->getCurrentUser();
	SharedLib::printUser($user);

/**
 * Get the bank accounts of the current user.
 */
	$monetaryAccounts = $bunq->getAllActiveBankAccount(100);  //to maximum
	$monetaryAccountId = $monetaryAccounts[0]->getId();  // this is default payment monetaryAccountId 

/**
 * Post ideal QRcode of the current user and print it.
 */
	try {			
		// Create a TOKEN QR REQUEST IDEAL map.
		$get_sub = explode(':',$text,2); 
		// Create a new Ideal QR REQUEST object.
		$objectX= TokenQrRequestIdeal::create($get_sub[1]);
		$idealOBJ = $objectX->getValue();			
	}catch (NotFoundException  $ex) {
		  die("Error code: 404" . $ex->getMessage());
		} catch (BadRequestException $ex) {
		  die("Error code: 400" . $ex->getMessage());
		  }catch (TooManyRequestsException  $ex) {
		  die("Error code: 429" . $ex->getMessage());
		}
		
	if (!empty($idealOBJ)) {
	  if (($idealOBJ->getStatus() == 'PENDING') && ($idealOBJ->getType()=="IDEAL")) {
		  echo "Have created ideal request, ideal number is: " . $idealOBJ->getId() . PHP_EOL;
		  
		  // Update ideal request to ACCEPTED
		  $amountAgreed = new Amount($idealOBJ->getAmountInquired()->getValue(),$idealOBJ->getAmountInquired()->getCurrency());
		  try {
				$request_updated = RequestResponse::update($idealOBJ->getId(), $monetaryAccountId,$amountAgreed, 'ACCEPTED'); 
			  }catch (TooManyRequestsException  $ex) {				
				die("Error code: 429" . $ex->getMessage());
				} catch (BadRequestException $ex) {
				die("Error code: 400" . $ex->getMessage());
				}catch (NotFoundException  $ex) {
				die("Error code: 404" . $ex->getMessage());
				}
				
		 echo "Successfully pay: " . $idealOBJ->getAmountInquired()->getCurrency() . " " . $idealOBJ->getAmountInquired()->getValue() . PHP_EOL;
	  }
  }


	// Save the API context to account for all the changes that might have occurred to it during the example execution
	$bunq->updateContext();	
}


		

