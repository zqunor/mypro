### 7.2 从一个错误了解 Exception 的继承关系

1.将`Exception`修改为全局`Exception`基类，而不是`think\Exception`

    `think\Exception => \Exception =>  Throwable`
    `HttpException => \RuntimeException =>  \Exception =>  Throwable`

**当访问的控制器不存在、url 错误时，属于 HttpException 异常。** 而原先定义的`render()`和`recordErrorLog()`方法要求接收的参数类型是`think\Exception`，由于`HttpException`不是继承于`think\Exception`，不能转化为`think\Exception`，所以会报错。

> 解决：使用全局 Exception 基类后，既支持 HttpException，又支持 think\Exception。子类可以自动被转化为父类的类型。
> Exception 基类是所有异常类的基类。

2.**补充**：PHPStrom 快捷键：

- ctrl+alt+O => 快速删除没有 use 的类

### 7.3 TP5 数据库中间层架构解析

![](https:raw.githubusercontent.com/zqunor/MarkdownPic/master/thinkphp5+小程序商城/7-3tp5数据库架构.png)

### 7.4 查询构造器一

1、为什么不使用原生的查询语句而使用查询构造器？

- 简洁方便
- 对不同数据库的操作进行了封装，使用统一的数据库操作标准

2、对查询构造器的理解

- 查询构造器不仅仅是对数据库进行读操作，还包括数据库的写操作
- 查询构造器仅仅是语法，最终都是通过 Builder 翻译成 SQL 语句来执行

### 7.5 查询构造器二

1.查询语句 = 链式方法 + 执行方法

2.链式方法：

- where
- whereOr
- field
- ...

只会返回 Query 对象，不是查询结果

3.执行方法：

- find
- select
- update
- delete
- insert

  4.**在执行方法调用前，查询状态是保留的，直到调用执行方法后，状态才会被清除**

### 7.6 查询构造器三

1.链式方法说明（where）：

> where('字段名','表达式','查询条件')

2.三种实现方式：

- 表达式

- 数组法（不够灵活，且存在一定的安全问题）

- 闭包（最灵活）

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

- banner 位的数据表`banner`

`banner(id, name, description, delete_time, update_time)`

- 每个 banner 位图片的数据表`banner_item`

`banner_item(id, img_id, key_word, type, delete_time, banner_id, update_time)`

- 图片表`image`

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

- `model/BannerItem.php`

```php
public function img()
{
    // BannerItem和Image是一对一的关系，使用的方法是belongsTo
    return $this->belongsTo('Image', 'img_id', 'id');
    //【需要创建Image模型类文件】
}
```

也可以在`model/Image.php`中定义,实现的效果是一样的。

- `controller/Banner.php`

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

- 方法 1：将对象转化为数组`toArray()`，再将该字段 unset

```php
$banner = $banner->toArray();
unset($banner['delete_time']);
```

- 方法 2：使用对象的 `hidden()` 方法

```php
$banner->hidden(['update_time', 'delete_time']);
```

- 方法 3：只显示指定字段`visible()`

```php
$banner->visible(['id', 'name']);
```

### 8-5 在模型内部隐藏字段

1.对嵌套的数据字段隐藏

最好的办法：在相应的模型类中定义相应的属性。

- 想要隐藏 `banner` 的字段信息

```php
// model/Banner.php
// 隐藏的字段
protected $hidden = ['id'];
// 只显示的字段
protected $visibale = ['name','update_time'];
```

- 想要隐藏 `banner.items` 下的字段信息：

```php
// model/BannerItem.php
protected $hidden = ['id'];
```

- 想要隐藏 `banner.items.img` 的字段信息

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
  'img_prefix' => 'http://mypro.com/static/images'
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

- 模型具有的性质
- 使用模型时自动调用的方法（访问该属性时调用）
- AOP 思想的一个实现

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

url 字段被自动拼接成：`"url": "http://mypro.com/static/images/banner-4a.png"`形式

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

通过关联模型访问 Image 模型并获取 url 字段信息时调用该方法。

### 8-8 自定义模型基类

1.对于多个模型处理 url 字段时，为增强代码的复用性，可将该处理方法封装到模型类基类`model/BaseModel.php`中。

2.其他的模型类不再直接继承`model`类，而是直接继承`BaseModel`类。

3.又考虑到当前使用的 url 表示的是 img 路径，而其他数据表中的 url 可能并非 img 路径，所以需要再次调整。将`getUrlAttr`功能的具体实现进行拆分。

(1) `model/BaseModel.php`，定义成一个普通的方法

```php
public function prefixImgUrl($value, $data)
{
    $finalUrl = $value;
    if ($data['from'] == 1) {
        $prefix = config('setting.img_prefix');
        $finalUrl = $prefix . $value;
    }

    return $finalUrl;
}
```

(2) `model/Image.php`，读取器中调用基类的方法。

```php
public function getUrlAttr($value, $data)
{
    return $this->prefixImgUrl($value, $data);
}
```

(3)分析：将业务逻辑的具体实现集中到一起，简化业务变动时的频繁修改。提高了项目的**扩展性**。

### 8-9 定义 API 版本号

1.为什么要实现多版本？

由于业务调整，实现的功能需要进行变更，（处理同一个问题需要使用不同解决方式），并且之前的功能还需要兼容，此时如果通过判断条件进行判断，再执行相应的功能会使得代码冗余，违背代码的**开闭原则**。应该将代码分离出来，每一个版本做一个单独的代码模块。

> 开闭原则：对扩展是开放的，对修改是封闭的。（以扩展的形式修改代码）

2.如何实现多版本？

- 目录设置:

```info
application
    |__ api
        |__ controller
            |__ v1
                |__ Banner.php
            |__ v2
                |__ Banner.php
```

- 路由设置：

```php
// 动态参数 :version 动态访问相应版本
Route::get('api/:version/banner/:id', 'api/:version.Banner/getBanner');
```

### 8-10 专题接口模型分析

- theme 专题表
  `theme(id,name,description,topic_img_id,delete_time,head_img_id,update_time)`

      `topic_img_id` 首页主题入口的img图片
      `head_img_id` 进入相应主题显示的head图片

- product 产品表
  `product(id,name,price,stock,delete_time,category_id,main_img_url,from,create_time,update_time,summary,img_id)`

      `main_img_url`
      `img_id`

- theme_product 专题-产品关联表
  `theme_product(theme_id,product_id)`

      theme <=> product 多对多关系
      theme_product 多对多关系表中需要一个关联表连接两者关系

### 8-11 一对一关系解析

    theme <=> image 一对一关系

1.一对一关系的表示方法（有主从关系）：

    hasOne()
    belongsTo()

外键存储在其中一张表里，所以需要使用`hasOne`和`belongsTo`来区分。

    有外键的表`belongsTo`无外键的表
    无外键的表`hasOne`有外键的表

theme -- (topic_img_id, head_img_id) -- 表中有外键 (对应 image 表中的 id 主键)
=》 theme topicImg belongsTo image

image -- 表中没有外键
=》 image hasOne theme

### 8-12 Theme 接口验证与重构

1.Theme 接口实现的不同方法对比：

（1）客户端只负责调用接口，由接口确定需要返回的主题 theme 的 id 号（2）由客户端传入具体需要的主题 Theme 的 id 号（前端有更大的灵活性）

2.方法实现

步骤：

(1)定义控制器方法名

```php
// api/controller/v1/Theme.php
getSimpleList();
```

(2)路由文件定义路由

```php
// config/route.php
Route::get('api/:version/theme', 'api/:version.Theme/getSimpleList');
```

(3)控制器方法具体实现业务功能（一） --- 参数要求

```php
/**
 * 获取需要展示的主题theme
 * @Location api/controller/v1/Theme.php
 * @param string $ids
 * @return string $theme
 */
```

(4)验证器验证

```php
// api/validate/IDCollection.php

// 1.验证规则
protected $rule = [
    'ids' => 'require|checkIDs'
];

// 2.验证不通过的提示信息
protected $message = [
    'ids' => 'ids必须是以逗号隔开的多个正整数'
];

// 3.自定义验证方法(验证器)
/**
 * 验证ids
 * @param string $values = id1,id2,id3,...
 * @return bool false/true
 */
protected function checkIDs($values)
{
    $ids = explode(',', $values);

    if (empty($ids)) {
        return false;
    }
    foreach ($ids as $id) {
        // 每个id必须是正整数
        $res = $this->isPositiveInteger($id);
        if (!$res) {
            return false;
        }

        return true;
    }
}
```

3.**扩展**:

IDCollection 和 IDMustPositiveInt 都用到对 id 是正整数的验证，为提高代码的复用性，可以：

    （1）将isPositiveInteger提取到公共方法中（没有内聚性）

    （2）将方法重新定义到验证器基类中供所有验证器之类调用。（优化的选择）

```php
// api/validate/BaseValidate.php
/**
 * 验证是否是正整数
 *
 * @param int $value
 * @return boolean false/true
 */
protected function isPositiveInteger($value)
{
    if (is_numeric($value) && is_int($value+0) && ($value +0)>0) {
        return true;
    } else {
        return false;
    }
}
```

4.调用验证器

```php
// api/controller/v1/Theme.php
(new IDCollection())->goCheck();
```

5.测试 url

```url
http://mypro.com/api/v1/theme?ids=0.1,2,3
http://mypro.com/api/v1/theme?ids=1,s,3
```

### 8-13 完成 Theme 简要信息接口

1.完成获取信息接口

```php
// api/controller/v1/Theme.php
public function getSimpleList($ids='')
{
    // 验证用户传递的参数
    (new IDCollection())->goCheck();

    $ids = explode(',', $ids);

    // 关联Image表获取相应信息
    $theme = model('theme')->with(['topicImg', 'headImg'])->select($ids);

    // 无查询结果时，进行异常处理
    if (!$theme) {
        throw new ThemeMissException();
    }

    // 对数组格式的返回数据进行json格式化
    return json($theme);
}
```

2.完成异常处理类

```php
// application/lib/exception/ThemeMissException.php
public $code = 404;
public $msg = '请求的主题不存在';
public $errorCode = 30000;
```

3.在相应的模型中隐藏部分字段

(1)隐藏 Theme 表的部分字段

```php
// api/model/v1/Theme.php
protected $hidden = ['delete_time', 'update_time', 'topic_img_id', 'head_img_id'];
```

(2)隐藏 Image 表的部分字段(只显示部分字段)

```php
// api/model/v1/Image.php
protected $visible = ['url'];
```

4.补充说明：

对于复杂的业务处理，应该将相应的代码写到 Service 层(Model 层之上) -- 特别是涉及到多个模型之间的关联的时候

### 8-14 开启路由完整匹配

1.功能需求说明

```info
点击专题图片进入到专题后需要显示相应的产品图片、

=》获取属于该专题的产品信息

（一个产品可以属于一个专题，也可以属于多个专题； 一个专题会包含多个产品） ==》多对多关系[Theme <=> Product]

多对多关系的数据表有一个中间关联表
```

2.模型关联获取关联的数据

```php
// api/model/Theme.php
public function products()
{
    // 参数1： 对应数据表的模型名
    // 参数2： 关联表的模型名
    // 参数3： 关联表中的外键名(和参数1模型关联)
    // 参数4： 关联表的外键(关联当前模型)
    return $this->belongsToMany('Product', 'theme_product', 'product_id'. 'theme_id');
}
```

3.编写控制器方法(定义方法名和需要接收的参数)

```php
// api/v1/controller/Theme.php
public function getProducts($id){}
```

4.定义路由

```php
Route::get('api/:version/theme/:id', 'api/:version.Theme/getProducts/:id');
```

【注意】：

默认情况下 TP5 的配置项是关闭路由完整匹配的，这种情况下访问当前路由接口时，由于先匹配到`api/:version/theme`路由，便不会再继续向下匹配路由，从而会调用该路由对应的接口。

==》**解决办法**：`开启路由完整匹配`

```php
// application/config.php默认配置文件路径
// 路由使用完整匹配（设置为true时开启）
'route_complete_match'   => false,  // =>true
```

### 8-15 完成 Theme 详情接口

1.参数校验

```php
// api/v1/controller/Theme.php
(new IDMustPositiveInt)->check();
```

2.在模型中编写方法实现数据获取

```php
// api/model/Theme.php
public function getThemeWithProducts($id)
{
    $theme = self::with('products,topicImg,headImg')->find($id);
    return $theme;
}
```

【注】REST 是面向资源的请求方式，即将相关的数据全部返回给客户端，不管客户端目前需不需要用得上，但这种方式返回的资源应该有一个限度，

3.在控制器中调用

```php
// api/v1/controller/Theme.php
$theme = model('theme')->getThemeWithProducts($id);
if(!$theme) {
    throw new ThemeMissException();
}
return $theme;
```

4.编写异常处理类

```php
// api/lib/exception/ThemeMissException.php
class ThemeMissException extends BaseException
{
    /**
     * 覆盖父类的相应属性
     */
    public $code = 404;
    public $msg = '请求的主题不存在';
    public $errorCode = 30000;
}
```

### 8-16 数据库字段冗余的合理利用

多对多关系的数据表关联查询时会自动多一个`pivot`字段的信息，存储关联字段。但关联信息不是我们需要显示的信息，所以将该字段隐藏掉。

`products`中`main_img_url`和`img_id`都是用来关联 image 表，记录图片信息。属于数据冗余。

但此处是数据冗余的合理应用范围，因为需要在多处使用到，并且数据量和业务并不是太复杂。

### 8-17 REST 的合理利用

1.数据冗余之后对数据的完整性和一致性的维护变得困难。

2.数据更新时需要对多处数据进行修改，否则就会出现数据不一致的现象。

3.完成方法编写(对 product 相关字段的 url 进行处理---添加前缀)

```php
// api/model/Product.php
public function getMainImgUrlAttr($value, $data)
{
    return $this->prefixImgUrl($value, $data);
}
}
```

4.REST 设计原则

(1)REST 是基于资源的，凡是和业务相关的数据都应该返回，不管当前的业务是否需要使用相应的数据。

好处在于后期业务变更需要相应的数据的时候，可以直接调用即可，不用更改服务器的接口程序，可以用来保证客户端的稳定性。

(2)但也不能一味的将所有相关的数据返回，会消耗数据库的性能。

### 8-18 最近新品接口编写

1.TP5 框架自带时间更新操作,使用模型操作数据库时，当插入记录时，自动带上`create_time`; 更新操作时自动带上`updated_time`;删除时自动带上`delete_time`

2.删除操作不是真实的物理删除，而是通过判断`delete_time`的值来确定该条记录的状态

3.实现步骤

(1)定义控制器方法 [方法名|传递参数]

```php
public function getRecent($count=15){}
```

(2)定义路由

```php
Route::get('api/:version/product/recent', 'api/:version.Product/getRecent');
```

(3)定义模型方法

- limit()方法的使用

```php
public function getMostRecent($count)
{
    $products = self::limit($count)->order('create_time desc')->select();
    return $products;
}
```

(4)编写验证器

```php
//需要对传递的count值进行验证
// application/validate/Count.php
protected $rule = [
    'count' => 'isPositiveInteger|between:1,15'
];
```

(5)完成控制器方法

```php
public function getRecent($count=15)
{
    (new Count())->goCheck();
    $products = model('product')->getMostRecent($count);
    if ($products) {
        throw new ProductMissException();
    }
    return $products;
}
```

(6)完成异常处理类方法

```php
class ProductMissException extends BaseException
{
    public $code = '404';
    public $msg = '请求的product不存在';
    public $errorCode = 20000;

}
```

[注]：`app_debug`设置为 true 时，在`ExceptionHandler.php`中会调用父类的`render()`方法，导致框架的异常处理类找不到程序中自定义的异常处理类，从而会有报错提示。

!!!出现 500 系统内部错误!

- 原因=>config.php 设置`default_return_type`的值为`html`, 而 Product 的 controller 中 return 的结果值为 array，导致系统内部错误。

- 解决=>将`default_return_type`的值为`json`。或者将 Product 的 controller 中 return 的结果进行 json 格式化。

#### **【警告】学会查看 log 日志信息，提高错误排查能力！**

### 8-19 使用数据集还是数组？

1.问题 1：验证方法中，`$rule`属性数组的键值对中， 值`'isPositiveInteger|between:1,15'`中`|`符两端不能有空格，否则会被视为验证错误。

2.问题 2：对某些当前不需要用到，但后期会用到的字段信息（特殊情况不用，大多数情况要用），既不能直接显示，也不能直接隐藏，如何处理？

=》 在`api/v1/Product/recent`接口中临时隐藏`summary`字段。

3.**collection()方法**：临时隐藏某个或某些字段

【使用方法】：

```php
// 使用数据集，临时隐藏某些字段
$productCollection = collection($products);
$products = $productCollection->hidden(['summary']);
```

4.一个 product 是一个对象，一组 product 也可是是一个对象(数据集)。

5.使用对象的方式，可读性好，内聚性好。

6.TP5 调用模型自动返回一个数据集的形式：`resultset_type` [database.php]

默认是`array`，设置成`collection`后，模型返回的数据自动就是`collection`形式，不需要再转换一次。

```php
// 在database.php中配置之后，不需要手动转换为collection
$products = $products->hidden(['summary']);
```

【扩展】：

但是这样使用之后，控制器中调用模型返回数据后，返回的是对象，即使没有数据，也不是空，所以直接使用`!`判断是不能实现效果的。

=》解决方法：使用数据集对象的`isEmpty()`方法进行判空。

### 8-20 分类列表接口

1.模型类的`all`方法使用。

- 参数 1：主键列表或者查询条件（闭包） `mixed`

- 参数 2：关联预查询 `array | string`

```php
$categories = model('Category')->with('img')->select();
// 等价于
$categories = model('Category')->all([], 'img');
```

### 8-21 扩展：接口粒度与接口分层

1.减少首页 http 请求(api)的次数，从而减轻服务器的压力

2.接口粒度： 太粗=》代码复用性不好，不够灵活；太细=》需要发送的请求太多，不方便

3.架构师 =》 Api 接口设计 =》 底层设计力度比较小、灵活性比较高的 api 接口；越往上粒度逐渐变粗。

4.如果确实调用的接口比较多，应该在 api 基础数据层上建立业务层，再在业务层调用基础数据层相关的接口，再进行封装。

### 9-1 初识 Token - 意义与作用

说明：目前这种情况下，用户只要知道了系统的接口的形式，就可以直接访问，并获取数据，而大多数情况下，我们需要对用户身份进行验证，如：需要用户登录后才能访问的接口，以及需要管理员才能访问的接口等。

1.获取令牌

```info
客户端=》(账号、密码)=》getToken 《==》 账号、密码、Token、Auth
```

描述：客户端携带账号和密码信息，调用`getToken`接口，经过处理验证后，返回账号、密码、Token、Auth 等信息。

2.访问接口

```info
客户端=》(Token)=》下单接口 《==》 账号、密码、Token、Auth
```

验证：1.是否合法 2.是否有效 3.是否有操作权限

3.上面两个过程的 getToken 接口和下单接口就是被保护的接口，需要验证通过才能让用户访问。

### 9-2 微信登录流程

1.微信身份登录体系

![微信登录流程](https:raw.githubusercontent.com/zqunor/MarkdownPic/master/thinkphp5+小程序商城/9-2微信登录流程.png)

2.Token 在接口验证时的使用流程

![Token访问下单接口](https:raw.githubusercontent.com/zqunor/MarkdownPic/master/thinkphp5+小程序商城/9-2使用Token访问下单接口.png)

### 9-3 实现 Token 身份权限体系

1.获取 token 的请求使用 post 方法[安全性方面考虑]

2.将复杂的业务分层到`service`层[实现分层思想]

使用模型处理数据库 CRUD 相关的操作，对于不操作数据库的复杂业务，将其封装到 Service 目录下，实现分层处理的思想，Service 层是在 Model 层之上的业务层。

3.基础实现

1）控制器的定义

```php
// api/controller/v1/Token [注意命名空间]
public function getToken($code = '') {}
```

2）路由定义

```php
// route.php
Route::post('api/:version/token/user', 'api/:version.Token/getToken');
```

3）验证器校验

```php
// api/controller/v1/Token
(new TokenGet())->goCheck();
```

```php
// api/validate/TokenGet
protected $rule = [
    // 在验证器基类中定义isNotEmpty()方法
    'code' => 'require|isNotEmpty'
];

protected $message = [
    'code' => 'code必填！'
];
```

### 9-4/5/6/7 实现 Token 身份权限体系

1.获取微信生成的 code 码，并将其作为参数，传递给微信接口来获得 openid 和 access_token 等相关信息[openid/session_key]

```php
// api/controller/v1/Token
$userToken = new UserToken($code);
$token = $userToken->get();
```

**2.封装 Service 层，实现 Token 令牌的获取**[重点]

1） 配置微信小程序相关参数[app_id app_secret login_url]

2.1.1 在配置文件中设置微信小程序的相关参数

```php
// config/extra/wx.php
return [
    'app_id' => 'XXXXXXXXX',
    'app_secret' => 'XXXXXXXXX',
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" . "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code"
];
```

2.1.2 创建 Service 层的 UserToken 处理类，定义参数为私有属性

```php
// api/service/UserToken.php
namespace app\api\service;

use app\lib\exception\WechatException;
use app\lib\exception\TokenException;

class UserToken extends Token
{
    protected $code;
    protected $appid;
    protected $appSecret;
    protected $loginUrl;
}
```

2） 拼接参数，并使用 curl 模拟 http 请求微信服务器，并获取返回结果

```php
// api/service/UserToken.php
public function __construct($code)
{
    $this->code      = $code;
    $this->appid     = config('wx.app_id');
    $this->appSecret = config('wx.app_secret');
    $this->loginUrl  = sprintf(
        config('wx.login_url'),
        $this->appid, $this->appSecret, $this->code
    );
}

public function get()
{
    $result = curl_get($this->loginUrl);
}
```

在公共方法文件中定义 curl 模拟 http 请求的方法：

```php
// application/common.php
function curl_get($url, &$httpCode = 0)
{
    //1、初始化curl
    $curl = curl_init();

    //2、告诉curl,请求的地址
    curl_setopt($curl, CURLOPT_URL, $url);
    //3、将请求的数据返回，而不是直接输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

    $fileContents = curl_exec($curl); // 执行操作
    curl_close($curl); // 关键CURL会话

    return $fileContents; // 返回数据
}
```

3） 请求微信接口失败[微信内部错误/程序编写出错]的异常处理

```php
// api/service/UserToken.php get()
$wxResult = json_decode($result, true);

if (empty($wxResult)) {
    // 经验总结得：如果返回的结果为空[没有返回错误信息和错误代码]，则是微信服务器接口的问题，直接抛出异常一颗
    throw new \Exception('获取session_key及openID异常，微信内部错误');
} else {
    $loginFail = isset($wxResult['errcode']);
    // 程序传递的参数出错时，微信服务器会返回错误码和错误提示信息
    if ($loginFail) {
        $this->processLoginErr($wxResult);
    }
}
```

调用微信 Token 请求接口调用出错时的处理：

```php
// api/service/UserToken.php
private function processLoginErr($wxResult)
{
    throw new WechatException(
        [
            'msg'       => $wxResult['errmsg'],
            'errorCode' => $wxResult['errcode'],
        ]
    );
}
```

4） **成功获取微信接口返回数据后的操作[存储 openid、生成令牌、写入缓存、返回令牌]**

```php
// api/service/UserToken.php get()
return $this->grantToken($wxResult);
```

2.4.1 存储 openid

```php
// api/service/UserToken.php
private function grantToken($wxResult)
{
    $now = time();
    // 1.拿到openid
    $openid     = $wxResult['openid'];
    // $sessionKey = $wxResult['session_key'];

    // 2.查看数据库中该openid的记录是否已经存在[同一个用户的openid始终保持不变]
    $user = model('user')->getByOpenId($openid);

    // 3.如果存在，则不处理； 如果不存在，那么新增一条user记录
    if ($user) {
        $uid = $user->id;
    } else {
        $uid = $this->newUser($openid);
    }
}
```

根据 openid 查询是否已经存在该用户

```php
// api/model/User.php
public static function getByOpenId($openid)
{
    $user = self::where('openid', '=', $openid)->find();

    return $user;
}
```

创建用户

```php
// api/service/UserToken.php
private function newUser($openid)
{
    $user = model('user')->create([
       'openid' => $openid
    ]);

    return $user->id;
}
```

2.4.2 准备缓存数据(缓存的值)[微信返回数据(openid|session_key) + uid(用户服务器中保存的用户记录 id) + scope(用户权限，值越大，权限越高) ]

```php
// api/service/UserToken.php  grantToken()
// 4.生成令牌，准备缓存数据，写入缓存 [获取用户的相关信息]
// 4.1 准备缓存数据
$cachedValue = $this->prepareCachedValue($wxResult, $uid);
```

准备缓存数据值的方法[缓存的值]

```php
// api/service/UserToken.php
private function prepareCachedValue($wxResult, $uid)
{
    $cachedValue = $wxResult;
    $cachedValue['uid'] = $uid;
    $cachedValue['scope'] = 16; // 数值越大，权限越多

    return $cachedValue;
}
```

2.4.3 写入缓存[令牌+微信返回数据+有效期]

```php
// api/service/UserToken.php  grantToken()
// 4.2 写入缓存，并返回令牌
$token = $this->saveToCache($cachedValue);
```

2.4.3.1 生成令牌(缓存的键)[随机字符串+时间戳+盐]

```php
// 令牌是用户程序生成的随机字符串，与微信服务器无关
// api/service/UserToken.php  saveToCache()
$key = self::generateToken();
```

在服务器层构建 Token 基类，处理用户登录 Token 和后续的其他 Token 信息[service 下 UserToken 继承该基类]

```php
// api/service/Token.php
public static function generateToken()
{
    // 用三组字符串，进行md5加密 [加强安全性]
    // 1.32个字符组成一组随机字符串
    $randChars = getRandChar(32);
    // 2.时间戳
    $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
    // 3.盐
    $salt = config('secure.token_salt');

    return md5($randChars.$timestamp.$salt);
}
```

公共方法中定义生成指定长度的随机字符串

```php
// application/common.php
function getRandChar($length)
{
    $str    = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $max    = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}
```

创建安全配置文件[盐：随机字符串]

```php
// extra/secure.php
return [
    'token_salt' => 'E7epHZhrTfgQ'
];
```

2.4.3.2 配置文件中设置 cache 缓存的有效期

````php
创建安全配置文件[盐：随机字符串]

```php
// extra/setting.php
'token_expire_in' => 7200
````

2.4.3.3 创建缓存文件

```php
private function saveToCache($cachedValue)
{
    $key = self::generateToken();
    $value = json_encode($cachedValue);
    // 设置缓存失效时间
    $expire_in = config('setting.token_expire_in');

    $request = cache($key, $value, $expire_in);
    if (!$request) {
        // 令牌缓存出错
        throw new TokenException([
            'msg' => '服务器缓存异常',
            'errorCode' => 10005
        ]);
    }

    return $key;
}
```

2.4.4 返回令牌

```php
// api/service/UserToken.php  grantToken()
// 4.3 写入缓存，并返回令牌
return $token;
```

3.异常处理类

3.1 微信内部错误[直接抛出异常]

3.2 微信接口调用出错[微信相关异常处理类 WechatException]

```php
class WechatException extends BaseException
{
    public $code = 404;
    public $msg = '微信服务器接口调用失败';
    public $errorCode = 999;
}
```

3.3 缓存 Token 出错[Token 异常处理类 TokenException]

```php
class TokenException extends BaseException
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10001;
}
```

### 9-8 获取请求参数 code 并调用 PHP 接口[借助微信开发工具]

#### 1.微信开发者工具中配置：

    设置好app_key后，需要将 “详情” 中的 “不校验合法域名、web-view(业务域名)、TLS版本以及HTTPS证书” 勾选上（在本地测试，没有远程访问的服务器或远程服务器访问的域名没有https证书）

#### 2.小程序代码：

(1) 在 config 中定义 restUrl

```javascript
// Protoss/utils/config.js [设置本地测试的域名基地址]
Config.restUrl = "http://mypro.com/api/v1/";
```

(2)在登录方法中获取 code

```javascript
// 在小程序登录调用wx.login()方法中输出code，然后使用接口请求工具将code作为post请求的参数，进行调用

// Protoss/utils/token.js getTokenFromServer()
wx.login({
  success: function(res) {
    console.log("code: " + res.code);
  }
});
```

#### 3.请求 PHP 接口获取 Token

```javascript
// 引用使用es6的module引入和定义
// 全局变量以g_开头
// 私有函数以_开头

import { Config } from "config.js";

class Token {
  constructor() {
    this.tokenUrl = Config.restUrl + "token/user";
  }

  verify() {
    var token = wx.getStorageSync("token");
    if (!token) {
      this.getTokenFromServer();
    }
  }

  getTokenFromServer(callBack) {
    var that = this;
    wx.login({
      success: function(res) {
        console.log("code: " + res.code);
        wx.request({
          url: that.tokenUrl,
          method: "POST",
          data: {
            code: res.code
          },
          success: function(res) {
            console.log("token： " + res.data.token);
            wx.setStorageSync("token", res.data.token);
            callBack && callBack(res.data.token);
          }
        });
      }
    });
  }
}

export { Token };
```

**【补充说明】**：

(1) 需要调试时，将 XDEBUG 参数拼接到`this.tokenUrl`即可

(2) 如果没有输出 code, 需要关闭开发者工具后再重新启动，会自动调用该方法，并输出 code
[调用过生成的 token 已经被存储到浏览器的 Storage 中，便不会再调用 Token 请求接口，从而不产生 code]

### 9-9 商品详情接口

1.  定义控制器方法 getOne($id)

2.  定义路由 api/:version/product/:id

3.  模型类实现[隐藏部分字段、设置数据表关联、实现数据库查询]

        Product => properties => ProductProperty => 商品属性值[品名、口味、产地、保质期]
        Product => imgs => Image  => 商品主图
        ProductImage => imgs.imgUrl => Image => 商品详情图

4.  异常处理信息提示

```php
[
    'msg'       => '当前产品无详情',
    'errorCode' => 20001
]
```

### 9-10-1 路由变量规则

1.路由匹配规则在项目中的应用。

```php
Route::get('api/:version/product/recent', 'api/:version.Product/getRecent');
Route::get('api/:version/product/:id', 'api/:version.Product/getOne');
```

2.存在的问题

目前调用接口都不存在问题，但是当将`:id`行放到`recent`行之前后，在调用`recent`路由时，则会因为优先匹配`:id`对应的路由，
此时则会因为参数校验不通过而报错。

3.解决之道：

对路由匹配规则进行限定，设置变量规则，对于`:id`行，限定只有当参数为数值时才匹配到当前行。即设置 `$id`的变量规则

变量规则：为变量用正则的方式指定变量规则，弥补了动态变量无法限制具体的类型问题，并且支持全局规则设置。

4.代码实现[设置变量规则]

```php
Route::get('api/:version/product/:id', 'api/:version.Product/getOne', [], ['id'=>'\d+']);
```

### 9-10-2 路由分组

对路由配置文件中，具有相同路由前缀的路由归为同一路由组，例如：

对于几个对应产品信息的路由，

```php
Route::get('api/:version/product/recent', 'api/:version.Product/getRecent');
Route::get('api/:version/product/by_category', 'api/:version.Product/getAllInCategory');
Route::get('api/:version/product/:id', 'api/:version.Product/getOne');
```

可以分组到产品组路由下，

```php
// 闭包方式注册路由分组
Route::group('api/:version/product', function() {
    Route::get('recent', 'api/:version.Product/getRecent');
    Route::get('by_category', 'api/:version.Product/getAllInCategory');
    Route::get(':id', 'api/:version.Product/getOne', [], ['id' => '\d+']);
});
```

或者：

```php
// 数组方式注册路由分组
Route::group('api/:version/product', [
    'recent' => ['api/:version.Product/getRecent'],
    'by_category' => ['api/:version.Product/getAllInCategory'],
    ':id' => ['api/:version.Product/getOne', [], ['id' => '\d+']]
],['method' => 'get']);
```

路由分组的方式定义路由，执行的效率会比一般形式高一点。

【注】路由分组的公共路由定义时，不能在末尾加`/`，否则会报控制器不存在的错误

### 9-11 闭包函数构建查询器

1.完成的商品详情的数据信息格式为：

```info
{
"id": 11,
"name": "贵妃笑 100克",
"price": "0.01",
"stock": 994,
"main_img_url": "http://mypro.com/static/images/product-dryfruit-a@6.png",
"summary": null,
"img_id": 39,
"imgs":[
    {
        "id": 4,
        "order": 1,
        "img_url":{
            "url": "http://mypro.com/static/images/detail-1@1-dryfruit.png"
        }
    },
    {
        "id": 5,
        "order": 2,
        "img_url":{
            "url": "http://mypro.com/static/images/detail-2@1-dryfruit.png"
        }
    },
],
"properties":[
    {
        "id": 1,
        "name": "品名",
        "detail": "杨梅"
    },
]
}
```

2.问题：其中`imgs`的值为每个商品下的所有图片介绍，所以所有图片之间一定存在一定的顺序，其中`imgs`数组下的数据中存在`order`排序字段，如何对`imgs`的数据通过`order`进行排序？

3.【答】：使用闭包函数构建查询器【相当于拼接 sql】。

```php
$product = self::with([
        'imgs' => function($query) {
            $query->with(['imgUrl'])->order('order asc');
        }
    ])
    ->with(['properties'])
    ->find($id);
```

4.思路分析：

（1）要对 imgs 下的数据进行处理，需要获取到每组数据，然后对`order`字段进行排序。【通过闭包函数获取到每组数据】

（2）除了要对每组数据进行按`order`排序，还需要处理`img_url`。【通过 with 链式操作处理`img_url`】

5.关于闭包函数的理解：

```php
'imgs' => function($query) {
    $query->with(['imgUrl'])->order('order asc');
}
```

对于数组`imgs`，通过闭包函数，获取到每组数据，其中`$query`即作为参数接收每组数据的值，然后再对每组数据的`img_url`通过 with 进行数据关联。

### 9-12 用户收货地址

1.需求说明：

用户收货地址接口信息需要进行身份验证，登录用户只能查看和操作自己的地址信息，未登录用户不能访问。

为简化操作，当前将用户和用户地址的关联关系设定为一对一。

2.思考点：

（1）对登录状态的判断：

当用户访问小程序时，调用`wx.login()`方法，并生成`code`,后台接口拿到 code 后生成 token，并用 token 以及配置的`app_id`和`app_secret`请求微信接口，并获取微信返回的`openid`等信息，存储到缓存中
[以 token 为键，uid|wxResult|scope 组成的 json 数据为值]

所以，创建或修改用户地址信息时，在处理地址信息和用户信息的关联时，使用的用户信息，应当是当前登录用户的信息，而不能是客户端传递的用户信息参数[可能传递有误，导致误操作到其他用户的地址信息]

实现一定程度上的接口保护。

（2）传入参数的检验

验证器校验往往只能验证某个字段或某些字段的合法性，而客户端可能传入的参数比需要的参数多，或者传入了`uid`或者`user_id`，导致更新时覆盖了其他用户的数据信息，对系统的安全性造成影响，
所以，在接收客户端传入参数时，需要进行多余字段的过滤。

（3）对手机号的验证

正则表达式的应用场景，正则模式`^1(3|4|5|6|7|8)[0-9]\d{8}$^`

（4）**通过模型关联，实现用户地址的新增和更新**【新】

通过关联模型方法，创建数据

```php
// 新增
$user->address()->save($dataArray);
```

通过关联模型属性，对当前属性对应的记录进行更新

```php
// 更新
$user->address->save($dataArray);
```

（5）模型关联方法的选择：

模型关联方法的区分：

    有主键关联无主键 =》 belongsTo
    无主键关联有主键 =》 hasOne|hasMany

（6）HTTP 状态码

200：操作成功，服务器已成功处理了请求。说明：[如果是对您的 robots.txt 文件显示此状态码，则表示 Googlebot 已成功检索到该文件](https://blog.csdn.net/u014028956/article/details/47125403)

201：创建成功，表示服务器执行成功，并且创建了新的资源

设置接口调用成功后的状态码标识：

```php
return json(new SuccessMessage(), 201);
```

### 9-12-1 通过令牌获取用户标识

1.  定义控制器方法 `createOrUpdate()`

2.  定义路由 `api/:version/address`

3.  验证器验证用户输入数据 [`name`, `mobile`, `province`, `city`, `country`, `detail`]

4.  异常处理信息提示

当数据不合法时抛出异常，而当操作成功时，也需要返回相应的数据信息。当前项目将抛出的成功信息也放在异常处理类库下。

### 9-12-2 面向对象的方式封装获取 uid 方法

1.通过令牌 token 即可获取缓存中对应的用户信息，而缓存中的信息包括`uid` `scope` `wxResult`[`openid` `session_key`]

而在 http 请求时，token 保存在 header 头信息中，获取头信息中`token`的方法：

`$token = Request::instance()->header('token');`

2.通过 json 键值对的键，获取 cache 数据

`Cache::get($token)`

3.增强项目的扩展性，可将通过 token 获取变量的方法进行封装。

4.代码实现：

```php
public static function getCurrentTokenVar($key)
{
    $token = Request::instance()->header('token');
    $vars  = Cache::get($token);
    if ( ! $vars) {
        throw new TokenException();
    } else {
        if (!is_array($vars)) {
            $vars = json_decode($vars, true);
        }

        if (isset($vars[$key])) {
            return $vars[$key];
        } else {
            throw new Exception('尝试获取的Token变量不存在');
        }
    }
}

public static function getCurrentUid()
{
    $uid = self::getCurrentTokenVar('uid');
    return $uid;
}
```

### 9-12-3 模型新增和更新

通过用户模型，进行面向对象方式的新增和更新

（1）user 模型定义 address()关联方法，获取到用户地址信息，当用户地址信息不存在时，也通过**关联模型方法**，保存地址信息

```php
// 新增
$user->address()->save($dataArray);
```

（2）user 模型通过 address()关联方法关联 user_address 数据表中对应的用户地址信息，通过关联获取的数据仍然可以作为模型的属性值使用，
再通过**关联模型属性**，对当前属性对应的记录进行更新 [包含主键 id]

```php
// 更新
$user->address->save($dataArray);
```

### 9-12-4 参数过滤

封装处理客户端传入的参数的方法，由于当前用户的信息是通过缓存获取的，为避免用户传入的参数造成错误修改，所以需要对客户端传入数据进行过滤，
如果携带用户 id 参数，则抛出异常，不再继续处理。除此之外，对于传入的无效、多余数据，进行过滤，仅接收验证器需要验证的字段信息。

```php
public function getDataByRule($params)
{
    if (isset($params['uid']) || isset($params['user_id'])) {
        throw new ParameterException([
            'msg' => '参数中包含非法的参数名user_id或者uid'
        ]);
    }
    $newArray = [];
    foreach ($this->rule as $key => $value) {
        $newArray[$key] = $params[$key];
    }

    return $newArray;
}
```

### 9-12-5 接口测试

1.需要的参数

- token: header 请求头 [通过微信小程序的开发者工具]

- address 字段信息 [`name`, `mobile`, `province`, `city`, `country`, `detail`]

  2.返回的数据

```json
{
  "code": 201,
  "msg": "ok",
  "errorCode": 0
}
```

并且通过设置返回值为带状态码的 json 数据，`json(new SuccessMessage(), 201)`，可将 http 的状态码也设置为`201`

### 10-1 Scope 权限作用域的应用

该系统通过 Scope 的数值大小进行权限管理，但当前对权限直接使用数值定义，可读性不强，且不易维护，一旦修改，可能需要修改多处。

又因为各个权限对应的数值是不同的，而 php 本身没有枚举类型的语法，所以现在定义一个类，通过常量的方式赋予权限数值。

```php
namespace app\lib\enum;

class ScopeEnum
{
    const User = 16;
    const Super = 32;
}
```

在 scope 赋值时，使用：

```php
// api/service/UserToken.php prepareCachedValue()

//$cachedValue['scope'] = 16; // 数值越大，权限越多
$cachedValue['scope'] = ScopeEnum::User;
```

### 10-2 前置方法

1、使用 tp5 的前置方法之前，需要保证控制器继承了框架的基类控制器。

```php
protected $beforeActionList = [];
```

【注】：
（1）前置方法不能设置为 private 访问方式。
（2）设置前置关系的属性名：`$beforeActionList` [定义成其他名称则前置关系失效]

2、代码实现：

（1）基类控制器继承

```php
use think\Controller;
class Address extends Controller{}
```

（2）定义前置方法和访问的接口

```php
protected $beforeActionList = [
    'first' => ['only' => 'second']
];

// 前置方法
protected function first ()
{
    echo 'first';
}

// API接口
public function second()
{
    echo 'second';
}
```

（3）定义测试路由

`Route::get('api/:version/second', 'api/:version.Address/second');`

### 10-3 对 Address 接口做权限控制

1.前置方法设置不生效的解决【注意】：

对 10-2 中测试的前置方法生效，而对`createOrUpdate()`方法设置前置方法时不生效的原因：

> 默认情况下，URL 是不区分大小写的，也就是说 URL 里面的模块/控制器/操作名会自动转换为小写，控制器在最后调用的时候会转换为驼峰法处理。 [TP5 手册](https://www.kancloud.cn/manual/thinkphp5/118012)

所以，测试用例中使用小写的`second`方法，可以正常访问，而使用驼峰法命名的`createOrUpdate()`方法不生效。

所以，在设置前置方法时，需要将方法名写成小写才能生效。
即
`'checkPrimaryScope' => ['only'=>'createorupdate']`

2.前置方法进行权限控制

（1）定义前置关系：

```php
protected $beforeActionList = [
    'checkPrimaryScope' => ['only'=>'createorupdate']
];
```

（2）前置方法中实现权限控制

```php
protected function checkPrimaryScope ()
{
    $scope = Token::getCurrentTokenVar('scope');
    if (!$scope) {
        throw new TokenException();
    }
    if ($scope < ScopeEnum::User) {
        throw new ForbiddenException();
    }
}
```

### 10-4 下单与支付的业务流程（库存量检测）

业务流程分析：

```bash
1. 用户在选择商品后，向API提交包含它所选择商品的相关信息
2. API在接收到信息后，需要检查订单相关商品的库存量
3. 有库存，把订单数据存入数据库中 = 下单成功，返回客户端消息，告诉客户端可以支付了
4. 调用我们的支付接口，进行支付
5. 还需要再次进行库存量的检测 [下单与支付之间的时间间隙可能存在库存变化]
6. 库存充足 =》 服务器这边就可以调用微信的支付接口进行支付
7. 微信会返回给我们一个支付的结果（异步）
8. 成功：也需要进行库存量的检查
9. 成功：进行库存量的扣除
```

### 10-5 下单与支付详细流程

![](https://ws1.sinaimg.cn/large/005EgYNMgy1fu19tx686zj30s50e7q5y.jpg)

### 10-6 重构微信权限控制前置方法

下单接口不让管理员访问。

1.重构理由：

```bash
当前项目的大多数接口都需要进行权限校验，这样的话，按照原先的流程，对每个控制器都需要加前置操作的关联属性和前置操作方法，而现在需要的前置处理都是访问权限的控制，所以，可以将访问权限控制的前置操作方法定义到公共的控制器方法中，最好的处理方式是定义`BaseController`控制器，将所有控制器都需要调用的方法放到该控制器中，提高代码的可复用性。
```

2.代码实现：

（1）自定义公共的控制器

```php
// application/api/controller/BaseController.php
namespace app\api\controller;
use think\Controller;
class BaseController extends Controller{}
```

（2）让项目中其他控制器继承该控制器

（3）在公共控制器中定义前置操作方法

```php
// 用户和管理员都能访问
protected function checkPrimaryScope()
{
    TokenService::needPrimaryScope();
}
// 只有用户能访问，管理员不能访问的接口
protected function checkExclusiveScope()
{
    TokenService::needExclusiveScope();
}
```

（4）小程序访问权限的定义[是对 Token 的处理，故将该方法封装到 Service 层的 Token 下]

```php
// 需要用户和CMS管理员都可以访问的权限
public static function needPrimaryScope()
{
    $scope = self::getCurrentTokenVar('scope');
    if (!$scope) {
        throw new TokenException();
    }
    if ($scope < ScopeEnum::User) {
        throw new ForbiddenException();
    }
}

// 只有用户可以访问的权限
public static function needExclusiveScope()
{
    $scope = self::getCurrentTokenVar('scope');
    if (!$scope) {
        throw new TokenException();
    }
    if ($scope != ScopeEnum::User) {
        throw new ForbiddenException();
    }
}
```

（5）控制器方法中的前置关系定义

用户地址操作接口：

```php
// api/controller/v1/Address.php
protected $beforeActionList = [
    'checkPrimaryScope' => ['only' => 'createorupdate'],
];
```

用户下单接口：

```php
// api/controller/v1/Order.php
protected $beforeActionList = [
    'checkExclusiveScope' => ['only' => 'placeOrder'],
];
```

### 10-7 编写一个复杂的验证器

1.需求分析:

```bash
对下单接口提交的数据进行处理： 一个订单可以有多个商品，一个商品会有多个信息。 =》 提交的数据为二维数组
```

2.验证器验证提交的订单信息 [双层验证]

- 验证一： 对一个订单中的多个商品验证 =》 必须是数组

- 验证二： 对一个订单中的一个商品的所有信息进行验证 =》 `product_id` `count`[必须是正整数]

  3.实现方式：

(1) 第一层验证的实现：验证器自动验证 [自定义验证方法]

验证规则：

```php
protected $rule = [
    'products' => 'require|checkProducts',
];
```

验证方法：

```php
protected function checkProducts($dataLists)
{
    if (!$dataLists) {
        throw new ParameterException([
            'msg' => '商品列表不能为空',
        ]);
    }

    if (!is_array($dataLists)) {
        throw new ParameterException([
            'msg' => '商品参数不正确',
        ]);
    }

    // 对具体的每个商品信息进行验证
    foreach ($dataLists as $key => $data) {
        $this->checkProduct($data);
    }

    return true;
}
```

(2) 第二层验证的实现：手动验证 [嵌套于第一层验证中]

验证规则：

```php
protected $singleRule = [
    'product_id' => 'require|isPositiveInteger',
    'count' => 'require|isPositiveInteger',
];
```

验证方法[手动传递验证规则进行验证]：

```php
protected function checkProduct($product)
{
    $validate = new BaseValidate($this->singleRule);
    $checkResult = $validate->batch()->check($product);
    if (!$checkResult) {
        throw new ParameterException([
            'msg' => '商品信息参数错误',
        ]);
    }
}
```

4.控制器调用验证器：

```php
// api/controller/v1/Order.php placeOrder()
(new OrderPlace())->goCheck();
```

### 10-8|9 下单接口模型

1.在 `service` 层中创建`Order`类，处理订单服务相关的操作。

(1) 定义属性

```php
// 用户提交的订单商品信息
protected $oProducts;
// 根据用户提交的商品信息，查询到数据库中相应商品的信息(库存量)
protected $products;
// 用户id
protected $uid;
```

(2) 定义功能方法

```php
public function place($uid, $oProducts){}
```

(3) 定义业务方法

```php
// 根据订单信息查找真实的商品信息 [id name price stock main_img_url]
private function getProductsByOrder($oProducts)
```

```php
// 获取订单中每个商品信息的状态 [id haveStock count name totalPrice]
private function getProductStatus($oPId, $count, $products)
```

```php
// 获取订单状态 [pass orderPrice pStatusArray]
private function getOrderStatus(){}
```

2.根据订单信息查找真实的商品信息 [id name price stock main_img_url]

```php
private function getProductsByOrder($oProducts)
{
    if (!is_array($oProducts)) {
        throw new ParameterException([
            'msg' => '商品列表参数错误',
        ]);
    }

    // 根据用户传入的所有订单商品的 product_id
    $oPIds = array_column($oProducts, 'product_id');

    // 根据商品id信息查询数据库，获得数据库中对应的商品信息
    $products = Product::all($oPIds)->visible(['id', 'name', 'price', 'stock', 'main_img_url'])->toArray();

    return $products;
}
```

模型类方法用法：

- [all()](https://www.kancloud.cn/manual/thinkphp5/135191)

参数没有使用索引就是查询主键为参数的所有结果; 有索引则是查询相应字段符合条件的所有结果 [返回结果为二维数组]

- [visible()](https://www.kancloud.cn/manual/thinkphp5/138667)

设置只显示的字段信息

- toArray()

针对数据集对象有效，[默认的数据集返回结果的类型是一个数组,可以配置默认返回结果的类型]

```php
// config.php文件中配置数据集返回类型
'resultset_type' => 'collection',
```

3.获取订单中每个商品信息的状态 [id haveStock count name totalPrice]

- id 记录商品的 id
- haveStock 记录商品是否有库存
- count 记录商品的购买数量
- totalPrice 记录某个商品的总价

```php
private function getProductStatus($oPId, $count, $products)
{
    // 当前处理的商品对应获取的数据库商品列表中的索引值
    $pIndex = -1;
    // 需要记录的商品信息
    $pStatus = [
        'id' => null,
        'haveStock' => false,
        'count' => 0,
        'name' => '',
        'totalPrice' => 0,
    ];

    // 获取索引值
    for ($i = 0; $i < count($products); $i++) {
        if ($products[$i]['id'] == $oPId) {
            $pIndex = $i;
        }
    }

    // 不存在时，抛出异常信息； 存在时，则处理需要的数据信息，并返回
    if ($pIndex == -1) {
        // 客户端传递的product_id有可能根本不存在
        throw new OrderException([
            'msg' => 'id为' . $oPId . '的商品不存在，创建订单失败',
        ]);
    } else {
        $product = $products[$pIndex];
        $pStatus['id'] = $oPId;
        $pStatus['count'] = $count;
        $pStatus['name'] = $products[$pIndex]['name'];
        $pStatus['totalPrice'] = $products[$pIndex]['price'] * $oCount;
        $pStatus['haveStock'] = ($product[$pIndex]['stock'] >= $oCount) ? true : false;
    }

    return $pStatus;
}
```

4.获取订单状态 [pass orderPrice pStatusArray]

- pass 记录该订单是否可以下单成功 [库存量验证]
- orderPrice 订单总价
- pStatusArray 订单中所有商品和所有商品的详细信息 [用户历史订单]

```php
private function getOrderStatus()
{
    $status = [
        'pass' => true,
        'orderPrice' => 0,
        'pStatusArray' => [], //订单商品的详细信息
    ];

    foreach ($this->oProducts as $key => $oProduct) {
        $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
        $status['pass'] = $pStatus['haveStock'];
        $status['orderPrice'] += $pStatus['totalPrice'];

        array_push($status['pStatusArray'], $pStatus);
    }

    return $status;
}
```

### 10-10 订单快照

需要对每个订单的详细信息进行记录，包括地址信息、订单的商品信息等。

#### 1.订单商品信息 `order_product`

(1) 存储订单的商品信息:

`order_id product_id count delete_time update_time`

(2) 订单和订单商品的对应关系： `多对多`

#### 2.订单表信息 `order`

1.记录订单的地址信息(快照) [`snap_address`]

```bash
用户的地址信息可能会改变，如果通过关联进行动态查询的话，可能实际收货信息与获取的信息不相同。所以直接存储地址快照信息。
```

2.记录订单的商品信息(快照) [`snap_items` `snap_img`]

```bash
由于商品的价格和img等信息会动态变化，如果不另行记录，以后查询时，可能会出现订单和商品信息之间的误差
```

3.订单的支付信息 [`status` `prepay_id`]

4.订单信息 [编号`order_no`、订单快照名称`snap_name`、数量`total_count`、价格`total_price`、操作时间`create_time`|`update_time`|`delete_time`]

5.订单的用户的信息 [`user_id`]

### 10-11 订单快照的实现

1.生成订单快照方法的定义

```php
private function snapOrder($status){}
```

【调整】：订单状态的数据结构[添加对订单商品总数量的记录]：

```php
private function getOrderStatus(){}
```

- 添加`totalCount`信息

```php
$status = [
    'totalCount' => 0, // 订单商品的总数量，不是商品种类的数量
];
```

- `totalCount`的数据梳理

```php
$status['totalCount'] += $oProduct['count'];
```

2.订单快照的数据结构

```php
$snap = [
    // 订单总价
    'orderPrice' => 0,
    // 订单商品总数量
    'totalCount' => 0,
    // 商品的信息[id haveStock count name totalPrice]
    'pStatus' => [],
    // 下单时的地址信息
    'snapAddress' => null,
    // 历史订单页显示的订单名称 [仅显示一个订单下的第一个商品，而不是所有商品，多个商品加'等'字]
    'snapName' => '',
    // 历史订单页显示的订单图片 [第一个商品的图片]
    'snapImg' => '',
];
```

3.数据结构的数据获取

```php
$snap['orderPrice'] = $status['orderPrice'];
$snap['totalCount'] = $status['totalCount'];
$snap['pStatus'] = $status['pStatusArray'];
$snap['snapAddress'] = json_encode($this->getUserAddress());
$snap['snapName'] = $this->products[0]['name'];
$snap['snapImg'] = $this->products[0]['main_img_url'];
// 当订单商品数量大于1时，给订单快照名称添加'等'字
if (count($this->products) > 1) {
    $snap['snapName'] .= '等';
}
```

4.获取用户下单时的收货地址 [将数据集对象转化为数组`toArray()`]

```php
public function getUserAddress()
{
    $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();

    if (!$userAddress) {
        throw new UserException([
            'msg' => '用户收货地址不存在，下单失败',
            'errorCode' => 60001,
        ]);
    }

    return $userAddress->toArray();
}
```

### 10-12 订单创建

1.【调整】

数组索引名的命名方式： `驼峰法` 转为 `下划线法`

2.生成订单编号方法

```php
public function makeOrderNo()
{
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $orderSn = $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m')))
                . date('d') . substr(time(), -5)
                . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));

    return $orderSn;
}
```

3.定义处理订单信息的方法

```php
private function createOrder($snap){}
```

4.处理订单信息并保存到订单表 [order 表]

```php
$order = new OrderModel();

$orderNo = $this->makeOrderNo();
$order->order_no = $orderNo;
$order->user_id = $this->uid;
$order->total_price = $snap['order_price'];
$order->total_count = $snap['total_count'];
$order->snap_items = $snap['p_status'];
$order->snap_address = $snap['snap_address'];
$order->snap_img = $snap['snap_img'];

$order->save();
```

5.通过模型对象获取属性值

```php
$orderId = $order->id;
$orderCreateTime = $order->create_time;
```

6.处理订单商品信息并保存到订单-商品表 [`order_product`]

```php
foreach ($this->oProducts as &$op) {
    $op['order_id'] = $orderId;
}

$orderProduct = new OrderProduct();
$orderProduct->saveAll($this->oProducts);
```

通过引用传值，得到`$this->oProducts`的结构为：

```bash
[
    [
        'product_id' => '',
        'count' => '',
        'order_id' => ''
    ],
    [
        'product_id' => '',
        'count' => '',
        'order_id' => ''
    ],
]
```

7.对数据表的操作进行`try-catch`异常捕获

8.调用创建订单方法

```php
// api/service/Order place()
$order = $this->createOrder($orderSnap);
```

### 10-13 一对多关系的新增操作

一对多模型关系的实现分两个过程:

```bash
先保存一 [新增一条记录]
再保存多 [新增关联表的多条记录]
```

1.完成 service 层下单方法的调用和结果处理

```php
// api/service/Orde.php place()
// status中的pass记录的是库存量是否检测通过的信息
$orderSnap = $this->snapOrder($status);
$order = $this->createOrder($orderSnap);
// 告诉客户端订单是够通过
$order['pass'] = true;

return $order;
```

2.完成控制器中的 api 接口方法调用

```php
// api/controller/v1/Order.php placeOrder()
$order = new OrderService();
$status = $order->place($uid, $oProducts);

return $status;
```

### 10-14 测试订单接口

1.调整几处细节错误：

- 验证器类的引入 [`use app\api\validate\OrderPlace;`]

- $oCount 变量名 [`$count` => `$oCount`]

- `have_stock`数组索引名的规范 [`$pStatus['haveStock']` => `$pStatus['have_stock']`]

- $products 数组元素调用时变量名 [`$product` => `$products`]

- 遗漏快照名称的赋值

```php
// api/controller/v1/Order.php createOrder()
$order->snap_name = $snap['snap_name'];
```

  2.测试接口成功 [成功创建订单信息和订单商品关联信息]

- 请求参数：

```json
{
  "products": [
    {
      "product_id": 1,
      "count": 4
    },
    {
      "product_id": 2,
      "count": 4
    }
  ]
}
```

- 返回结果：

```json
{
  "order_no": "B813476849200914",
  "order_id": "7",
  "create_time": "1970-01-01 08:00:00",
  "pass": true
}
```

3.测试库存不足时的情况 [将商品表中相应商品的库存手动设为 0]

※ **【找到系统中存在的 bug】**：

（1）问题描述：

```bash
    当一个订单中有多个商品时，第一个商品的库存充足，最后一个商品不充足时，可以正常判断为下单失败

    当前面的商品库存不充足，最后一个商品的库存充足时，应该判断为下单失败，但是实际却是下单成功。
```

（2）找到问题：

```php
// api/service/Order.php
private function getOrderStatus()
{
    // ...
    foreach ($this->oProducts as $key => $oProduct) {
        $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
        // 原来的处理是
        // $status['pass'] = $pStatus['have_stock'];
        // 这将导致之前库存不足的结果被后面库存充足时的值覆盖，造成误判

        // 此处修改为：
        if (!$pStatus['have_stock']) {
            $status['pass'] = false;
        }
        // 由于初始化时该值为true, 所以如果有库存不足的情况就将该值置为false，而不是每次都赋值

        //...
    }

    return $status;
}
```

### 10-15 自动写入时间戳

1.需求分析：

    很多模型对应的数据表都需要记录操作的时间并写入时间戳，这样如果手动操作的话步骤比较繁琐，所以可以使用TP5框架自动写入时间戳的功能。

2.实现方法：

（1）局部实现 [每个模型类中定义属性`$autoWriteTimestamp`]

```php
protected $autoWriteTimestamp = true;
```

（2）全局实现 [数据库配置文件]

```php
// 开启自动写入时间戳字段
'auto_timestamp' => true,
```

### 10-16 在 tp5 中使用事务

当需要实现某个功能，需要同时操作多个数据表时，可能出现某个数据表的操作执行成功，但有些数据表的执行操作出现异常，而没有成功操作到数据表，这种情况下，会导致数据出现不一致性。事务可以保证数据库操作的一致性，即一个功能执行操作数据库的多个操作时，如果成功则全部成功并执行，如果有执行出错的操作，则全部不执行。

1.开启事务

```php
Db::startTrans();
```

开启后需要对数据库操作进行异常捕获，如果一切正常，则正常执行事务中的所有操作；如果发生异常，则回滚事务，撤销事务中的所有操作。

2.提交事务 [执行正常，提交所有执行]

```php
Db::commit();
```

3.事务回滚 [执行异常，撤销所有执行]

```php
Db::rollback();
```