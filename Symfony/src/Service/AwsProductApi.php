<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use \GuzzleHttp\Client;
use App\Service\DelayFish;

class AwsProductApi
{
    private $logger;
    private $client;
    private $aws_access_key_id;
    private $aws_secret_access_key;
    private $aws_associate_tag;
    private $delayProvider;

    public function __construct(
        LoggerInterface $logger,
        Client $api,
        string $aws_access_key_id,
        string $aws_secret_access_key,
        string $aws_associate_tag,
        DelayFish $delayProvider
    )
    {
        $this->logger                   = $logger;
        $this->client                   = $api;
        $this->aws_access_key_id        = $aws_access_key_id;
        $this->aws_secret_access_key    = $aws_secret_access_key;
        $this->aws_associate_tag        = $aws_associate_tag;
        $this->delayProvider            = $delayProvider;
    }

    /**
     *
     */
    public function browseNodeLookup(
        int $nodeId
    ): \SimpleXMLElement
    {
        $params = [
            'Operation'     => 'BrowseNodeLookup',
            'BrowseNodeId'  => $nodeId,
            'ResponseGroup' => 'BrowseNodeInfo',
        ];

        return $this->aws_signed_request($params);
    }

    # -------------------------------------------------------------------------

    /**
     *
     */
    private function aws_signed_request(
        array $params,
        string $method = 'GET'
    ): \SimpleXMLElement{

        // FIXME where should this go?
        libxml_use_internal_errors(true);

        $query              = $this->generateSignedQuery($params);

        $this->delayProvider->delay();

        $response           = $this->client->request($method, $query);
        $xml                = $response->getBody();

        $sxe                = new \SimpleXMLElement((string)$xml);

        return $sxe;
    }

    /**
     *
     */
    private function generateSignedQuery(array $params): string
    {
        $config = $this->client->getConfig();

        $scheme     = $config['base_uri']->getScheme();
        $host       = $config['base_uri']->getHost();
        $path       = $config['base_uri']->getPath();

        $additional_params = array(
            'Service'           => 'AWSECommerceService',
            'AWSAccessKeyId'    => $this->aws_access_key_id,
            'AssociateTag'      => $this->aws_secret_access_key,
            'Timestamp'         => gmdate("Y-m-d\TH:i:s\Z"),
            'Version'           => "2011-08-01"
        );

        $canonicalized_query    = [];
        $params                 = array_merge($params, $additional_params);
        ksort($params);

        foreach ($params as $key => $value){
            $key                    = str_replace("%7E", "~", rawurlencode((string)$key));
            $value                  = str_replace("%7E", "~", rawurlencode((string)$value));
            $canonicalized_query[]  = $key."=".$value;
        }

        $canonicalized_query    = implode("&", $canonicalized_query);
        $string_to_sign         = "GET\n$host\n$path\n$canonicalized_query";
        $signature              = base64_encode(
            hash_hmac("sha256", $string_to_sign, $this->aws_secret_access_key, true));

        $signature              = str_replace("%7E", "~", rawurlencode($signature));

        $query = "?$canonicalized_query&Signature=$signature";

        $this->logger->info(
            "-----> API call: $scheme://$host$path$query");

        return $query;

    }
}
