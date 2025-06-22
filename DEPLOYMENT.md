# 🚀 Laravel + Vue 3 看板应用部署指南

## 📋 项目概述

这是一个现代化的看板管理应用，采用 Laravel 11 + Vue 3 + TypeScript 技术栈开发。

### 🏗️ 技术架构
- **后端**: Laravel 11 + PHP 8.2+ + SQLite/MySQL
- **前端**: Vue 3 + TypeScript + Vite + Tailwind CSS
- **认证**: Laravel Sanctum Token 认证
- **测试**: Pest (后端) + Vitest (前端)
- **代码质量**: ESLint + Prettier + PHP CS Fixer

## 🔧 环境要求

### 后端要求
- PHP 8.2 或更高版本
- Composer 2.x
- SQLite 或 MySQL 数据库
- Node.js 18+ (用于资源编译)

### 前端要求
- Node.js 18 或更高版本
- npm 或 yarn

## 📦 部署步骤

### 1. 克隆项目
```bash
git clone git@github.com:williamhatch/my-laravel-kanban-2.git
cd my-laravel-kanban-2
```

### 2. 后端部署

```bash
# 进入后端目录
cd backend

# 安装 PHP 依赖
composer install --optimize-autoloader --no-dev

# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate

# 配置数据库连接 (编辑 .env 文件)
# 对于生产环境，推荐使用 MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_kanban
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 运行数据库迁移和种子
php artisan migrate --seed

# 生成 Sanctum 密钥
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 优化性能 (生产环境)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. 前端部署

```bash
# 进入前端目录
cd ../frontend

# 安装依赖
npm install

# 构建生产版本
npm run build

# 构建产物在 dist/ 目录中
```

### 4. Web 服务器配置

#### Nginx 配置示例
```nginx
# 后端 API
server {
    listen 80;
    server_name api.yourdomain.com;
    root /path/to/your/project/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# 前端应用
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/project/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

#### Apache 配置示例
```apache
# 后端 .htaccess (已包含在 Laravel 中)
# 前端 .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

## 🔒 安全配置

### 环境变量设置
```bash
# 生产环境必须设置
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# 数据库安全
DB_PASSWORD=strong_random_password

# CORS 配置
CORS_ALLOWED_ORIGINS=https://yourdomain.com

# 会话安全
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

### SSL/HTTPS 配置
生产环境必须启用 HTTPS：
```bash
# 使用 Let's Encrypt 获取免费 SSL 证书
sudo certbot --nginx -d yourdomain.com -d api.yourdomain.com
```

## 📊 监控和维护

### 日志监控
```bash
# 查看 Laravel 日志
tail -f backend/storage/logs/laravel.log

# 清理日志 (定期执行)
php artisan log:clear
```

### 性能优化
```bash
# 清理缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 重新缓存 (生产环境)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 备份策略
```bash
# 数据库备份脚本示例
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p database_name > backup_$DATE.sql
```

## 🧪 质量保证

### 运行测试
```bash
# 后端测试
cd backend
php artisan test --coverage

# 前端测试
cd frontend
npm test

# 代码质量检查
npm run lint
```

### CI/CD 集成
项目已配置 GitHub Actions，每次推送都会自动运行：
- 代码质量检查
- 自动化测试
- 安全扫描

## 🔧 故障排除

### 常见问题

1. **权限问题**
```bash
sudo chown -R www-data:www-data backend/storage
sudo chmod -R 775 backend/storage
```

2. **Composer 内存不足**
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

3. **前端构建失败**
```bash
rm -rf node_modules package-lock.json
npm install
```

### 性能调优

1. **启用 OPcache** (php.ini)
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```

2. **Redis 缓存** (可选)
```bash
# 安装 Redis
sudo apt install redis-server

# Laravel 配置
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## 📞 支持

如有问题，请查看：
- [Laravel 文档](https://laravel.com/docs)
- [Vue 3 文档](https://vuejs.org/)
- [项目 GitHub Issues](https://github.com/williamhatch/my-laravel-kanban-2/issues)

---

**祝您部署顺利！** 🎉 