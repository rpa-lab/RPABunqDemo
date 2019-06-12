Robotic Process Automation search discounting at webshop and with Bunq API payment

We all know that there is RPA tool for repetitive tasks and frees up time, and Bunq has excellent API and easy usage SDK for money payment(I like bunq API, bunq offers easy SDK), then there may be opportunities for some creative ideas to combine automation tools with bank payment interfaces, for personal usage or for business automation.

Here I just want to give a simple demo tutorial for using RPA and bunq API.
From the following pictures and comments, you can see the demo includes:
1)Use RPA to OCR text(keywords) of discounting information from Kruidvat weekly brochure.
2)RPA search the keywords at Kruidvat webshop, then get the ideal payment QRcode image snapshot.
3)Use Bunq API to post ideal QR code string and confirm to payment.

I use UiPath Studio(RPA tool free charge), you can check the UiPath step by step installation on RPA in 15 minutes topic
https://www.cfb-bots.com/single-post/2018/01/19/How-to-Get-Started-in-Robotic-Process-Automation-RPA-in-Only-15-Minutes

I assume that you have installed PHP, composer at your computer(Windows OS),
and you have setup Bunq API.
Then you can also download my demo at https://github.com/rpa-lab/RPABunqDemo
In my demo, I use PHP QR decode(ZXing library), and Bunq PHP SDK.

How to install:
Clone from Github, 
1)Move images folder to your computer desktop.
2)Move bunq folder to c:\
3)At dos command line, type
c:\
cd c:\bunq
composer install

then it will install bunq packages and QR code decoder 

4)Use UiPath Studio open OCRImagestoFindProduct\project.json, and run

How to write PHP code:
1)QR code decoder
Check composer.json,  I use ZXING library (khanamiryan/qrcode-detector-decoder)
And you can easily to get QR code string by using:
$qrcode = new QrReader($imgfile);
    $text = $qrcode->text(); //return decoded text from QR Code

2)Post ideal QR code string using Bunq API
Check tinker\scanqr.php, Bunq PHP SDK offers TokenQrRequestIdeal class,
// Create a new Ideal QR REQUEST object.
		$objectX= TokenQrRequestIdeal::create($get_sub[1]);
		$idealOBJ = $objectX->getValue();

3)Accept ideal payment request
// Update ideal request to ACCEPTED
$amountAgreed = new Amount($idealOBJ->getAmountInquired()->getValue(),$idealOBJ->getAmountInquired()->getCurrency());
$request_updated = RequestResponse::update($idealOBJ->getId(), $monetaryAccountId,$amountAgreed, 'ACCEPTED'); 
