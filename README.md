## プロジェクト概要
このプロジェクトは Laravel を用いたフリマアプリです。  
ユーザーは会員登録・ログイン後に商品を出品・購入することができ、  
メール認証・コメント・お気に入り（マイリスト）・検索・Stripe による決済機能などを備えています。  

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:towa709/fleamarket.git`
2. `cd fleamarket`
3. DockerDesktopアプリを立ち上げる
4. `docker-compose up -d --build`

 上記の手順は任意の作業ディレクトリで実行可能です。  
   例: Linux/WSL 環境では `/home/ユーザー名/coachtech/fleamarket`、  
   Windows 環境では `C:\Users\ユーザー名\coachtech\fleamarket` など。

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. '.env.example'ファイルを コピーして'.env'を作成し、DBの設定を変更
4. `cp .env.example .env`

**注意**
初回ビルド及び.envコピー後、`src/` ディレクトリが root 権限になりますので、以下を必ずプロジェクトのルートディレクトリで実行して権限を修正してから保存してください。  
```bash
sudo chown -R $(whoami):$(whoami) .
```
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_FROM_ADDRESS=example@test.com
MAIL_FROM_NAME="Fleamarket App"

STRIPE_KEY=your_stripe_public_key_here
STRIPE_SECRET=your_stripe_secret_key_here
# Stripe API キー（開発環境用のテストキーを設定）
STRIPE_KEY と STRIPE_SECRET は Stripe ダッシュボードから取得してください。ここではダミー値が入っています。
 Stripe テストキーはこちらから取得できます:  
  https://dashboard.stripe.com/test/apikeys
```

5. アプリケーションキーの作成
``` bash
docker-compose exec php bash
php artisan key:generate
```

6. マイグレーションの実行時に、MySQL の初期化エラー（--initialize specified but the data directory has files in it）が出ます。以下のコマンドを順にプロジェクトのルートディレクトリで実行してデータディレクトリを削除して再起動してから、マイグレーションを実行してください。
```bash
docker-compose down
sudo rm -rf ./docker/mysql/data/*
docker-compose up -d
```

``` bash
docker-compose exec php bash
php artisan migrate --seed
php artisan migrate:fresh --seed
```
※これでマイグレーションとデータ投入は完了です
## テストユーザー情報
Seeder によって以下のユーザーが登録済みです。ログイン確認に使用してください。

- 管理者ユーザー（id:1）
  - Email: tanaka@example.com
  - Password: password123

- 一般ユーザー（例）
  - Email: sato@example.com
  - Password: password123


7. ストレージのリンク作成（画像保存用）
```bash
php artisan storage:link
```
8.  アクセス時に Permission denied エラーが出る場合は以下を実行してください。（http://localhost）
```bash
docker-compose exec php bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

9. テスト用データベースの作成  
テストは `laravel_test` データベースを使用します。  
初回のみ以下を実行して DB を作成してください。

```bash
docker-compose exec mysql bash
mysql -u root -p
```

MySQL コンソールに入ったら以下を入力：
```bash
CREATE DATABASE IF NOT EXISTS laravel_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON laravel_test.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;
EXIT;
```
これでテスト用 DB が準備されます。

10. テストの実行
```bash
php artisan test --env=testing
```
全 44 件のテストが PASS すれば、環境構築は正常に完了です。

### 実施内容
・各機能ごとに Feature テスト を作成
・会員登録・ログイン・ログアウト
・メール認証（認証メール送信／認証リンクによる認証完了／未認証時のアクセス制御）
・商品一覧／商品詳細／商品出品／検索
・コメント機能／お気に入り（マイリスト）機能
・購入処理（支払い方法・送付先住所・購入完了処理）
・プロフィール表示・編集

※メール認証について
・Laravel 標準のメール認証機能（MustVerifyEmail / Registered イベント / VerifyEmail 通知）を利用
・開発環境では MailHog を使用し、メール送信・認証リンク動作を確認
・会員登録後に認証メールを送信 → リンククリックで email_verified_at が更新され、商品一覧画面へ遷移

※決済処理について
・Stripe Checkout を利用し、クレジットカード・コンビニ払いに対応
・購入時に配送先住所を入力／変更可能
・決済成功後に transactions テーブルへ購入履歴を保存し、商品を is_sold = true に更新
・購入完了後はトップページへリダイレクトし、正常動作を確認済み

※テスト結果
・php artisan test --env=testing を実行
・合計 44 件のテストケースを作成し、すべて PASS

### 結論
仕様書に記載された全ての要件を満たしている
PHPUnit テストによって機能の正常動作を確認済み
特にメール認証と Stripe 決済処理について、仕様通りのフローを実装・確認できた

## ER図

![ER図](./docs/er.png)

## URL
- 開発環境：http://localhost
- phpMyAdmin: http://localhost:8080
- MailHog: http://localhost:8025

## 使用技術
- Laravel 11
- PHP 8.2
- MySQL 8.0
- Docker / docker-compose
- Nginx
- MailHog
- phpMyAdmin
- Stripe Checkout
