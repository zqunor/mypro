<?php
namespace app\lib\exception;

use think\Request;
use think\Exception;
use think\Log;
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
            // 服务器自身异常（代码错误，调用  外部接口错误），记录到日志，不向客户端返回具体问题
            
            // 客户端开发人员不需要错误信息（json结构体即可），服务器开发人员需要错误信息进行调试
            // 通过配置项判断当前需要的错误提示
            // 配置文件不考虑写入，记录某些值时应记录到数据库或redis等
            if(config('app_debug')) {
                // 返回框架默认的错误信息
                return parent::render($e);
            } else {
                // 返回自定义的错误信息（json格式）
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

    public function recordErrorLog(Exception $e)
    {
        Log::record($e->getMessage(), 'error');
    }
}  