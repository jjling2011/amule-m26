[English](README.md) 简体中文

amule-m26 是在 aMule project 基础上进行改进。所有修改的代码都在 [patch file](docker/tmp/m26.patch) 这个补丁里面。

这个 web UI 不支持原版 aMule project，因为原版的 PHP 引擎的工具函数太少，想实现一些功能很困难。

### 用法

```bash
docker pull ghcr.nju.edu.cn/jjling2011/amule-m26:latest
```

使用方法和 [ngosang/docker-amule](https://github.com/ngosang/docker-amule) 相同，除了 `Template=M26` 这一个设置。

### 界面

![dark-theme-search.png](docs/search-dark.png)

### 缺失的功能

- 上传任务列表
- 各种统计图

我觉得这些功能并不重要。

### 开发指引

[deploy.yml](.github/workflows/deploy.yml)

### 荣誉

- https://github.com/ngosang/docker-amule/
- https://github.com/MatteoRagni/AmuleWebUI-Reloaded/
- https://github.com/amule-org/amule/
- https://github.com/amule-project/amule/
