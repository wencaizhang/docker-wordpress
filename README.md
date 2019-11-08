> 使用 traefik 作为代理服务器
> 记得先创建外部网卡
> docker network create traefiknet

# 使用 Docker 快速安装 WordPress

## 环境准备

**使用脚本快速安装 Docker**

```bash
curl -fsSL https://get.docker.com -o get-docker.sh && sudo sh get-docker.sh
```

**安装 docker-compose**

```bash
sudo curl -L "https://github.com/docker/compose/releases/download/1.22.0/docker-compose-$(uname -s)-$(uname -m)" \
-o /usr/local/bin/docker-compose

# 给Docker Compose 执行权限
sudo chmod +x /usr/local/bin/docker-compose

# 查看Docker和Docker Compose的版本
sudo docker version
sudo docker-compose version
```

**设置国内 Docker 镜像源**

```bash
sudo mkdir -p /etc/docker
sudo tee /etc/docker/daemon.json <<-'EOF'
{
  "registry-mirrors": ["https://oojjt1xs.mirror.aliyuncs.com"]
}
EOF
sudo systemctl daemon-reload
sudo systemctl restart docker
```

阿里云镜像源需要个人注册阿里云账号之后才能使用，这里我已经注册过了。

## 使用

直接在项目根目录启动 docker-compose 即可：

```bash
sudo docker-compose up -d
```

在 wordpress 安装（具体浏览器端的安装）完成之后，执行下面命令，安装本项目中预先准备好的的插件和主题

```bash
sudo cp -rf plugins/* wordpress/wp-content/plugins/

sudo cp -rf themes/* wordpress/wp-content/themes/
```


注意：
1. 请确认你服务器的 8000 端口未被其他程序占用，如果已经被占用，可以在 docker-compose.yml 文件中来修改服务器向 Docker 容器映射的端口。
2. 如果你想要为 wordpress 网站指定一个域名，而非通过 8000 端口进行访问，请先进行 nginx 反向代理设置域名，然后通过该域名来访问 wordpress 网站。
