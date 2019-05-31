<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/8
 * Time: 18:02
 */

namespace Fengzyz\Exception;

use App\Constants\ErrorCode;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerException
{
    public function __construct()
    {
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public static function handler($request, Exception $e)
    {
        if (is_a($e, NotFoundHttpException::class)) {
            return response()->json(['errorCode' => 404, 'msg' => 'Url NotFound']);
        }
        $code = $e->getCode() == 0 ? ErrorCode::FAIL : ($e->getCode() == ErrorCode::SUCCESS ? ErrorCode::FAIL : $e->getCode());
        Log::error($e->getMessage(), ['file' => $e->getFile() . ':' . $e->getLine(), 'code' => $e->getCode()]);
        $result = array('errorCode' => $code, 'errorMsg' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine());
        $appEnv = getenv("APP_ENV") ? getenv("APP_ENV") : "production";
        if ($appEnv == 'production') {
            $result = array('errorCode' => $code, 'errorMsg' => $e->getMessage());
        }
        return response()->json($result);
    }
}