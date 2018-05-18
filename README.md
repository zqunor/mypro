### 7.2 从一个错误了解 Exception 的继承关系

1.将`Exception`修改为全局`Exception`基类，而不是`think\Exception`

    `think\Exception => \Exception =>  Throwable`
    `HttpException => \RuntimeException =>  \Exception =>  Throwable`

**当访问的控制器不存在、url 错误时，属于 HttpException 异常。** 而原先定义的`render()`和`recordErrorLog()`方法要求接收的参数类型是`think\Exception`，由于`HttpException`不是继承于`think\Exception`，不能转化为`think\Exception`，所以会报错。

> 解决：使用全局 Exception 基类后，既支持 HttpException，又支持 think\Exception。子类可以自动被转化为父类的类型。
> Exception 基类是所有异常类的基类。

2.**补充**：PHPStrom 快捷键：

* ctrl+alt+O => 快速删除没有 use 的类

### 7.3 TP5 数据库中间层架构解析

![](https:raw.githubusercontent.com/zqunor/MarkdownPic/master/thinkphp5+小程序商城/7-3tp5数据库架构.png)

### 7.4 查询构造器一

1、为什么不使用原生的查询语句而使用查询构造器？

* 简洁方便
* 对不同数据库的操作进行了封装，使用统一的数据库操作标准

2、对查询构造器的理解

* 查询构造器不仅仅是对数据库进行读操作，还包括数据库的写操作
* 查询构造器仅仅是语法，最终都是通过 Builder 翻译成 SQL 语句来执行

### 7.5 查询构造器二

1.查询语句 = 链式方法 + 执行方法

2.链式方法：

* where
* whereOr
* field
* ...

只会返回 Query 对象，不是查询结果

3.执行方法：

* find
* select
* update
* delete
* insert

  4.**在执行方法调用前，查询状态是保留的，直到调用执行方法后，状态才会被清除**

### 7.6 查询构造器三

1.链式方法说明（where）：

> where('字段名','表达式','查询条件')

2.三种实现方式：

* 表达式

* 数组法（不够灵活，且存在一定的安全问题）

* 闭包（最灵活）

```php
// 通过use来使用外部的数据
where(function ($query) use ($id){
    $query->where('banner_id', '=', $id)
})
```

### 7.7 开启 SQL 日志记录

1.database.php =》 debug=true

2.config.php =》 app_debug=true

3.config.php =》 log =》 level =》sql

```php
'log'                    => [
     日志记录方式，内置 file socket 支持扩展
    'type'  => 'File',
     日志保存目录
    'path'  => LOG_PATH,
     日志记录级别
    'level' => ['error', 'sql'],
],
```

不在配置文件中开启日志

`ExceptionHandler`=>`recordErrorLog` 方法中开启日志仅仅在发生异常时写入。

所以对于全局的情况，将日志手动添加到入口文件中，对所有调用都开启 sql 日志

？？这样和在配置文件中开启日志有什么区别？

### 7-8 ORM 与模型

1.ORM 理解：

ORM = `Object Relationship Map`

对象关系映射:将每张数据表看作是一个对象

2.模型（TP5 模型）--ORM 实现的具体机制
=> `业务的集合= 数据库查询+业务逻辑+...`

【注】模型与数据表不是一一对应的关系，简单的业务逻辑看上去是一张表对应一个模型，但复杂的业务逻辑（需要分层）可能是横跨多个表。

模型不仅仅只是 model 这一层，复杂的业务还可以继续划分，tp5 中有 `model`(数据层，细)，`service`(服务层，粗)，`logic`(逻辑层)

### 7-9 初识模型

1.`model/Banner.php` 继承 Model 类之后，就成为了模型，就可以使用模型类已经封装好的方法(`get`)，而不用自定义方法(`getBannerById`)。

```php
$banner = BannerModel::getBannerById($id);
 // => 返回数组
 //等价于
$banner = BannerModel::get($id);
// => 返回对象，便于处理查询结果
```

2.tp5 自动将对象序列化，序列化的格式根据配置文件中的配置项`default_return_type`转化为相应的格式。

```php
// config.php
'default_return_type' => 'html'
```

3.自定义模型方法(`getBannerById`)调用的是指定的数据表，而模型类自动的方法(`get`)调用的是模型类名对应的数据表。

### 8-1 Banner 相关表分析（数据表关系分析）

* banner 位的数据表`banner`

`banner(id, name, description, delete_time, update_time)`

* 每个 banner 位图片的数据表`banner_item`

`banner_item(id, img_id, key_word, type, delete_time, banner_id, update_time)`

* 图片表`image`

`image(id, url, from, delete_time, update_time)`

    banner <=> banner_item 一对多关系
    image <=> banner_item 一对一关系

### 8-2 模型关联--定义关联与查询关联

> model/Banner.php

```php
// 创建关联方法
public function items()
{
    // 参数1：关联模型的模型名
    // 参数2：关联模型的外键
    // 参数3：当前模型的主键
    // hasMany：表示是一对多的关系
    return $this->hasMany('BannerItem', 'banner_id', 'id');
    // 【需要创建BannerItem模型类文件】
}
```

> controller/Banner.php

```php
//with()方法，设置关联模型
$banner = BannerModel::with('items')->find($id);
//等价于 $banner = model('banner')->with('items')->find($id);
```

执行结果时会自动附件关联信息。

```json
{
  "id": 1,
  "name": "首页置顶",
  "description": "首页轮播图",
  "delete_time": null,
  "update_time": "1970-01-01 08:00:00",
  "items": [
    {
      "id": 1,
      "img_id": 65,
      "key_word": "6",
      "type": 1,
      "delete_time": null,
      "banner_id": 1,
      "update_time": "1970-01-01 08:00:00"
    }
  ]
}
```

### 8-3 模型关联 --嵌套关联查询

1.多个关联表

    with(['items','item2'])

2.命令行创建模型（自动完成模板）

    php think make:model api/Image

3.banner 嵌套 items，现在需要给 items 嵌套 img 相关信息

多层嵌套使用方法：

    with(['items', 'items.img'])

4.具体实现：

* `model/BannerItem.php`

```php
public function img()
{
    // BannerItem和Image是一对一的关系，使用的方法是belongsTo
    return $this->belongsTo('Image', 'img_id', 'id');
    //【需要创建Image模型类文件】
}
```

也可以在`model/Image.php`中定义,实现的效果是一样的。

* `controller/Banner.php`

```php
// 多层嵌套的使用
$banner = BannerModel::with(['items', 'items.img'])->find($id);
```

5.实现效果如下：

```json
{
  "id": 1,
  "name": "首页置顶",
  "description": "首页轮播图",
  "delete_time": null,
  "update_time": "1970-01-01 08:00:00",
  "items": [
    {
      "id": 1,
      "img_id": 65,
      "key_word": "6",
      "type": 1,
      "delete_time": null,
      "banner_id": 1,
      "update_time": "1970-01-01 08:00:00",
      "img": {
        "id": 65,
        "url": "/banner-4a.png",
        "from": 1,
        "delete_time": null,
        "update_time": "1970-01-01 08:00:00"
      }
    },
    {...}
  ]
}
```

### 8-4 隐藏模型字段

* 方法 1：将对象转化为数组`toArray()`，再将该字段 unset

```php
$banner = $banner->toArray();
unset($banner['delete_time']);
```

* 方法 2：使用对象的 `hidden()` 方法

```php
$banner->hidden(['update_time', 'delete_time']);
```

* 方法 3：只显示指定字段`visible()`

```php
$banner->visible(['id', 'name']);
```

### 8-5 在模型内部隐藏字段

1.对嵌套的数据字段隐藏

最好的办法：在相应的模型类中定义相应的属性。

* 想要隐藏 `banner` 的字段信息

```php
// model/Banner.php
// 隐藏的字段
protected $hidden = ['id'];
// 只显示的字段
protected $visibale = ['name','update_time'];
```

* 想要隐藏 `banner.items` 下的字段信息：

```php
// model/BannerItem.php
protected $hidden = ['id'];
```

* 想要隐藏 `banner.items.img` 的字段信息

```php
// model/Image.php
protected $hidden = ['from'];
```

### 8-6 图片资源 URL 配置

1.数据库存放的图片 url 是相对地址，所以获取的数据也是相对地址，不能直接获取到图片的具体资源位置。

    具体路径 = 服务器域名+路径配置+相对地址

2.定义自己项目相关的配置 =》 自定义配置文件

TP5 扩展配置目录 =》自动加载该目录下的配置文件

默认位置：`application/extra`

3.定义配置项:

```php
// application/extra/setting.php
return [
  'img_prefix' => 'http://mypro.com/images'
];
```

4.tp5 中只有 public 目录是对外公开，可以访问的，所以图片资源应当放在 public 目录下

5.读取配置文件：

```php
// 配置文件自动被加载，直接读取配置项即可
config('setting.img_prefix');
```

6.**【注】：如果自定义了`CONF_PATH`目录，则自动加载的配置文件目录应该在`config/extra`目录下**

```php
// public/index.php
// 自定义CONF_PATH目录
define('CONF_PATH', __DIR__ . '/../config/');
```

### 8-7 模型读取器的巧妙应用

1.读取器的命名：`get+字段名+Attr`

如对 url 处理则定义为`getUrlAttr`

2.读取器的特性：

* 模型具有的性质
* 使用模型时自动调用的方法
* AOP思想的一个实现

  3.接收器参数说明：

      参数1：需要处理的字段的值
      参数2：当前记录的完整信息(包括隐藏未显示的字段)

  4.使用方法：

```php
// 定义读取器（框架自动调用）
public function getUrlAttr($value)
{
    // $value为获取到的url值。
    $prefix = config('setting.img_prefix');
    return $prefix.$value;
}
```

url 字段被自动拼接成：`"url": "http://mypro.com/images/banner-4a.png"`形式

5.根据业务逻辑进行调整

image 数据表中的`from`字段标识当前图片的来源。

    from=1 =》 图片来自当前项目，存储的是 相对路径
    from=2 =》 图片来自网络，存储的是 绝对路径

即：当 from=1 时，才需要对 url 进行相关操作。

此时需要访问到`from`的值，要用到第二个参数。

6.调整代码实现

```php
// 定义读取器（框架自动调用）
public function getUrlAttr($value, $data)
{
    // $value 获取到的url值。
    // $data 当前记录的完整信息(包括隐藏未显示的字段)

    $finalUrl = $value;
    if ($data['from'] == 1) {
        $prefix = config('setting.img_prefix');
        $finalUrl = $prefix . $value;
    }

    return $finalUrl;
}
```