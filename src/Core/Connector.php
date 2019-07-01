<?php
/**
 * Created by PhpStorm.
 * User: kmadenski
 * Date: 01.07.19
 * Time: 12:59
 */

namespace App\Core;

use App\Exception\ConnectionException;
use App\Exception\HashFunctionNotDeclaredException;

class Connector
{
    CONST MD5 = 'md5';
    private $loginAdmin;
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
        return $this->request("fcc-campaigns-list", $post);
    }
    public function classifiersList($campaignId): string {
        $post = [
            "campaigns_id" => $campaignId
        ];
        return $this->request("fcc-classifiers-list", $post);
    }
    public function addRecords($campaignId, array $records): string {
        $post = [
            "campaigns_id" => $campaignId,
            "records" => $records
        ];
        return $this->request("fcc-add-records", $post);
    }
    public function updateRecords($campaignId, array $records): string {
        $post = [
            "campaigns_id" => $campaignId,
            "records" => $records
        ];
        return $this->request("fcc-update-records", $post);
    }
    public function exportRecords($campaignId): string {
        $post = [
            "campaigns_id" => $campaignId,
        ];
        return $this->request("fcc-export-records", $post);
    }
    public function call($recordId): string {
        $post = [
            "records_id" => $recordId,
        ];
        return $this->request("fcc-call", $post);
    }
    /**
     * @return string
     * @throws HashFunctionNotDeclaredException
     */
    private function getHash(): string
    {
        $str = $this->loginAdmin . $this->change . $this->apiKey;
        if ($this->isMd5()) {
            return md5($str);
        }
        throw new HashFunctionNotDeclaredException("Method hashowania jest błędna. Wartośc: " . $this->method);
    }

    private function getLoginWithDomain(): string
    {
        return $this->loginAdmin . "@" . $this->domain;
    }

    private function isMd5(): bool
    {
        return $this->method == self::MD5;
    }

    public function request($requestName, $body): string
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . $requestName,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(array_merge($body, $this->prepareBodyAuthPart())),
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

    private function prepareBodyAuthPart(): array
    {
        return [
            "login" => $this->getLoginWithDomain(),
            "change" => $this->change,
            "hash" => $this->getHash(),
            "method" => $this->method,
        ];
    }
}
