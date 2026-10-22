## nginx 1.30.6 安全更新（2026-10-22，FEATURE-884）

`PhpAndNginx8.4` 保留 PHP 8.4.27 公司扩展层，使用 nginx 官方为 Alpine 3.23 x86_64 发布的 `nginx-1.30.6-r1.apk`。Alpine 默认仓库当前为 1.28.3-r7，仅重跑原来的 `apk add nginx` 不会获得本次修复。

Dockerfile 固定安装包和签名公钥的 SHA-256，并由 apk 验证包签名；离线安装只使用现有基础镜像的依赖。移除官方包自带的默认站点，再复制公司 nginx 配置，避免增加额外入口。适用平台为 linux/amd64。

基础镜像构建需要支持 `ADD --checksum` 的 Docker/BuildKit，本次使用 BuildKit 0.33.1。最终组合流水线只拉取已发布基础镜像，不执行这里的安装步骤。

| ACR 仓库 `shanjing/php-fpm` 的标签 | digest |
| --- | --- |
| `8.4.27-nginx1.30.6-feature-884-20261022` | `sha256:4db536ee0cf2a152532b9b06d2db3849fb5ea5e60a613133de24da85abfeff64` |
| `8.4-with-nginx`（已更新） | `sha256:4db536ee0cf2a152532b9b06d2db3849fb5ea5e60a613133de24da85abfeff64` |
| `8.4-with-nginx-backup-feature-884-20261022-6b476c9d9f5a`（升级前备份） | `sha256:6b476c9d9f5a64fe384274edf12122ec75a7ae5078abb79986495cd251a334e7` |

包 SHA-256 为 `d3e022393d1f9d5a1155e460565f96d8bdcf3401e8675f49940951c9b9b4599d`，公钥 SHA-256 为 `ff7cf138acc09f2a5029300ab713fe6a1440605fca72e2bab76a4da9206fec87`。两者均从 nginx.org 下载；公钥另与 nginx 官方 Dockerfile 固定的 SHA-512 核对一致。

验证：PHP CLI/FPM 8.4.27、nginx 1.30.6；完整镜像下的组合业务配置和本机 nginx 配置均通过 `nginx -t`。断网临时容器中的 nginx→FPM 扩展功能、multipart 上传、307 重定向、自定义响应头、WebSocket 101 握手全部通过。测试没有连接数据库、Redis、队列或生产接口。

本机 Compose 的 nginx 服务已使用上述固定 digest，显式设置 `entrypoint: ["nginx"]`、`command: ["-g", "daemon off;"]`，只运行 nginx。由于 DockerHub 拉取超时，此处复用已验证的公司镜像；镜像包含 PHP，但不启动 PHP/Supervisor。其他四个 Compose 服务的容器 ID、镜像、启动时间和重启次数保持不变。本机默认虚拟主机请求返回配置预期的 403。

生产 ECS/ECI 流水线没有触发；现有生产容器不会因基础标签改变而自动升级。公网 Tengine/雷池、本地 `8.4-dev`、`phpqa:8.4` 未随此操作更新。真实业务登录、上传、WebSocket 消息帧及队列消费尚待验收。

回退共享标签时，将备份 digest 原样发布到 `8.4-with-nginx`；生产已发布的组合镜像另按自身旧 digest 回退。本地旧 nginx 镜像保留为 `feature-884/nginx-local-backup:20261022-1.18.0`，恢复旧 Compose 镜像并移除本次 entrypoint/command 后，仅重新创建 nginx 服务。

官方依据：[安全公告](https://nginx.org/en/security_advisories.html)、[1.30.6 更新记录](https://nginx.org/en/CHANGES-1.30)。
