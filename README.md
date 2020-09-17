# laravele-doc安装
> - #### 写文档非常浪费后端同学的时间
>
> - #### 自动读取 request 的验证规则和方法注释作为请求参数
>
> - #### 读取attributes,validation.php,数据库字段字段作为注释
>
> - #### 提供中间件保存返回会自动的生成相关文档

1. 安装

   ```shell
   composer require faed/laravle-doc
   ```

2. 发布配置

   ```shell
   php artisan vendor:publish
   ```

3. 配置

   ```php
   return [
       //版本
       'v'=>1,
       //名称
       'name'=>env('APP_NAME'),
       //app名称
       'app_name'=>env('APP_NAME'),
       //请求地址
       'path'=>env('APP_URL'),
       //接口地址发送地址
       'send'=>'http://127.0.0.1:8000',
   	//路由过滤
       'only'=>'api',
       //laravle版本 7.8路由方式不一样默认８请自行定义
       'laravle_versions'=>8,
   	//自动读取数据库的字段注释作为注释，多库请自行定义
       'mysql' => ['mysql'],
   ];
   
   ```

4. 运行迁移文件生成相关的表

   ```shell
   php artisan migrate
   ```

5. 运行 自动生成相关的文档数据路由　http://xxxxx/doc

   ```she
   php artisan api:make
   ```



# 使用

1. http://xxxxx/doc 路由

![](http://119.28.55.169/nav.png)

![](http://119.28.55.169/doc.png)

2. 提供中间件[ RecordReturn ]记录返回数据,请自行添加

   ```php
      [
           'throttle:api',
           \Illuminate\Routing\Middleware\SubstituteBindings::class,
           RecordReturn::class,
      ];
   ```

   ![](http://119.28.55.169/return.png)

3. 读取数据库时需要的时间可能较长提供参数选择

   ```shell
   php artisan api:make -MC
   ```

> ps:可以作为一个项目多个版本的管理,或者单独布置多个项目接口管理
