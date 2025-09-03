## 環境構築
**Dockerビルド**
1. `git clone git@github.com:towa709/fleamarket.git`
2.`cd fleamarket`
3. DockerDesktopアプリを立ち上げる
4. `docker-compose up -d --build`

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. '.env.example'ファイルを コピーして'.env'を作成し、DBの設定を変更
4. `cp .env.example .env`
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=example@test.com
MAIL_FROM_NAME="Fleamarket App"

# Stripe API キー（開発環境用のテストキーを設定）
STRIPE_KEY=your_stripe_public_key_here
STRIPE_SECRET=your_stripe_secret_key_here
```
# 注意
STRIPE_KEY と STRIPE_SECRET は Stripe ダッシュボードから取得してください。
ここではダミー値が入っています。
また、初回ビルド後、`src/` ディレクトリが root 権限になる場合があります。  
その際は以下を実行して権限を修正してください：  
```bash
sudo chown -R $(whoami):$(whoami) .
```

5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行時に、MySQL の初期化エラー（--initialize specified but the data directory has files in it）が出る場合は、以下でデータディレクトリを削除して再起動した後、再度マイグレーションを実行してください。
```bash
docker-compose down
sudo rm -rf ./docker/mysql/data/*
docker-compose up -d
```

``` bash
php artisan migrate
```
7. シーディングの実行
``` bash
php artisan db:seed
```
8. ストレージのリンク作成（画像保存用）
```bash
php artisan storage:link
```
9.  アクセス時に Permission denied エラーが出る場合は以下を実行してください。
```bash
docker-compose exec php bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

実施内容
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

結論
仕様書に記載された全ての要件を満たしている
PHPUnit テストによって機能の正常動作を確認済み
特にメール認証と Stripe 決済処理について、仕様通りのフローを実装・確認できた

## ER図

ER図は、要件定義シートのテーブル仕様書に貼り付け済み。

## URL
- 開発環境：http://localhost
- phpMyAdmin:：http://localhost:8080