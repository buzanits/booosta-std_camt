<?php
namespace booosta\std_camt;

use Genkgo\Camt\Config;
use Genkgo\Camt\Reader;
use Genkgo\Camt\Camt053\MessageFormat\V02;

use \booosta\Framework as b;
b::init_module('std_camt');


class Std_camt extends \booosta\base\Module
{
  use moduletrait_std_camt;

  protected $messageFormat = 'default';
  protected $errors = [];


  public function __construct(protected $xmlfile = null) {}
  public function setMessageFormat($format) { $this->messageFormat = $format; }

  public function parseFile($xmlfile = null)
  {
    if($xmlfile === null) $xmlfile = $this->xmlfile;

    $config = Config::getDefault();

    if($this->messageFormat == 'Stuzza'):
      $config->addMessageFormat(new StuzzaMessageFormat(new V02()));
      $config->disableXsdValidation();
    endif;

    $reader = new Reader($config);

    try {
      $message = $reader->readFile($xmlfile);
      $statements = $message->getRecords();
    } catch(\Exception $e) {
      $msg = "ERROR: Could not read XML: " . $e->getMessage();  
      $this->add_error($msg);
      $this->error($msg);

      return [];
    }


    $entries = [];
    foreach ($statements as $statement) $entries[] = $statement->getEntries();

    return $entries;
  }

  public function getTransactions($xmlfile = null)
  {
    $result = [];
    $entries = $this->parseFile($xmlfile);
    #b::debug($this->error());

    foreach($entries as $day):
      foreach($day as $transaction):
        $data = [];

        $data['record_id'] = $record_id = $transaction?->getRecord()?->getId();
        $data['date'] = $transaction?->getBookingDate()?->format('Y-m-d');
        $data['datetime'] = $transaction?->getBookingDate()?->format('Y-m-d H:i:s');
        $data['timezone'] = $transaction?->getBookingDate()?->getTimezone()?->getName();
        $data['amount_cent'] = $transaction?->getAmount()?->getAmount();
        $data['amount'] = intval($transaction?->getAmount()?->getAmount()) / 100;
        $data['currency'] = $transaction?->getAmount()?->getCurrency()?->getCode();
        #b::debug($data);

        $details = $transaction?->getTransactionDetails();
        if(!is_array($details)):
          $this->add_error("No details found in transaction with record id $record_id");
          b::debug("No details ({$data['amount']})");
          continue;
        endif;

        if(sizeof($details) > 1):
          $this->add_error("More than one details found in transaction with record id $record_id");
          b::debug("Multiple details ({$data['amount']})"); b::debug($details);
          continue;
        endif;

        if(sizeof($details) == 0):
          #b::debug($transaction);
          $data['transaction_id'] = $this->getUniqueID($transaction, null);
          $data['message'] = $transaction?->getAdditionalInfo() ?: 'No details provided';
          $data['is_credit'] = floatval($data['amount']) > 0;

          $result[] = $data;
          continue;
        endif;

        $detail = $details[0];
        #$data['transaction_id'] = $detail?->getReference()?->getTransactionId();

        $party0 = $detail?->getRelatedParties()[0];
        $party1 = $detail?->getRelatedParties()[1];

        if(is_a($party0?->getRelatedPartyType(), "Genkgo\\Camt\\DTO\\Creditor")):
          $creditor = $party0;
          $debtor = $party1;
        else:
          $creditor = $party1;
          $debtor = $party0;
        endif;

        $data['creditor_name'] = $creditor?->getRelatedPartyType()?->getName();
        $address = $creditor?->getRelatedPartyType()?->getAddress()?->getAddressLines();
        $data['creditor_address'] = is_array($address) ? implode(', ', $address) : $address;
        $data['creditor_country'] = $creditor?->getRelatedPartyType()?->getAddress()?->getCountry();

        $account = $creditor?->getAccount();
        if(is_callable([$account, 'getIban'])) $data['creditor_iban'] = $account->getIban()?->getIban();

        $data['debtor_name'] = $debtor?->getRelatedPartyType()?->getName();
        $address = $debtor?->getRelatedPartyType()?->getAddress()?->getAddressLines();
        $data['debtor_address'] = is_array($address) ? implode(', ', $address) : $address;
        $data['debtor_country'] = $debtor?->getRelatedPartyType()?->getAddress()?->getCountry();

        $account = $debtor?->getAccount();
        if(is_callable([$account, 'getIban'])) $data['debtor_iban'] = $account->getIban()?->getIban();

        $data['transaction_id'] = $this->getUniqueID($transaction, $detail);
        $data['message'] = $detail?->getRemittanceInformation()?->getMessage();
        $data['is_credit'] = $detail?->getCreditDebitIndicator() == 'CRDT'; 

        $result[] = $data;
      endforeach;
    endforeach;

    return $result;
  }

  protected function getUniqueID($transaction, $detail)
  {
    #b::debug("txid: " . $detail?->getReference()?->getTransactionId());
    #b::debug("srvid: " . $detail?->getReference()?->getAccountServicerTransactionId());
    if($id = $detail?->getReference()?->getTransactionId()) return $id;
    if($id = $transaction?->getAccountServicerReference()) return $id;
    return null;
  }

  protected function add_error($msg) { $this->errors[] = $msg; }
  public function get_errors() { return $this->errors; }
}

