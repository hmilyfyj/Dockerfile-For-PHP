# PHP 8.4 开发镜像安全更新

`Dockerfile8.4-dev` 固定已验证的 PHP 8.4.27 公司扩展层，保留 `php.ini-unsecure`、FPM 池配置和 xlswriter 1.5.8。生产 session 配置不在本变更范围内。

发布顺序：先将 `php-fpm:8.4`、`8.4-dev` 的旧 digest 备份到独立日期标签，构建开发候选并验证 PHP CLI/FPM、扩展、配置摘要和 xlswriter 工作簿，再覆盖同名标签。ACR 自动触发规则保持原状。

本地拉取新版后，仅对 `config/production/lnmp/single_build/lnmp-in-aliyun-v3-test-v1.yml` 的 `php-fpm84` 服务执行 `docker compose up -d --no-deps php-fpm84`。镜像发布不会更新已有容器；其他 Compose 服务不应重建。
