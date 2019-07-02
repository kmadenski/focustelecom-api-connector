<?php
/**
 * Created by PhpStorm.
 * User: kmadenski
 * Date: 01.07.19
 * Time: 12:59
 */

namespace FocusConnector\Core;

use FocusConnector\Exception\ConnectionException;
use FocusConnector\Exception\HashFunctionNotDeclaredException;

class Connector
{
    CONST MD5 = 'md5';
    private $loginAdmin;
    private $consultantAdmin;

    private $apiKey;
    private $domain;
    private $change;
    private $method = self::MD5;

    private $baseUrl;

    /**
     * Connector constructor.
     * @param string $loginAdmin
     * @param string $apiKey
     * @param string $domain
     * @param string $change
     * @param string $baseUrl
     */
    public function __construct(string $loginAdmin, string $apiKey, string $domain, string $change, string $baseUrl)
    {
        $this->loginAdmin = $loginAdmin;
        $this->apiKey = $apiKey;
        $this->domain = $domain;
        $this->change = $change;
        $this->baseUrl = $baseUrl;
    }

    public function campaignList(): string {
        $post = [];
        return $this->adminRequest("fcc-campaigns-list", $post);
    }
    public function classifiersList($campaignId): string {
        $post = [
            "campaigns_id" => $campaignId
        ];
        return $this->adminRequest("fcc-classifiers-list", $post);
    }
    public function staffAgentList(): string {
        $post = [];
        return $this->adminRequest("fcc-staff-agents-list", $post);
    }
    public function addRecords($campaignId, array $records): string {
        $post = [
            "campaigns_id" => $campaignId,
            "records" => $records
        ];
        return $this->adminRequest("fcc-add-records", $post);
    }
    public function updateRecords($campaignId, array $records): string {
        $post = [
            "campaigns_id" => $campaignId,
            "records" => $records
        ];
        return $this->adminRequest("fcc-update-records", $post);
    }
    public function exportRecords($campaignId): string {
        $post = [
            "campaigns_id" => $campaignId,
        ];
        return $this->adminRequest("fcc-export-records", $post);
    }
    public function call($recordId, $consultantName): string {
        $post = [
            "records_id" => $recordId,
        ];
        return $this->consultantRequest("fcc-call", $post, $consultantName);
    }
    public function dropCall($callId, $consultantName){
        $post = [
            "calls_id" => $callId,
        ];
        return $this->consultantRequest("fcc-drop-call", $post, $consultantName);
    }
    /**
     * @return string
     * @throws HashFunctionNotDeclaredException
     */
    private function calcHash(string $login): string
    {
        $str = $login . $this->change . $this->apiKey;
        if ($this->isMd5()) {
            return md5($str);
        }
        throw new HashFunctionNotDeclaredException("Method hashowania jest błędna. Wartośc: " . $this->method);
    }
    private function getLoginWithDomain(string $login): string {
        return $login."@".$this->domain;
    }
    private function getAdminLoginWithDomain(): string
    {
        return $this->getLoginWithDomain($this->loginAdmin);
    }
    private function isMd5(): bool
    {
        return $this->method == self::MD5;
    }

    /**
     * @param $requestName
     * @param $body
     * @return string
     * @throws ConnectionException
     */
    private function adminRequest($requestName, $body): string
    {
        $authPostPart = $this->prepareAdminAuthPart();

        $body = array_merge($body, $authPostPart);

        return $this->baseRequest($requestName, $body);
    }

    /**
     * @param $requestName
     * @param $body
     * @param $consultantLogin
     * @return string
     * @throws ConnectionException
     */
    private function consultantRequest($requestName, $body, $consultantLogin): string
    {
        $authPostPart = $this->prepareConsultantAuthPart($consultantLogin);
        //print_r($authPostPart);exit;
        $body = array_merge($body, $authPostPart);

        return $this->baseRequest($requestName, $body);
    }

    /**
     * @param string $requestName
     * @param array $body
     * @return string
     * @throws ConnectionException
     */
    private function baseRequest(string $requestName, array $body): string {
        $curl = curl_init();
        //echo json_encode($body,JSON_PRETTY_PRINT);exit;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . $requestName,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "cache-control: no-cache,no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new ConnectionException($err);
        } else {
            return $response;
        }
    }
    private function prepareAdminAuthPart(): array
    {
        $login = $this->getAdminLoginWithDomain();
        return [
            "login" => $login,
            "change" => $this->change,
            "hash" => $this->calcHash($login),
            "method" => $this->method,
        ];
    }
    private function prepareConsultantAuthPart(string $login): array
    {
        //$login = $this->getLoginWithDomain($login);
        return [
            "login" => $login,
            "change" => $this->change,
            "hash" => $this->calcHash($login),
            "method" => $this->method,
        ];
    }
}
