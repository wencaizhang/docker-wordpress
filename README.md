### 目录 TOC

+ [简介 Intro](#简介-Intro)
+ [使用 Usage](#使用-Usage)
+ [数据卷 volume](#数据卷-volume)
+ [端口设置 ports](#端口设置-ports)
+ [环境变量设置 environment](#环境变量设置-environment)
+ [常用命令 Command](#常用命令-Command)
  - [1.镜像相关命令](#1.镜像相关命令)
  - [2.容器相关命令](#2.容器相关命令)
  - [3.Compose 相关命令](#3.Compose-相关命令)
  - [4.数据卷相关命令](#4.数据卷相关命令)

### 简介 Intro

一个 wordpress 网站需要 Mysql 数据库、 wordpress 程序和 Nginx/Apache 服务器才能运行起来，使用 Docker 仅需要两个镜像即可：

+ mysql:5.7（配置：Debian 系统、Mysql/5.7.25)
+ wordpress:latest（配置：Debian 系统、Apache/2.4.25 、PHP/7.2.15)

然后使用 docker-compose 将这两个服务关联成一个项目。

>Compose 中有两个重要的概念：
>+ 服务 (service)：一个应用的容器，实际上可以包括若干运行相同镜像的容器实例。
>+ 项目 (project)：由一组关联的应用容器组成的一个完整业务单元，在 docker-compose.yml 文件中定义。

### 使用 Usage

直接在项目根目录启动 docker-compose 即可：

```bash
docker-compose up -d
```

注意：
1. 请确认你服务器的 8000 端口未被其他程序占用，如果已经被占用，可以在 docker-compose.yml 文件来修改服务器向 Docker 容器映射的端口。
2. 如果你想要为 wordpress 网站指定一个域名，而非通过 8000 端口进行访问，请先进行 nginx 反向代理设置域名，然后通过该域名来访问 wordpress 网站。

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


### 常用命令 Command

#### 1.镜像相关命令

+ 列出所有镜像

```bash
docker image ls
```

+ 删除镜像

```bash
docker image rm <image-name>
```

#### 2.容器相关命令

+ 基于镜像新建一个容器并启动



#### 3.Compose 相关命令

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

+ 删除

删除所有（停止状态的）服务容器。推荐先执行 docker-compose stop 命令来停止容器。

选项：
+ -f, --force 强制直接删除，包括非停止状态的容器。一般尽量不要使用该选项。
+ -v 删除容器所挂载的数据卷。

```bash
docker-compose rm
```

+ 列出**项目中**的所有容器

```bash
docker-compose ps
```


#### 4.数据卷相关命令

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


### 参考

[Docker — 从入门到实践·语雀](https://www.yuque.com/grasilife/docker)
