<?php

class payU {
    
    //private $baseUrl = 'https://staging.payu.co.za';
    private $baseUrl = 'https://secure.payu.co.za';
    private $soapWdslUrl;
    private $payuRppUrl;
    private $safeKey;
    private $username;
    private $password;
    
    public $client;


    public $apiVersion = 'ONE_ZERO';
    
    public function __construct($wsdlUrl, $rppUrl, $version=null) {
        $this->soapWdslUrl = $this->baseUrl.$wsdlUrl;
        $this->payuRppUrl = $this->baseUrl.$rppUrl;
        if(isset($version)) {
            $this->apiVersion = $version;
        }
    }
    
    public function setCred($key, $username, $password) {
        $this->safeKey = $key;
        $this->username = $username;
        $this->password = $password;
    }
    
    private function setHeaderBody() {
        if(!$this->username || !$this->password) {
            return false;
        }
        $header = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
        $header .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
        $header .= '<wsse:Username>'.$this->username.'</wsse:Username>';
        $header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->password.'</wsse:Password>';
        $header .= '</wsse:UsernameToken>';
        $header .= '</wsse:Security>';
        $headerBody = new SoapVar($header, XSD_ANYXML, null, null, null);
        return $headerBody;
    }
    
    private function setClient() {
        $soap_client = new SoapClient($this->soapWdslUrl, array("trace" => 1, "exception" => 0)); 
        $headerBody = $this->setHeaderBody();        
        $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS.
        $header = new SOAPHeader($ns, 'Security', $headerBody, true);
        $soap_client->__setSoapHeaders($header);
        return $soap_client;
    }
    
    public function init() {
        if(!is_object($this->client = $this->setClient())) {
            die('could not instantiate SOAP client');
        }
    }
    
    public function setTransaction($options=array()) {
        if(empty($options)) { die('could not set transaction without options'); }
        $config['Api'] = $this->apiVersion;
        $config['Safekey'] = $this->safeKey;
        $config['TransactionType'] = $options['TransactionType'];
        $config['AdditionalInformation'] = $options['AdditionalInformation'];
        $config['Customer'] = $options['Customer'];
        $config['Basket'] = $options['Basket'];
        $client = $this->client;
        try {
            $response = $client->setTransaction($config);
            $result =  $response->return;
            if(($result->successful) && isset($result->payUReference)) {
                $redirectUrl = $this->payuRppUrl . $result->payUReference;
                return $redirectUrl;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return $this->showError($ex);
        }
    }
    
    
    public function getTransaction($options=array()) {
        if(empty($options)) { die('could not get transaction without options'); }
        $config['Api'] = $this->apiVersion;
        $config['Safekey'] = $this->safeKey;
        $config['AdditionalInformation'] = $options['AdditionalInformation'];
        $client = $this->client;
        try {
            $response = $client->getTransaction($config);
            $result =  $response->return;
            if($result->successful) {
                return $result;
            } else {
                return false;
            }

        } catch (Exception $ex) { 
            return $ex;
            //return $this->showError($ex);
        }
    }

    private function showError($e) {
        $message = $e->getMessage();
        $respone = array('status'=> 'error', 'message' => $message);
        return $respone;
    }
    
    public function getLastRequest() {
       return $this->client->__getLastRequest();
    }
    
    public function getLastResponse() {
        return $this->client->__getLastResponse();
    }
}

?>

