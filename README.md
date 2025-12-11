# フリマアプリ

## 環境構築

### Docker ビルド

1. リポジトリを取得

   ```bash
   git clone https://github.com/matsu-shima-130/freemarket-exam.git
   cd freemarket-exam
   ```

2. コンテナを作成・起動

   ```bash
   docker-compose up -d --build
   ```

   ※ MySQL は、OS によっては起動しない場合があるため、各 PC に合わせて docker-compose.yml を編集してください。

### Laravel 環境構築

1. PHP コンテナに入る

   ```bash
   docker-compose exec php bash
   ```

2. 依存パッケージのインストール & 環境ファイル作成

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. `.env` の DB 設定（例）

   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass
   ```

4. マイグレーション

   ```bash
   php artisan migrate
   ```

5. シーディング

   ```bash
   php artisan db:seed
   php artisan db:seed --class=ItemSeeder
   ```

6. メール送信設定（Mailtrap）

- メール認証機能には Mailtrap を使用しています。
- Mailtrap でテスト用アカウントを作成し、SMTP の接続情報を取得します。
- .env を以下のように設定します（値は Mailtrap 上のものに置き換えてください）。
  ```bash
  MAIL_MAILER=smtp
  MAIL_HOST=sandbox.smtp.mailtrap.io
  MAIL_PORT=2525
  MAIL_USERNAME=xxxxxxxxxxxxxxxx
  MAIL_PASSWORD=yyyyyyyyyyyyyyyy
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=no-reply@example.com
  MAIL_FROM_NAME="Freemarket-exam"
  ```
- 設定反映
  ```bash
  php artisan config:clear
  ```
- 会員登録後、/email/verify に遷移し、Mailtrap の受信ボックスに届いたメールから認証リンクをクリックするとメール認証が完了します。

7. 決済処理（Stripe）

- 購入処理には Stripe Checkout（テストモード）を使用しています。
- Stripe アカウントを作成し、ダッシュボードを テストモード に切り替えます。
- 「開発者」→「API キー」から以下のキーを取得します。
  - 公開可能キー（Publishable key）
  - シークレットキー（Secret key）
- .env に追記します。
  ```bash
  STRIPE_KEY=pk_test_xxxxxxxxxxxxxxxxx
  STRIPE_SECRET=sk_test_yyyyyyyyyyyyyyyy
  ```
- 設定反映
  ```bash
  php artisan config:clear
  ```

8. テスト

- PHPUnit による Feature テストを実装しています。
  ```bash
  docker-compose exec php bash
  php artisan test
  ```
- 特定のテストクラスだけ実行したい場合は --filter を使用します。

  ```bash
  # ID1 会員登録機能
  php artisan test --filter=RegisterTest

  # ID2 ログイン機能
  php artisan test --filter=LoginTest

  # ID3 ログアウト機能
  php artisan test --filter=LogoutTest

  # ID4 商品一覧取得
  php artisan test --filter=ItemListTest

  # ID5 マイリスト一覧取得
  php artisan test --filter=MylistTest

  # ID6 商品検索機能
  php artisan test --filter=ItemSearchTest

  # ID7 商品詳細情報取得
  php artisan test --filter=ItemShowTest

  # ID8 いいね機能
  php artisan test --filter=LikeTest

  # ID9 コメント送信機能
  php artisan test --filter=CommentTest

  # ID10 商品購入機能
  php artisan test --filter=PurchaseTest

  # ID11 支払い方法選択機能
  php artisan test --filter=PaymentMethodTest

  # ID12 配送先変更機能
  php artisan test --filter=PurchaseAddressTest

  # ID13 ユーザー情報取得
  php artisan test --filter=MypageProfileTest

  # ID14 ユーザー情報変更
  php artisan test --filter=MypageProfileEditTest

  # ID15 出品商品情報登録
  php artisan test --filter=ItemStoreTest

  # ID16 メール認証機能
  php artisan test --filter=EmailVerificationTest
  ```

  - テスト内容はテストケース一覧に準拠しています。

## ダミーデータについて

- `php artisan db:seed` を実行すると、以下のテストデータが作成されます。
  - 商品カテゴリデータ（ファッション / 家電 など）
- `php artisan db:seed --class=ItemSeeder`を実行すると、以下のテストデータが作成されます。
  - 出品者ユーザー（seller@example.com）
  - ダミー商品データ一式（画像付き）

## 追加機能

仕様書に明記されていないが、利便性向上のために以下の機能を追加しています。

- フラッシュメッセージ表示

  - 商品詳細ページで、いいね／コメント送信後に結果をフラッシュメッセージで表示します。
  - プロフィール編集画面で情報を編集後、結果をフラッシュメッセージで表示します。

- コメント削除機能

  - ログインユーザーが自分で投稿したコメントに限り、「削除」ボタンが表示され、削除できるようにしています。
  - Policy を用いて本人以外は削除できないよう制御しています。

## 開発環境（URL）

- 商品一覧（トップ画面）: http://localhost/
- 会員登録: http://localhost/register
- ログイン: http://localhost/login
- phpMyAdmin: http://localhost:8080/

### 主な画面

- プロフィール画面: http://localhost/mypage
- プロフィール編集画面: http://localhost/mypage/profile
- 商品出品画面: http://localhost/sell
- 商品詳細画面: http://localhost/item/{id}
  - 例）http://localhost/item/1
- 商品購入画面: http://localhost/purchase/{item_id}
  - 例）http://localhost/purchase/1
- 配送先変更画面: http://localhost/purchase/address/{item_id}
  - 例）http://localhost/purchase/address/1

## 使用技術（実行環境）

### バックエンド/インフラ

- PHP 8.1.33
- Laravel 8.83.8
- MySQL 8.0.26
- Nginx 1.21.1
- Docker / docker-compose

### ライブラリ・パッケージ

- Laravel Fortify（認証・メール認証）
- stripe/stripe-php（決済連携）

### 外部サービス

- Mailtrap（メール送信テスト環境）
- Stripe（テスト決済）

### フロントエンド

- Font Awesome（アイコン表示）

## ER 図

![ER図](./docs/er.png)
