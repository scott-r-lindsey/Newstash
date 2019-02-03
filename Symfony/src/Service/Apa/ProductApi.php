<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Service\DelayFish;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class ProductApi
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

    public function ItemLookup(
        array $asins,
        array $responses
    ): SimpleXMLElement
    {
        $params = [
            'Operation'     => 'ItemLookup',
            'ItemId'        => implode(',', $asins),
            'IdType'        => 'ASIN',
            'ResponseGroup' => implode(',', $responses)
        ];

        $this->logger->notice("APA ItemLookup on " . implode(',', $asins));

        return $this->aws_signed_request($params);
    }

    public function topSellerBrowsenodeLookup(
        int $browseNode
    ): SimpleXMLElement
    {
        $params = [
            'Operation'     => 'BrowseNodeLookup',
            'BrowseNodeId'  => $browseNode,
            'ResponseGroup' => 'TopSellers',
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

        $this->logger->info("APA query " . print_r($params, true));

        $this->delayProvider->delay();

        $fail = 0;
        $xml = null;
        while (null === $xml) {
            $query              = $this->generateSignedQuery($params);

            if ($fail === 10) {
                throw new \Exception('Too many guzzle errors');
            }

            try {
                $response           = $this->client->request($method, $query);
                $xml                = $response->getBody();
            }
            catch (ClientException | ServerException $e) {
                $response = $e->getResponse();
                $this->logger->notice("Caught guzzle exception code " . $response->getStatusCode());

                $fail++;
                $this->delayProvider->delay();
            }
        }

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
