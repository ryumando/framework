# 軽量フレームワーク

個人サイト用に作成した簡易フレームワーク  
PHP7以上で動作

## 利用方法

1. common および project ディレクトリをサーバー環境へアップロード
2. project/public ディレクトリが公開ディレクトリ（DocumentRoot）となるよう設定
3. すべてのアクセスが project/public/index.php にリダイレクトされるよう設定 ※1
4. サイトにアクセスし、< Hello! World > と表示されることを確認 ※2

※1 Webサーバーが Apache で、mod_rewrite ならびに .htaccess が有効な環境であれば設定の必要はありません。  
※2 初期状態では controllers/sample_controller.php の index メソッドが実行され、views/sample_view.php が表示されます。

- project ディレクトリ名は任意の名称（プロジェクト名等）に変更可能です。
- project ディレクトリをコピーして複数サイトに対応可能です。

## ローカル環境の起動方法

Docker および Docker Compose がインストールされている環境であれば、プロジェクトディレクトリ直下で以下のコマンドを実行しローカル環境を起動できます。  
`docker-compose up -d`

ローカル環境の起動後、ブラウザで以下URLへアクセスすると挙動を確認できます。  
http://localhost:8080

以下URLではローカル環境で送信されたメールの確認ができます。  
http://localhost:1080

プロジェクトディレクトリ直下で以下のコマンドを実行すると、ローカル環境を停止します。  
`docker-compose down`

- プロジェクトディレクトリ名（ project ）または公開ディレクトリ名（ public ）を変更した場合は、プロジェクトディレクトリ下 docker/apache/000-default.conf 内の DocumentRoot 設定箇所（以下部分）を適宜書き換えてください。  
`DocumentRoot /var/www/html/project/public`

- ローカル環境における MySQL、PostgreSQL のユーザー名・パスワード・データベース名等は docker-compose.yml より確認・変更してください。

## ローカル環境のコンテナ確認方法

ローカル環境の起動後に以下コマンドを実行すると、各Dockerコンテナにアクセスすることが可能です。

- PHP コンテナ  
`docker-compose exec php bash`

- MySQL コンテナ  
`docker-compose exec mysql bash`

- PostgreSQL コンテナ  
`docker-compose exec postgres bash`

※ 全ての Docker イメージを一括削除する場合は以下コマンドを実行  
`docker images -aq | xargs docker rmi`

## 命名規則

ディレクトリ名・ファイル名・名前空間・クラス名は以下のルールに沿って命名してください。

- PHPの名前空間およびクラス名はパスカルケースで、ディレクトリ名およびファイル名はスネークケースでの命名を推奨します。

- PHPファイルのクラス名はファイル名と一致するよう命名してください。  
例えば、TopController クラスは top_controller.php に記載されている必要があります。  

- PHP ファイルの名前空間は、プロジェクトディレクトリからの相対パスに合致するよう命名してください。  
例えば、project/domain/value_objects/user/name.php の名前空間指定は以下になります。  
`namespace Domain\ValueObjects\User;`

- プロジェクトディレクトリ下ではなく common ディレクトリ下に PHP ファイルを作成する場合、名前空間は Common から始まるように命名してください。  
例えば、common/library/image.php の名前空間は以下になります。  
`namespace Common\Library;`

## ルーティングの設定

ルーティング設定は middleware/routing.php 内、routes メソッドから返却される要素数3の配列として設定してください。  
最初の要素はURLにマッチするパターンを正規表現で設定してください。2番目の要素は呼び出されるクラス名を、3番目の要素は実行するメソッド名を設定してください。  
最初の要素でカッコで囲ったパターンにマッチした値は、3番目の要素で指定したメソッドの引数として引き渡されます。

【設定例】

    public static function routes(): array
      return [
        ['search', 'Controllers\Search', 'form'],
        ['search/([0-9]+)', 'Controllers\Search', 'number'],
        ['search/([a-z]+)', 'Controllers\Search', 'word'],
        ['search/([0-9]+)/([a-z]+)', 'Controllers\Search', 'detail'],
        ['contact/[0-9]?', 'Controllers\Contact', 'form']
      ];
    }

例えば、上記の設定例の場合の挙動は以下です。
- http://your-domain/search/ へアクセスがあった場合  
⇒ Controllers\Search クラスの form メソッドが実行されます。

- http://your-domain/search/123/ へアクセスがあった場合  
⇒ Controllers\Search クラスの number メソッドに引数 123 が渡されて実行されます。

- http://your-domain/search/abc/ へアクセスがあった場合  
⇒ Controllers\Search クラスの word メソッドに引数 abc が渡されて実行されます。

- http://your-domain/search/123/abc/ へアクセスがあった場合  
⇒ Controllers\Search クラスの detail メソッドに第1引数 123、第2引数 abc が渡されて実行されます。

- http://your-domain/contact/ へアクセスがあった場合  
⇒ Controllers\Contact クラスの form メソッドが実行されます。

- http://your-domain/contact/1/ へアクセスがあった場合  
⇒ Controllers\Contact クラスの form メソッドが実行されます。

- http://your-domain/contact/11/ へアクセスがあった場合  
⇒ どのパターンにもマッチしないため、404エラーとなります。

## Controller の利用

Controller クラスには \Common\Core\Controller クラスを継承してください。  
Controller クラスから view メソッドを実行すると View ファイルを呼び出すことが出来ます。  
view メソッドの引数は以下です。（第1引数のみ必須）

- 第1引数：プロジェクトディレクトリから View ファイルまでの相対パス
- 第2引数：View ファイルへ受け渡すデータ
- 第3引数：第2引数に含まれるHTMLタグをエンコードする場合は true（デフォルトは false ）
- 第4引数：View ファイル呼び出し後も処理を続ける場合は false（デフォルトは true ）

例：`$this->view('views/top_view.php', $data);`

## View の利用

View ファイル内では Controller の view メソッド第2引数で受け渡されたデータを $res 変数で参照出来ます。  
例えば Controller から以下のデータが受け渡された場合、

    $data = [
      'key1' => 123,
      'ley2' => 'ABC'
    ];

    $this->view('views/top_view.php', $data);

View ファイルの $res 変数は以下になります。

    $res['key1'] => 123
    $res['key2'] => 'ABC'

## 設定値の参照

config/app.ini ファイルに任意の設定値を記載し、config メソッドを通して内容を参照することが出来ます。

【設定例】

    environment = develop

    pickup[] = 100
    pickup[] = 200
    pickup[] = 300

    [any-api]
    url = "http://any-api-domain/sample/"

    [databases]
    default[driver] = SQLite
    default[dbname] = "/sqlite/sqlite.db"

config メソッドの引数には、参照したい設定値のキーをピリオドでつないで渡します。  
例えば上記の設定例の場合、以下のようにキーを渡すことで値が返却されます。

    self::config('environment') => 'develop'
    self::config('pickup.0') => '100'
    self::config('pickup.1') => '200'
    self::config('pickup.2') => '300'
    self::config('any-api.url') => 'http://any-api-domain/sample/'
    self::config('databases.default.driver') => 'SQLite'
    self::config('databases.default.dbname') => '/sqlite/sqlite.db'

また、以下のように配列で参照することも可能です。

    self::config('pickup') => ['100', '200', '300']


※ config メソッドは \Common\Core\Base を継承したクラスで使用することが出来ます。

## CLIでの利用方法

cli.php へコマンドライン引数としてPHPファイルのパス（またはクラス名）およびメソッド名を指定すると、CLI（コマンドラインインターフェース）から任意のメソッドを実行することができます。

`php cli.php [PHPファイルのパスまたはクラス名] [メソッド名] [引数]...`

- 第1引数：プロジェクトディレクトリからPHPファイルへの相対パス、またはクラス名
- 第2引数：実行するメソッド名
- 第3引数以降：第2引数で指定したメソッドへ渡される引数

例えば \Middleware\Migraion クラスの migrate メソッドに、2つの引数 'mysql' と 'dir' を渡して実行する場合のコマンドは以下になります。  
`php cli.php middleware/migration.php migrate mysql dir`

## データベースの利用

middleware/database.php コンストラクタ内の $databases 配列に、任意のキー（データベース接続情報キー）に紐付く連想配列としてデータベース接続情報を設定してください。

- driver  
データベースの種類（SQLite, MySQL, PostgreSQL など）
- user  
接続ユーザー名
- password  
接続パスワード
- dbname  
接続先データベース名
- host  
接続先ホスト
- option  
option を指定するとPDOコンストラクタの第4引数 options に渡されます

【設定例】

    public function __construct($key = null)
    {
        self::$databases = [
            'db1' => [
                'driver' => 'SQLite',
                'dbname' => '/path/to/data.sqlite',
            ],
            'db2' => [
                'driver' => 'MySQL',
                'user' => 'm_user_name',
                'password' => 'm_password',
                'dbname' => 'm_database',
                'unix_socket' => '/path/to/socket',
                'charset' => 'utf8mb4'
            ],
            'db3' => [
                'driver' => 'PostgreSQL',
                'user' => 'p_user_name',
                'password' => 'p_password',
                'dbname' => 'p_database',
                'host' => 'localhost',
                'port' => '5432'
            ],
        ];
        parent::__construct($key);
    }


### ■ 接続
Middleware\Database クラスのコンストラクタへ接続したいデータベース情報のキーを引数として渡すとデータベースへ接続します。  
例：`$db = new \Middleware\Database('db1');`

または、connect メソッドの引数に接続したいデータベース情報のキーを渡して接続することも可能です。  
例：`$db->connect('db2');`

### ■ クエリの実行
SQLは query メソッドにクエリを引数として渡すことで実行されます。  
例：`$db->query('SELECT * FROM table');`

また、以下のように第2引数以降に値を渡すことで、値をバインドさせることも可能です。  
例：`$db->query('SELECT * FROM table WHERE column1 = ? AND column2 = ?', 'val1', 'val2');`

第2引数に配列として値を渡す形でもバインドさせることが可能です。  
例：`$db->query('SELECT * FROM table WHERE column1 = ? AND column2 = ?', ['val1', 'val2']);`

以下のように、第2引数に連想配列として値を渡す形でもバインドが可能です。  
例：`$db->query('SELECT * FROM table WHERE column1 = :col1 AND column2 = :col2', ['col1' => 'val1', 'col2' => 'val2']);`

一度に複数のクエリを実行する際は、exec メソッドを実行してください。  
例：`$db->exec('TRUNCATE TABLE table1; TRUNCATE TABLE table2');`

### ■ 結果の取得
クエリの結果を全件取得する際は、result メソッドを実行してください。  
※ result メソッドの返却値は PDOStatement オブジェクトです。

クエリの結果を一件づつ取得する際は、fetch メソッドを実行してください。
- 結果として返されるカラムが一つのみの場合は、そのカラムの値のみを返却します。
- 複数のカラムが返される場合は、カラム名をキーとした連想配列として値が返却されます。
- 引数に取得したいカラム名を渡すと、指定したカラムの値のみを返却します。

fetch メソッドが返却する結果は一行のみです。  
すべての結果を取得する場合は連続してfetch メソッドを呼び出すか、result メソッドを利用してください。

【実行例】

    // result メソッドで全件取得する場合
    $result = $db->result();
    foreach ($result as $data) {
      ...
    }

    // fetch メソッドで一件づつ取得する場合
    while ($data = $db->fetch()) {
      ...
    }

connect、query、result および fetch メソッドは、チェーンメソッドとしてつなげて記述することも可能です。  
例：`$name = $db->connect('db')->query('SELECT name FROM user WHERE id = ?', $id)->fetch();`

### ■ トランザクション・コミット・ロールバック
startTransaction メソッドでトランザクションを開始し、commit メソッドでコミットを実行、rollback メソッドでロールバックを行います。

【実行例】

    $db->startTransaction(); // トランザクション開始
    try {
      $db->query('DELETE FROM table WHERE column = 1');
      $db->query('DELETE FROM table WHERE column = 2');
      $db->commit(); // コミット
    } catch (\Exception $e) {
      $db->rollback(); // ロールバック
    }

### ■ 切断
不要となった接続を切断する際には close メソッドを実行してください。  
例：`$db->close();`

データベース接続情報キーが同じであれば、異なるインスタンスであっても裏では同じ接続を使用しています。  
そのため、仮に close メソッドで接続を切断した後、同じ接続情報キーで初期化した異なるインスタンスから処理を実行する場合には、connect メソッドで再接続を行う必要があります。

## マイグレーションの実行

マイグレーション管理用のディレクトリを用意し、そのディレクトリ配下に実行したいSQLクエリを記載したテキストファイル（マイグレーションファイル）を配置してください。  
その後、以下いづれかの方法でマイグレーションを実行できます。

### ■ Middleware\Database インスタンスから実行
マイグレーションを実行したいデータベースに接続し、プロジェクトディレクトリからマイグレーション管理用ディレクトリまでの相対パスを引数として migration メソッドを実行してください。

【実行例】

    $db = new \Middleware\Database('db1');
    $db->migration('migration/db1');

### ■ Middleware\Migration インスタンスから実行
第1引数にマイグレーションを実行したいデータベースの接続情報キー、第2引数にプロジェクトディレクトリからマイグレーション管理用ディレクトリまでの相対パスを指定し、migrate メソッドを実行してください。

【実行例】

    $migration = new \Middleware\Migration();
    $migration->migrate('db1', 'migration/db1');

### ■ CLIから実行
cli.php を経由して Middleware\Migration クラスの migrate メソッドを実行してください。  
コマンドライン引数の第一引数は migration.php のパス、第二引数はメソッド名 migrate、第3引数はマイグレーションを実行したいデータベースの接続情報キー、第4引数はプロジェクトディレクトリからマイグレーション管理用ディレクトリまでの相対パスとなります。

【実行例】

    php cli.php middleware/migration.php migrate db1 migration/db1

### ■ 実行履歴
マイグレーションを実行すると、まだ未実行のマイグレーションファイルに記載されたSQLクエリを、ファイル名昇順で順次実行していきます。  

マイグレーションの実行履歴は migration_history テーブルに保存されます。  
migration_history テーブルのテーブル名を変更する場合は、Database クラスの setMigrationTable メソッドの引数に新しいテーブル名を設定した上で、migration メソッドを実行してください。

【対応例】

    // Middleware\Database インスタンスから実行する場合
    $db->setMigrationTable('new_table_name')->migration('migration/db1');

    // middleware/migration.php を書き換えて対応
    return $database->setMigrationTable('new_table_name')->migration($path);

既に実行されたマイグレーションファイルを再度実行したい場合は、実行履歴保存テーブル（ migration_history ）から当該ファイルのレコードを消去した上でマイグレーションを実行してください。

## メールの送信

Middleware\Mail クラスからメールの送信を行うことが出来ます。  
メソッドは以下です。

- subject  
メールの件名を設定します。
- message  
メールの本文を設定します。
- file  
添付ファイルを追加します。第1引数がファイル名、第2引数がバイナリです。  
ファイルを複数添付したい場合は、file メソッドを複数回実行します。
- to  
メールの宛先を追加します。第1引数がメールアドレス、第2引数が名前です。（第2引数は省略可能）  
宛先を複数設定したい場合は、to メソッドを複数回実行します。
- cc  
メールのCCを追加します。第1引数がメールアドレス、第2引数が名前です。（第2引数は省略可能）  
CCを複数設定したい場合は、cc メソッドを複数回実行します。
- bcc  
メールのBCCを追加します。第1引数がメールアドレス、第2引数が名前です。（第2引数は省略可能）  
BCCを複数設定したい場合は、bcc メソッドを複数回実行します。
- from  
メールの送信者を設定します。第1引数がメールアドレス、第2引数が名前です。（第2引数は省略可能）
- returnPath  
エラー通知先のメールアドレスを設定します。  
未指定の場合は from メソッドで指定したメールアドレスが設定されます。
- replyTo  
メールの返信先を設定します。第1引数がメールアドレス、第2引数が名前です。（第2引数は省略可能）  
未指定の場合は from メソッドで指定したメールアドレス、名前が設定されます。
- organization  
メール送信者の組織名を設定します。
- priority  
メールの重要度を1～5の整数で設定します。  
1が重要度高、3が通常、5が重要度低です。
- encoding  
メールの送信文字コードを設定します。  
未指定の場合、UTF-8に変換されます。
- isHtml  
HTMLメールを送信する場合のみ true を設定します。  
未指定または false を設定した場合はテキストメールとして扱われます。
- reset  
設定内容を初期化します。
- send  
メールを送信します。

【実行例】

    $mail = new \Middleware\Mail();
    
    // 必須項目のみ
    $mail
      ->subject('件名')
      ->message('本文です。')
      ->to('to@example.com')
      ->from('from@example.com')
      ->send();

    // すべて変更
    $mail
      ->reset()
      ->subject('件名')
      ->message('<b>本文です。</b>')
      ->file('sample1.png', file_get_contents('/path/to/sample1.png'))
      ->file('sample2.pdf', file_get_contents('/path/to/sample2.pdf'))
      ->to('to1@example.com', '〇〇様')
      ->to('to2@example.com', '〇〇様')
      ->cc('cc1@example.com', '〇〇様')
      ->cc('cc2@example.com', '〇〇様')
      ->bcc('bcc1@example.com', '〇〇様')
      ->bcc('bcc2@example.com', '〇〇様')
      ->from('from@example.com', '送信者名')
      ->replyTo('reply-to@example.com', '送信者名')
      ->returnPath('return-path@example.com')
      ->organization('株式会社〇〇')
      ->priority(5)
      ->isHtml(true)
      ->encoding('ISO-2022-JP')
      ->send();

## ディレクトリ構造の変更

プロジェクトディレクトリ内のディレクトリ構造等は自由に変更可能ですが、以下ディレクトリ・ファイルを変更する際には一部書き換え・設定が必要となります。

- public ディレクトリを変更する場合は、public/index.php の以下部分で、common/core/app.php が正しく読み込まれるよう適宜パスを書き換えてください。  
`require_once dirname(__FILE__) . '/../../common/core/app.php';`  
ローカル環境を利用される場合は、docker/apache/000-default.conf 内の DocumentRoot 設定箇所（以下部分）も合わせて書き換えてください。  
`DocumentRoot /var/www/html/project/public`

- cli.php の配置を変更する場合は、cli.php の以下部分で、common/core/app.php が正しく読み込まれるよう適宜パスを書き換えてください。  
`require_once dirname(__FILE__) . '/../common/core/app.php';`

- middleware/database.php, middleware/migration.php, middleware/routing.php を変更する場合は、それぞれの名前空間が実際の配置ディレクトリに合致するよう書き換えてください。

- middleware/database.php を変更する場合は middleware/migration.php 内のクラス名（以下部分）も適宜書き換えてください。  
`$database = new Database($key);`

- middleware/routing.php を変更する場合は、public/index.php および cli.php の \Common\Core\App::start メソッドを実行する前に、プロジェクトディレクトリから routing.php までの相対パス（またはRoutingクラス名）を引数として setRoutingClass メソッドを実行してください。  
例： `$App->setRoutingClass('new/dir/routing.php')->start();`  

- config/app.ini を変更する場合は、public/index.php および cli.php の \Common\Core\App::start メソッドを実行する前に、プロジェクトディレクトリから app.ini までの相対パスを引数として setConfigFile メソッドを実行してください。  
例： `$App->setConfigFile('new/dir/app.ini')->start();`

- docker ディレクトリを変更する場合は、docker-compose.yml 内の関連箇所も適宜書き換えてください。
