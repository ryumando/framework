<?php

namespace Common\Core;

/**
 * データベースクラス
 */
class Database extends Base
{
    /** @var array データベース接続情報 */
    protected static $databases;

    /** @var string データベース接続情報キー */
    private $key;

    /** @var object PDOStatement オブジェクト */
    private $stmt;

    /** @var string データベースドライバー */
    private $driver;

    /** @var array データベースハンドラ保持配列 */
    private static $dbh = [];

    /** @var string マイグレーション履歴保存テーブル */
    private $migration_table = 'migration_history';

    /**
     * コンストラクタ
     *
     * @param string|null $key
     */
    public function __construct(string $key = null)
    {
        if ($key !== null) $this->connect($key);
    }

    /**
     * 接続
     *
     * @param string $key
     * @return object
     */
    public function connect(string $key): object
    {
        if (is_array(self::$databases) && isset(self::$databases[$key])) $database = self::$databases[$key];
        if (empty($database)) throw new \Exception('could not find database settings');
        if (isset($database['driver'])) $driver = strtolower($database['driver']);
        if ($driver === 'postgres' || $driver === 'postgresql') $driver = 'pgsql';
        if (empty(self::$dbh[$key])) {
            $dsn = '';
            if ($driver === 'sqlite') $dsn .= $database['dbname'];
            foreach ($database as $k => $v) {
                if ($v !== null && $k !== 'driver' && $k !== 'user' && $k !== 'password' && $k !== 'option' && ($k !== 'dbname' || $driver !== 'sqlite')) {
                    if (!empty($dsn)) $dsn .= ';';
                    $dsn .= $k . '=' . $v;
                }
            }
            $user = isset($database['user']) ? $database['user'] : null;
            $password = isset($database['password']) ? $database['password'] : null;
            $option = isset($database['option']) ? $database['option'] : null;
            try {
                self::$dbh[$key] = new \PDO($driver . ':' . $dsn, $user, $password, $option);
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        $this->key = $key;
        $this->driver = $driver;
        return $this;
    }

    /**
     * 切断
     *
     * @return void
     */
    public function close(): void
    {
        $this->stmt = null;
        self::$dbh[$this->key] = null;
        $this->driver = null;
        $this->key = null;
    }

    /**
     * クエリ実行
     *
     * @param string $query
     * @param array $params
     * @return object
     */
    public function query(string $query, ...$args): object
    {
        if (is_object($this->stmt)) $this->stmt->closeCursor();
        if (empty($args)) {
            $this->stmt = self::$dbh[$this->key]->query($query);
        } else {
            if (is_array($args[0]) && count($args) === 1) {
                $params = $args[0];
            } else {
                $params = $args;
            }
            $this->stmt = self::$dbh[$this->key]->prepare($query);
            foreach ($params as $k => $v) {
                if (is_int($k)) $k++;
                $this->stmt->bindValue($k, $v);
            }
            $this->stmt->execute();
        }
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $this;
    }

    /**
     * クエリ結果セット取得
     *
     * @return object
     */
    public function result()
    {
        return $this->stmt;
    }

    /**
     * クエリ結果取得
     *
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key = null)
    {
        $result = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        if (!is_array($result)) return null;
        if ($key !== null) return $result[$key];
        if (count($result) === 1) return array_shift($result);
        return $result;
    }

    /**
     * クエリ結果数取得
     *
     * @return int
     */
    public function count()
    {
        if ($this->driver === 'sqlite') return null;
        return $this->stmt->rowCount();
    }

    /**
     * 複数クエリ実行
     *
     * @param string $query
     * @return int
     */
    public function exec(string $query)
    {
        return self::$dbh[$this->key]->exec($query);
    }

    /**
     * エスケープ
     *
     * @param string $string
     * @return string
     */
    public function escape(string $string): string
    {
        return self::$dbh[$this->key]->quote($string);
    }

    /**
     * トランザクション開始
     *
     * @return bool
     */
    public function startTransaction()
    {
        return self::$dbh[$this->key]->beginTransaction();
    }

    /**
     * コミット
     *
     * @return bool
     */
    public function commit()
    {
        return self::$dbh[$this->key]->commit();
    }

    /**
     * ロールバック
     *
     * @return bool
     */
    public function rollback()
    {
        return self::$dbh[$this->key]->rollback();
    }

    /**
     * マイグレーション実行
     *
     * @param string $path
     * @return integer
     */
    public function migration(string $path): int
    {
        $this->createMigrationTable();
        $executed = 0;
        $path = App::$project_path . '/' . preg_replace('#^\.?/|/$/#', '', $path) . '/';
        $files = scandir($path);
        foreach ($files as $file) {
            if (is_file($path . $file) && $this->query('SELECT migration_file_name FROM ' . $this->migration_table . ' WHERE migration_file_name = :migration_file_name LIMIT 1', ['migration_file_name' => $file])->fetch() === null) {
                $this->exec(file_get_contents($path . $file));
                $this->query('INSERT INTO ' . $this->migration_table . '(migration_file_name, migration_datetime) VALUES(:migration_file_name, CURRENT_TIMESTAMP)', ['migration_file_name' => $file]);
                $executed++;
            }
        }
        return $executed;
    }

    /**
     * マイグレーション履歴保存テーブル設定
     *
     * @param string $migration_table_name
     * @return object
     */
    public function setMigrationTable(string $migration_table_name): object
    {
        $this->migration_table = $migration_table_name;
        return $this;
    }

    /**
     * マイグレーション履歴保存テーブル生成
     *
     * @return void
     */
    private function createMigrationTable(): void
    {
        $datetime_type = $this->driver === 'mysql' ? 'DATETIME' : 'TIMESTAMP';
        $this->query('CREATE TABLE IF NOT EXISTS ' . $this->migration_table . ' (migration_file_name VARCHAR(255) PRIMARY KEY, migration_datetime ' . $datetime_type . ')');
    }

}