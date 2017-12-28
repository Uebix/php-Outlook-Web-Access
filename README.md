# php-Outlook-Web-Access #

Licence = OSL 3.0
http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)

## Example: ##

#### 1) Set parameters in exchange_config.php ####
#### 2) Create and run a PHP file with this code: ####

```php
<?php
  require_once dirname(__FILE__)."/exchange.php";
  $exch = new Exchange(_EXCHSERVER_, _EXCHMAIL_, _EXCHUSER_, _EXCHPASSWORD_);
  $exch->email->getMails(10);
  foreach ($exch->email->emails as $email)
  {
    print_r($email);
  }
```

Outlook PHP Class

contact me at : info [at] uebix.com
