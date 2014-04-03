
PAYMENT MODULE : ZAAKPAY
---------------------------
Allows you to use Zaakpay payment gateway with CSCart.

INSTALLATION PROCEDURE
--------------------------

Ensure you have a running version of cscart installed. This module was developed under CSCart 3.0.2
-	Execute the following query in your backend (database)

1.  REPLACE INTO `cscart_payment_processors` (`processor_id`, `processor`, `processor_script`, 
`processor_template`, `admin_template`, `callback`, `type`) VALUES (1000, 'Zaakpay', 'zaakpay.php',
'cc_outside.tpl', 'zaakpay.tpl', 'N', 'P');

2.	INSERT INTO `cscart_payments` (`payment_id`, `usergroup_ids`, `position`, `status`, `template`,
`processor_id`, `params`, `a_surcharge`, `p_surcharge`, `localization`) VALUES (13, '0', 0, 'A',
'cc_outside.tpl', 1000, 'a:5:{s:7:"merchant_id";s:4:" "; s:9:"secret_key";s:3:" "; s:8:"mode";s:1:"test";
s:5:"ip_address";s:2:" "; s:4:"order_prefix";s:4:" "; s:6:"log_params";s:8:" ";}', '0.000', '0.000', '');

3.	INSERT INTO `cscart_payment_descriptions` (`payment_id`, `payment`, `description`, `instructions`, `lang_code`) 
VALUES (13, 'Zaakpay', 'Simplifying Payments', ' ',  'EN');

-	Extract the downloaded zip file , there are two files called "zaakpay.php" and "zaakpay.tpl" and one
checksum file included with this package ,
-	Copy the files present in the folder "payments" and paste it to (root_dir)\payments\,
-	Copy the file present in the folder "skins" and paste it to (root_dir)C:\xampp\htdocs\cscart\skins\basic
\admin\views\payments\components\cc_processors\.

CONFIGURATION
-----------------
CSCart Settings

-	Login to the administrator area of cscart,
-	Choose Payment Methods under Administration tab , you can see Zaakpay under the Payment method if the module gets insatalled properly, 
-	Click Edit and configure the following 

- Zaakpay Merchant ID: The Merchant Id provided by Zaakpay.

- Zaakpay Secret Key: Please note that get this key ,login to your Zaakpay merchant account 
and visit the "URL and Key's" section at the "Integration" tab and generate a Key.

- Transaction Mode: The mode you want to make transaction.  1.Test(Sandbox)	2.Live.

- Ip Address : The Ip Address which you want to be there.

and choose yes if you want to log the parameters which are posting to Zaakpay.(you can see the logs in the php error log file)

-	Click update/save .


Now you can make your payment securely through Zaakpay by selecting Zaakpay as the Payment Method at the Checkout stage.

