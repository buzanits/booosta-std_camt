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

    $reader = $reader = new Reader($config);
    $message = $reader->readFile($xmlfile);

    $statements = $message->getRecords();
    $entries = [];
    foreach ($statements as $statement) $entries[] = $statement->getEntries();

    return $entries;
  }
}

