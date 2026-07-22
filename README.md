# Web 技術各論演習

## セットアップ

### 0. リポジトリのクローン

`git clone` 等でリポジトリをクローンする。

```bash
git clone https://github.com/haruki26/web-class-practice.git
```

### 1. Docker Compose で起動

以下コマンドでアプリケーションを起動する。

```bash
docker compose up -d
```

### 2. テーブルの作成

掲示板が使用するテーブルを作成します。


MySQL コンテナに接続。

```bash
docker compose exec mysql mysql example_db;
```

SQL を発行。

```sql
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `image_filename` TEXT DEFAULT NULL,
);
```

### 3. アクセス

ブラウザから `http://[your-ip]/bbsimagetest.php` にアクセスして確認。

