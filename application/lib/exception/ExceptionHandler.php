<?php
namespace app\lib\exception;

use think\Request;
use think\Exception;
use think\exception\Handle;
use app\lib\exception\BaseException;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    // 渲染所有的错误信息，并返回到客户端
    public function render(Exception $e)
    {
        if($e instanceof BaseException){
            // 如果是自定义的异常处理,需要向客户端返回具体的消息（用户操作导致的异常）
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            $this->code = 500;
            $this->msg = '系统内部错误，不想告诉你~~';
            $this->errorCode = 999;
        }
        $request = Request::instance();
        $result = [
            'msg' => $this->msg,
            'errorCode' => $this->errorCode,  
            'request_url' => $request->url()
        ];
        return  json($result, $this->code);
    }
}  