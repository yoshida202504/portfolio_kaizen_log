# Kaizen Log

日々の行動を振り返り、次に試す改善策とその結果を記録する Laravel 製Webアプリケーションです。自分の記録を継続的に改善しながら、公開設定や相互フォローを通じて他ユーザーの記録からも学べます。
自分の記録を継続的に改善しながら、公開設定や相互フォローを通じて他ユーザーの記録からも学べます。

## 主な機能

- ユーザー登録・ログイン・ログアウト・退会（ユーザーはSoft Delete）
- 日報の作成・一覧・検索・編集・削除（Soft Delete）
- 公開 / 非公開の設定と画像アップロード
- 改善結果・改善率の記録（記録日から7日後まで更新可能）
- Communityでの公開日報閲覧
- フォロー / フォロー解除、相互フォロー時の非公開日報閲覧
- Like、コメントの投稿・編集・削除
- マイページでのフォロー、Like済み日報、改善率推移の確認

## アクセス制御の方針

| 操作 | 許可されるユーザー |
| --- | --- |
| 日報の編集・削除・改善結果入力 | 日報の所有者のみ |
| 公開日報の閲覧 | ログイン済みユーザー |
| 非公開日報の閲覧 | 所有者または相互フォローのユーザー |
| Like・コメント | 閲覧可能な他ユーザーの日報のみ |
| コメントの編集・削除 | コメント投稿者のみ |

## 技術構成

- PHP 8.4 / Laravel 13
- MySQL 8.4
- Nginx
- Docker Compose
- Laravel Pint
- PHPUnit Feature Test

## ローカル開発環境

### 必要なもの

- Docker Desktop または Docker Engine と Docker Compose

### 起動方法

```bash
git clone <repository-url>
cd portfolio_kaizen_log
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

ブラウザで `http://localhost:8280` を開きます。

> `.env` には接続情報やAPP_KEYが含まれます。Git管理や公開リポジトリへの登録はしないでください。

## テストとコードスタイル

テストはSQLiteのインメモリDBを使用するため、ローカルMySQLのデータを初期化しません。

```bash
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app php artisan view:cache
```

## ディレクトリ構成

```text
portfolio_kaizen_log/
├── infra/                 # PHP・MySQL・NginxのDocker設定
├── src/
│   ├── app/               # Controller、Model、Policy、FormRequest
│   ├── database/          # Migration、Factory、Seeder
│   ├── resources/views/   # Bladeテンプレート
│   └── tests/Feature/     # 主要機能のFeature Test
├── docker-compose.yml
└── docker-compose.prod.yml
```

## 本番公開前の確認

- `APP_ENV=production`、`APP_DEBUG=false` を設定する
- HTTPSを有効にし、HTTPS環境では `SESSION_SECURE_COOKIE=true` を設定する
- DB・メールなどの認証情報を本番用に発行し、過去に利用した値は再利用しない
- `php artisan test` と `./vendor/bin/pint --test` の成功を確認する
- GitHub Actionsのチェックが成功していることを確認する

## 今後の改善候補

- メール認証の導入可否の検討
- ログイン試行制限の継続的な監視
- 利用データ増加後のクエリ分析とインデックス最適化
- 改善率グラフ生成処理の専用クラスへの分離
