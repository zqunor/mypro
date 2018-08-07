<?php
/**
 * User: zhouqun
 * Time: 2018/8/7 9:21
 */

namespace app\lib\enum;

class ScopeEnum
{
    // scope=16 代表App用户的权限数值
    const User = 16;

    // scope=32 代表CMS（管理员）用户的权限数值
    const Super = 32;
}