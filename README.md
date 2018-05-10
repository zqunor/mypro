# ThinkPHP 5.0 入门

## 详细开发文档参考 [ThinkPHP5 完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

# 框架篇

## 一、命名规范：

### 下划线法：

* 函数的命名
* 配置参数
* 常量（大写）
* 数据表和字段

### 驼峰法：

* 属性的命名
* 方法的命名

### 帕斯卡法：

* 类名
* 类文件名
* 类的命名

---

# 控制器篇

## 一、控制器访问

### 1、命名空间

命名空间与目录路径对应。

如：路径位置为：`application/index/controller/Index.php`
其文件的命名空间应为：`app\index\controller`

> 命名空间解释：
>
> * `app`对应`application`目录（在入口文件`mypro/public/index.php`中定义的，可根据需求自定义修改）
> * `index`对应`index`模块
> * `controller`对应`控制器`位置

### 2、定义类

类名直接使用该控制器名即可，不需要用 Controller 结尾

> 如：当前控制器类为`User`控制器，则直接定义该类名为`User`即可。

### 3、浏览器访问控制器的方法（操作）

TP5 的[路由访问方式][1]采用`PATH_INFO`进行地址访问，不再支持普通模式的 URL 访问。

如果定义了在`index`模块的`index`控制器中定义了方法名为`test`的方法，那么在浏览器中的访问 url 应为
`http://mypro.com/index.php/index/index/test`
（此处将项目名配置了虚拟域名`mypro.com`）

---

# 模型层篇

## 一、操作数据库

### 1、数据库连接配置

数据库默认的相关配置在项目的`application\database.php`中已经定义好。只需要在**模块的数据库配置文件**中配置好当前模块需要连接的数据库的配置参数即可。

> 模块的数据库配置文件的路径为：`application/index/database.php`

配置参数 如：**数据库名称**和**端口号**

```php
return [
    // 数据库名
    'database'    => 'test',
	// 端口
    'hostport'    => 3306
];
```

### 2、查看数据库配置详情

打印`config('database')`即可查看所有配置

### 3、[连接数据库][2]

```php
$res = Db::connect();
```

**注意：**

> * 需要在文件头引入`Db`类。引入方式为：`use think\Db;`
> * TP5 是憜性加载，即此时虽然已经可以查看到连接数据库的参数信息，但即使配置参数有问题（如数据库不存在）时不会有错误提示。

### 4、查询数据

(1)运行原生 SQL 语句

* [query()查询][3]
  > 支持**参数绑定**

```php
Db::query('select * from think_user where id=?',[8]);
```

> 支持**命名占位符绑定**

```php
Db::query('select * from think_user where id=:id',['id'=>8]);
```

> 支持**多个数据库连接**

```php
Db::connect($config)->query('select * from think_user where id=:id',['id'=>8]);
```

(2)[查询构造器][4]

* 查询一条数据（结果不存在时，返回**null**）

```php
Db::table('think_user')->where('status',1)->find();
```

> 【定义了数据表前缀】

```php
Db::name('user')->where('status',1)->find();
```

> 【助手函数：默认每次都会重新连接数据库】

```php
db('user')->where('status',1)->find();
```

> 【助手函数：使用第三个参数进行单例化，使得每次使用不再重新连接数据库】

```php
db('user',[],false)->where('status',1)->find(); 【助手函数：使用第三个参数进行单例化，使得每次使用不再重新连接数据库】
```

> 【使用查询对象进行查询】

```php
$query = new \think\db\Query();
$query->table('think_user')->where('status',1);
Db::find($query);  
```

> 【直接使用闭包函数】

```php
Db::find(function($query){
    $query->table('think_user')->where('status',1);
});
```

* 查询多条数据（结果不存在时，返回**空数组**）

```php
Db::table('think_user')->where('status',1)->select();
```

> 【定义了数据表前缀】

```php
Db::name('user')->where('status',1)->select();
```

> 【助手函数：默认每次都会重新连接数据库】

```php
db('user')->where('status',1)->select();
```

> 【助手函数：使用第三个参数进行单例化，使得每次使用不再重新连接数据库】

```php
db('user',[],false)->where('status',1)->select(); 【助手函数：使用第三个参数进行单例化，使得每次使用不再重新连接数据库】
```

> 【使用查询对象进行查询】

```php
$query = new \think\db\Query();
$query->table('think_user')->where('status',1);
Db::select($query);  
```

> 【直接使用闭包函数】

```php
Db::select(function($query){
    $query->table('think_user')->where('status',1);
});
```

* 查询某个字段的值

```php
Db::table('think_user')->where('id',1)->value('name');
```

* 查询某一列的值

```php
Db::table('think_user')->where('status',1)->column('name');
```

* **数据集分批处理**

* **JSON 类型数据查询**

### 5、添加数据

* 添加一条数据`insert()`----添加成功返回 1

```php
$data = ['foo' => 'bar', 'bar' => 'foo'];
Db::table('think_user')->insert($data);
```

* 添加多条数据`insertAll()`----添加成功返回添加成功的记录条数

```php
$data = [
    ['foo' => 'bar', 'bar' => 'foo'],
    ['foo' => 'bar1', 'bar' => 'foo1'],
    ['foo' => 'bar2', 'bar' => 'foo2']
];
Db::name('user')->insertAll($data);
```

\*助手函数

```php
// 添加单条数据
db('user')->insert($data);

// 添加多条数据
db('user')->insertAll($list);
```

\*快捷更新(V5.0.5+)

```php
Db::table('data')
    ->data(['name'=>'tp','score'=>1000])
    ->insert();
```

---

# 模板篇

## 一、模板访问

### 1、没有参数传递

```php
$view = new View();
return $view->fetch();
```

此时默认访问的模板路径为：`[模板文件目录]/当前控制器名（小写+下划线）/当前操作名（小写）.html`

### 2、指定模板（跨模板）

```php
$view = new View();
return $view->fetch('add');
```

此时访问的模板路径为：`[模板文件目录]/当前控制器名（小写+下划线）/add.html`

### 3、指定某个控制器的某个模板(跨控制器)

```php
$view = new View();
return $view->fetch('user/add');
```

此时访问的模板路径为：`[模板文件目录]/user/add.html`

### 4、指定某个模块的某个控制器的某个模板（跨模块）

```php
$view = new View();
return $view->fetch('admin@user/add');
```

### 5、全路径模板调用

```php
$view = new View();
return $view->fetch(APP_PATH.request()->module().'/view/public/header.html');
```

## 二、模板继承

### 1、定义基础模板

(基础模板路径：`mypro/application/index/view/index/base.html`)
在基础模板中定义好需要设置的子模板名称。

子模板定义方式：

```php
<block name="子模板名称1">这是默认显示的内容</block>
```

### 2、在子模板中引入基础模板

（子模板路径：`mypro/application/index/view/index/index.html`）

引入方式：

```php
{extend name="index/base" /}
```

注：`name`是相对于`application`开始的

### 3、定义子模板中的内容

定义方式：

```php
<block name="子模板名称1">这是自定义该子模板需要显示的内容</block>
```

## 三、模板引擎时间函数

```php
{$c.create_time|date="Y-m-d H:i:s",###}
```

# 异常处理篇







[1]: https://www.kancloud.cn/manual/thinkphp5/118012
[2]: https://www.kancloud.cn/manual/thinkphp5/118059
[3]: https://www.kancloud.cn/manual/thinkphp5/118060
[4]: https://www.kancloud.cn/manual/thinkphp5/135176
