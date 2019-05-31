<?php
/**
 * QueryListener.php
 *
 * Created by PhpStorm.
 * @author: fengzyz<fengzyz@meiyue.me>
 * @ComputerAccount:costa92
 * createTime: 2019/4/7 4:02 PM
 *
 */

namespace Fengzyz\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

class QueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $sql = str_replace("?", "'%s'", $event->sql);
        $log['sql'] = vsprintf($sql, $event->bindings);
        $log['time'] = $event->time;
        $requestId = app('requestId');
        $file = env('LOG_FILE_SQL_PATH') ? env('LOG_FILE_SQL_PATH') : storage_path('logs/sql.log');
        $output = "[%datetime%][%channel%][requestId:{$requestId}][Level:%level_name%][Message:%message% %context% %extra%]\n";
        (new \Monolog\Logger('sql'))->pushHandler((new RotatingFileHandler($file))
            ->setFormatter(new LineFormatter($output, null, true, true)))
            ->info("query", $log);
    }

}