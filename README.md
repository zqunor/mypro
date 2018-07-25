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

使用模型处理数据库CRUD相关的操作，对于不操作数据库的复杂业务，将其封装到Service目录下，实现分层处理的思想，Service层是在Model层之上的业务层。


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

1.获取微信生成的code码，并将其作为参数，传递给微信接口来获得openid和access_token等相关信息[openid/session_key]

```php
// api/controller/v1/Token 
$userToken = new UserToken($code);
$token = $userToken->get();
```

**2.封装Service层，实现Token令牌的获取**[重点]

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

2.1.2 创建Service层的UserToken处理类，定义参数为私有属性

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


2） 拼接参数，并使用curl模拟http请求微信服务器，并获取返回结果

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

在公共方法文件中定义curl模拟http请求的方法：

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

调用微信Token请求接口调用出错时的处理：

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

4） **成功获取微信接口返回数据后的操作[存储openid、生成令牌、写入缓存、返回令牌]**

```php
// api/service/UserToken.php get()
return $this->grantToken($wxResult);
```

2.4.1 存储openid

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

根据openid查询是否已经存在该用户

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

2.4.2 准备缓存数据(缓存的值)[微信返回数据(openid|session_key) + uid(用户服务器中保存的用户记录id) + scope(用户权限，值越大，权限越高) ]

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

在服务器层构建Token基类，处理用户登录Token和后续的其他Token信息[service下UserToken继承该基类]

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

2.4.3.2 配置文件中设置cache缓存的有效期

```php

创建安全配置文件[盐：随机字符串]

```php
// extra/setting.php
'token_expire_in' => 7200
```

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

3.2 微信接口调用出错[微信相关异常处理类WechatException]

```php
class WechatException extends BaseException
{
    public $code = 404;
    public $msg = '微信服务器接口调用失败';
    public $errorCode = 999;
}
```

3.3 缓存Token出错[Token异常处理类TokenException]

```php
class TokenException extends BaseException
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10001;
}
```

### 9-8 补充：关于请求参数code的获取[借助微信开发工具]

#### 1.微信开发者工具中配置：

    设置好app_key后，需要将 “详情” 中的 “不校验合法域名、web-view(业务域名)、TLS版本以及HTTPS证书” 勾选上（在本地测试，没有远程访问的服务器或远程服务器访问的域名没有https证书）

#### 2.小程序代码：

(1) 在config中定义restUrl

```javascript
// Protoss/utils/config.js [设置本地测试的域名基地址]
Config.restUrl = 'http://mypro.com/api/v1/';
```

(2)在登录方法中获取code

```javascript
// 在小程序登录调用wx.login()方法中输出code，然后使用接口请求工具将code作为post请求的参数，进行调用

// Protoss/utils/token.js getTokenFromServer()
wx.login({
  success: function (res) {
    console.log("code: " + res.code);
  }
})
```

> 【注】：如果没有输出code, 需要关闭开发者工具后再重新启动，会自动调用该方法，并输出code