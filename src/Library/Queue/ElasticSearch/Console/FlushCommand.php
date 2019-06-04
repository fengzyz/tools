<?php
/**
 * Created by PhpStorm.
 * User: shuyu
 * Date: 2019/6/4
 * Time: 16:03
 */

namespace Fengzyz\Queue\ElasticSearch\Console;
use Illuminate\Console\Command;
use Fengzyz\Queue\Elasticsearch\ElasticsearchClientTrait;

class FlushCommand extends Command
{
    use ElasticsearchClientTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:flush {model}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = $this->argument('model');
        $this->call('scout:flush', [
            'model' => $class
        ]);
        $model = new $class;
        $index = [
            'index' => config('scout.elasticsearch.prefix').$model->searchableAs()
        ];
        $client = $this->getElasticsearchClient();
        $client->indices()->delete($index);
    }
}