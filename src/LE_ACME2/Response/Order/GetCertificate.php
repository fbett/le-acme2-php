<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Response\AbstractResponse;

class GetCertificate extends AbstractResponse {

    protected $_pattern = '~(-----BEGIN\sCERTIFICATE-----[\s\S]+?-----END\sCERTIFICATE-----)~i';


    public function getCertificate() {

        if(preg_match_all($this->_pattern, $this->_raw->body, $matches))  {

            return $matches[0][0];
        }

        throw new \RuntimeException('Preg_match_all has returned false - invalid pattern?');
    }

    public function getIntermediate() {

        if(preg_match_all($this->_pattern, $this->_raw->body, $matches))  {

            $result = '';

            for($i=1; $i<count($matches[0]); $i++)  {

                $result .= "\n" . $matches[0][$i];
            }
            return $result;
        }

        throw new \RuntimeException('Preg_match_all has returned false - invalid pattern?');
    }
}