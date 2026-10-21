# PHP 8.4.27 安全更新候选（FEATURE-884）

生产在 2026-10-21 22:44 核验时仍为 PHP 8.4.15。本次将 8.4 生产基础层
升级到修复版本 8.4.27，并固定基础镜像 digest，避免构建再次消费旧浮动标签。
仅构建并发布独立候选标签，没有更新生产浮动标签、ACR 自动规则或现有容器。

## 镜像来源

| 层 | ACR 候选标签（仓库 `shanjing/php-fpm`） | digest |
| --- | --- | --- |
| 官方配方自建 | `8.4.27-official-base-feature-884-20261021` | `sha256:8dd9aeb50fa5ac29f09df4eb26d07537c756087e5fd0327c56ca3a385caac42c` |
| 公司扩展 | `8.4.27-feature-884-20261021` | `sha256:7a1d272ccf8dc2686d76994d335b97c0eed82815b5c88d7c3fbf8a7e1c786d6d` |
| nginx | `8.4.27-with-nginx-feature-884-20261021` | `sha256:6b476c9d9f5a64fe384274edf12122ec75a7ae5078abb79986495cd251a334e7` |

官方配方来自 docker-library/php 提交
`e3eaff845f7a9f7259c31da54dce0320b47a3bbd` 的 `8.4/alpine3.23/fpm`，
完整保留源码 SHA256 与 GPG 校验。源码 SHA256 为
`dcf93923b0e38e0d5252b25b796520b5a47761a08b3488b6d0b5d1115764d3f0`，
签名指纹为 `9D7F99A0CB8F05C8A6958D6256A97AF7600A39A6`。
目标平台为 `linux/amd64`；公司层和 nginx 层的源码构建结果与本次测试镜像一致。

## 本地构建与检查

先确认 Docker context 属于本地测试环境。以下命令不会推送或部署：

```sh
docker buildx build --platform linux/amd64 --load -f Dockerfile84-alpine \
  -t feature-884/php-fpm:8.4.27-local .
docker buildx build --platform linux/amd64 --load -f PhpAndNginx8.4 \
  -t feature-884/php-fpm:8.4.27-with-nginx-local .
docker run --rm --platform linux/amd64 --network none --read-only --tmpfs /tmp \
  -v "$PWD/php84-runtime-smoke.php:/tmp/php84-runtime-smoke.php:ro" \
  feature-884/php-fpm:8.4.27-local php /tmp/php84-runtime-smoke.php
```

已通过 CLI/FPM 8.4.27 检查、nginx/FPM 配置检查、扩展加载与十进制计算、
UTF-8、ZIP 往返、GD/Imagick PNG 编码检查。扩展版本为 Redis 6.3.0、
Imagick 3.8.1、ZIP 1.22.8；其余被测 PHP 内置扩展为 8.4.27。nginx 为 1.28.3。
`php84-probe-nginx.conf` 可在断网容器中将同一功能检查脚本交给 FPM 执行，
仅监听容器本机 18080，不调用业务接口、队列或生命周期脚本。

本次保留现有 ini 策略：upload progress 为 On、strict mode 为 Off。
安全修复依靠 8.4.27，不把这两个配置当作完整补丁。

## 交付边界

原配方的 apk/PECL/Composer 下载仍随上游变化；本次已发布镜像以 digest 固定。
`php-8.41` 的 ACR 分支设置可能是有意禁用触发，本次没有修改。
开发、yiwutaro 和其他 PHP/FC 运行环境不在这次候选构建范围内。
组合镜像另见 docker-sae 的 `DockerfileForCombine8.4-security`。
