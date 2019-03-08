
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