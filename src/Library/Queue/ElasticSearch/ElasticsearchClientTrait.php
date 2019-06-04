<?php
/**
 * Created by PhpStorm.
 * User: shuyu
 * Date: 2019/6/4
 * Time: 15:59
 */

namespace Fengzyz\Queue\ElasticSearch;
use Elasticsearch\ClientBuilder;

Trait ElasticsearchClientTrait
{
    /**
     * Get ElasticSearch Client
     *
     * @return \Elasticsearch\Client
     */
    public function getElasticsearchClient()
    {
        $hosts = config('scout.elasticsearch.hosts');
        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
        return $client;
    }
}