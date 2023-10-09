<?php
namespace booosta\Std_camt;

use Genkgo\Camt\MessageFormatInterface;
use Genkgo\Camt\DecoderInterface;
use Genkgo\Camt\Camt053\MessageFormat\V02;


class StuzzaMessageFormat implements MessageFormatInterface 
{
  public function __construct(protected V02 $camt053 = null) {}

  public function getXmlNs() : string { return 'ISO:camt.053.001.02:APC:STUZZA:payments:003'; }
  public function getMsgId() : string { return 'ISO.camt.053.001.02.austrian.003'; }
  public function getName() : string  { return 'Stuzza'; }
  public function getDecoder() : DecoderInterface { return $this->camt053->getDecoder(); }
}

