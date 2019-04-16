### 目录 TOC

+ [简介 Intro](#简介-Intro)
+ [数据卷 volume](#数据卷-volume)
+ [端口设置 ports](#端口设置-ports)
+ [环境变量设置 environment](#环境变量设置-environment)
+ [常用命令 Command](#常用命令-Command)

### 简介 Intro

一个 wordpress 网站需要 Mysql 数据库、 wordpress 程序和 Nginx/Apache 服务器才能运行起来，使用 Docker 仅需要两个镜像即可：

+ mysql:5.7（配置：Debian 系统、Mysql/5.7.25)
+ wordpress:latest（配置：Debian 系统、Apache/2.4.25 、PHP/7.2.15)

然后使用 docker-compose 将这两个服务关联成一个项目。

>Compose 中有两个重要的概念：
>+ 服务 (service)：一个应用的容器，实际上可以包括若干运行相同镜像的容器实例。
>+ 项目 (project)：由一组关联的应用容器组成的一个完整业务单元，在 docker-compose.yml 文件中定义。

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

[返回目录 :arrow_heading_up:](#目录-TOC)

### 端口设置 ports

**数据库端口**

MySql 默认端口 3306

**wordpress 端口**

映射服务器端口到容器内部的端口，假设服务器ip地址是 192.168.1.0 的话，我们可以通过 192.186.1.0:8000 访问 container 的 80 端口

```yaml
ports:
  - "8000:80"
```

#### Nginx 反向代理

如果需要为 wordpress 网站设置域名，则可以使用下面配置。`proxy_pass` 对应的 8000 端口要和 wordpress 容器向外映射的端口保持一致。

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

[返回目录 :arrow_heading_up:](#目录-TOC)

### 一些信息

+ wordpress 程序默认的安装路径是：`/var/www/html`。
+ php 版本：PHP 7.2.15
+ php 默认开启 Opcache

[返回目录 :arrow_heading_up:](#目录-TOC)

### 环境变量设置 environment

环境变量 environment 就是用户需要设置的内容。

**数据库环境变量**

在 mysql 容器中，我们需要定义 mysql 中 root 用户的密码、为 wordpress 创建的数据库名称、mysql的用户名还有密码。

```yaml
environment:
  MYSQL_ROOT_PASSWORD: somewordpress
  MYSQL_DATABASE: wordpress
  MYSQL_USER: wordpress
  MYSQL_PASSWORD: wordpress
```

**wordpress环境变量**

在 wordpress 容器中，我们需要定义 wordpress 程序需要关联的 mysql 主机和端口、mysql 用户名和密码。

```yaml
environment:
  WORDPRESS_DB_HOST: db:3306
  WORDPRESS_DB_USER: wordpress
  WORDPRESS_DB_PASSWORD: wordpress
```

[返回目录 :arrow_heading_up:](#目录-TOC)

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



### 常用命令 Command

#### 镜像相关命令

+ 列出所有镜像

```bash
docker image ls
```

+ 删除镜像

```bash
docker image rm <image-name>
```

#### 容器相关命令

+ 基于镜像新建一个容器并启动



#### Compose 相关命令

Compose 命令都是针对项目的，因此需要在项目目录下也就是 docker-compose.yml 文件所在目录下执行。

+ 前台启动

所有启动的容器都在前台，控制台将会同时打印所有容器的输出信息，可以很方便进行调试。当通过 `Ctrl-C` 停止命令时，所有容器将会停止。

```bash
docker-compose up
```

+ 后台启动

在后台启动并运行所有的容器，一般推荐生产环境下使用该选项。

```bash
docker-compose up -d
```

+ 停止

停止已经处于运行状态的容器，但不删除它。通过 `docker-compose start` 可以再次启动这些容器。

```bash
docker-compose stop
```

+ 启动

启动已经存在的服务容器。

```bash
docker-compose start
```

+ 列出**项目中**的所有容器

```bash
docker-compose ps
```

#### 数据卷相关命令

+ 列出所有数据卷

```bash
docker volume ls
```

+ 查看指定数据卷

```bash
docker inspect <volume-name>
```

+ 删除数据卷

```bash
docker volume rm <volume-name>
```

+ 删除无主的数据卷

```bash
docker volume prune
```

[返回目录 :arrow_heading_up:](#目录-TOC)

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
