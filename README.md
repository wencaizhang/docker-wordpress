
## 必读

#### 设置 wp-content 目录读写权限

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