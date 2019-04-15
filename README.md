### 目录TOC

+ [数据卷 volume](#数据卷-volume)
+ [端口设置 ports](#端口设置-ports)
+ [环境变量设置 environment](#环境变量设置-environment)

### 

### 数据卷 volume

数据卷 是一个可供一个或多个容器使用的特殊目录，它绕过 UFS，可以提供很多有用的特性：

+ 数据卷 可以在容器之间共享和重用
+ 对 数据卷 的修改会立马生效
+ 对 数据卷 的更新，不会影响镜像
+ 数据卷 默认会一直存在，即使容器被删除

**数据库使用的数据卷**

```yaml
volumes:
  - db_data:/var/lib/mysql
```

**wordpress 使用的数据卷**

```yaml
volumes:
  - ./wordpress:/var/www/html
  - ./favicon.ico:/var/www/html/favicon.ico
  - ./uploads.ini:/usr/local/etc/php/conf.d/uploads.ini
```

[返回目录 :arrow_heading_up:](#目录TOC)

### 端口设置 ports

**数据库端口**

MySql 默认端口 3306

**wordpress 端口**

```yaml
ports:
  - "8000:80"
```

#### Nginx 反向代理

如果需要为 wordpress 网站设置域名，则可以使用下面配置

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



[返回目录 :arrow_heading_up:](#目录TOC)

### 一些信息

+ wordpress 程序默认的安装路径是：`/var/www/html`。
+ php 版本：PHP 7.2.15
+ php 默认开启 Opcache

[返回目录 :arrow_heading_up:](#目录TOC)

### 环境变量设置 environment

**数据库环境变量**

```yaml
environment:
  MYSQL_ROOT_PASSWORD: somewordpress
  MYSQL_DATABASE: wordpress
  MYSQL_USER: wordpress
  MYSQL_PASSWORD: wordpress
```

**wordpress环境变量**

```yaml
environment:
  WORDPRESS_DB_HOST: db:3306
  WORDPRESS_DB_USER: wordpress
  WORDPRESS_DB_PASSWORD: wordpress
```

[返回目录 :arrow_heading_up:](#目录TOC)

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


### 参考

[Docker — 从入门到实践·语雀](https://www.yuque.com/grasilife/docker)
