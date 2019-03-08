
## 必读
#### 一些信息

+ wordpress 程序默认的安装路径是：`/var/www/html`。
+ php 版本：PHP 7.2.15
+ php 默认开启 Opcache

#### 设置 wp-content 目录读写权限

```bash
chmod -R 777 wp-content
```

#### 安装插件需要 FTP
默认情况下，wordpress 安装插件需要 FTP 服务，通过给 `wp-config.php` 文件添加以下代码的方法可以避免这个问题：

```php
define("FS_METHOD","direct");
define("FS_CHMOD_DIR", 0777);
define("FS_CHMOD_FILE", 0777);
```

然后，设置目录权限

```bash
chmod -R 777 wp-content
```

#### Nginx 反向代理

```conf
server {
  listen      80;
  server_name docker.wencaizhang.com;

  client_max_body_size    20m;

  location / {
    proxy_pass http://localhost:8000;
    proxy_set_header Host $host;
    proxy_set_header X-Forward-For $remote_addr;
  }
}
```

#### 一些命令

启动
```bash
docker-compose up -d
```

停止
```bash
docker-compose stop
```

#### 代码变更

git status显示修改了大量文件，git diff提示filemode变化，如下：

```
old mode 100644
new mode 100755
```

原来是filemode的变化，文件chmod后其文件某些位是改变了的，如果严格的比较原文件和chmod后的文件，两者是有区别的，但是源代码通常只关心文本内容，因此chmod产生的变化应该忽略，所以设置一下：

切到源码的根目录下，
```
git config --add core.filemode false
```
这样你的所有的git库都会忽略filemode变更了～