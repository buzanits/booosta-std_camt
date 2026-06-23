# CAMT XML reader for the Booosta Framework

This modules provides a reader for the CAMT inter banking format. Thanks to genko whos work this
is based on (https://github.com/genkgo/camt).

Booosta allows to develop PHP web applications quick. It is mainly designed for small web applications.
It does not provide a strict MVC distinction. Although the MVC concepts influence the framework. Templates,
data objects can be seen as the Vs and Ms of MVC.

Up to version 3 Booosta was available at Sourceforge: https://sourceforge.net/projects/booosta/ From version
4 on it resides on Github and is available from Packagist under booosta/booosta .

## Installation

This module can be used inside the Booosta framework. If you want to do so, install the framework first. See the
[installation instructions](https://github.com/buzanits/booosta-installer) for accomplishing this. If your
Booosta is installed, you can install this module.

You also can use this module in your standalone PHP scripts. In both cases you install it with:

```
composer require booosta/booosta-std_camt
```

## Usage in the Booosta framework

In your scripts you use the module:

```
# [...]
$camt = $this->makeInstance('std_camt', $xml_file);
$transactions = $camt->getTransactions();

foreach($transactions as $transaction) {
  print PHP_EOL . 'record_id: ' . $transaction['record_id'];
  print PHP_EOL . 'transaction_id: ' . $transaction['transaction_id'];
  print PHP_EOL . 'date: ' . $transaction['date'];
  print PHP_EOL . 'datetime: ' . $transaction['datetime'];
  print PHP_EOL . 'timezone: ' . $transaction['timezone'];
  print PHP_EOL . 'amount: ' . $transaction['amount'];
  print PHP_EOL . 'amount_cent: ' . $transaction['amount_cent'];
  print PHP_EOL . 'currency: ' . $transaction['currency'];
  print PHP_EOL . 'message: ' . $transaction['message'];
  print PHP_EOL . 'is_credit: ' . $transaction['is_credit'];  // is it a credit (true) or debit (false) booking?
  print PHP_EOL . 'creditor_name: ' . $transaction['creditor_name'];
  print PHP_EOL . 'creditor_address: ' . $transaction['creditor_address'];
  print PHP_EOL . 'creditor_country: ' . $transaction['creditor_country'];
  print PHP_EOL . 'creditor_iban: ' . $transaction['creditor_iban'];
  print PHP_EOL . 'debtor_name: ' . $transaction['debtor_name'];
  print PHP_EOL . 'debtor_address: ' . $transaction['debtor_address'];
  print PHP_EOL . 'debtor_country: ' . $transaction['debtor_country'];
  print PHP_EOL . 'debtor_iban: ' . $transaction['debtor_iban'];
  print PHP_EOL;
}
```

## Usage as Standalone Module

Just replace the first line of the above example with:

```
$camt = new \booosta\std_camt\Std_camt($xml_file);
```

## Use of Stuzza format

Some CAMT files use a special format called "Stuzza". To tell the object to use this format
just add this line right after the instantiation:

```
$camt->setMessageFormat('Stuzza');
```
