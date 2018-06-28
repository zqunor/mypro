<?php
namespace app\lib\exception;

use think\Request;
use think\Log;
use think\exception\Handle;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(\Exception $e)
    {
        if($e instanceof BaseException){
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            if(config('app_debug')) {
                return parent::render($e);
            } else {
                $this->code = 500;
                $this->msg = '系统内部错误，不想告诉你~~';
                $this->errorCode = 999;
                $this->recordErrorLog($e);
            }
        }
        $request = Request::instance();
        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $request->url()
        ];
        return  json($result, $this->code);
    }

    public function recordErrorLog(\Exception $e)
    {
        Log::init(
            [
                'type' => 'File',
                'path' => LOG_PATH,
                'level' => ['error']
            ]);
        Log::record($e->getMessage(), 'error');
    }
}