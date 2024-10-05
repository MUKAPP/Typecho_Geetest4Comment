# Typecho_Geetest4Comment

Typecho的极验4评论插件

[演示地址](https://mukapp.top/archives/178/)

## 安装

1. 在 Release 页面下载最新版本
2. 压缩包解压后，将 `Geetest4Comment` 文件夹复制到 `usr/plugins/` 目录下
3. 在后台插件管理中启用本插件

## 配置

1. 在极验官网注册账号，选择 **行为验证4.0**，**创建业务模块**，**新增业务场景**。客户端类型选择 `Web/Wap`，业务类型选择 `评论发帖`
2. 将创建的 `验证 ID` 和 `验证 KEY` 填入本插件的配置项中，保存设置
3. 在你使用的主题的 `comments.php` 文件中，找到 `<form>` 标签，在 `<button>` 附近添加以下代码（一定要在 `<form></form>` 内）
   ```php
   <?php Geetest4Comment_Plugin::commentCaptchaRender(); ?>
   ```
