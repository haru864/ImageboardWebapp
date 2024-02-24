<?php

namespace Database\DataAccess;

use Database\DatabaseManager;
use InvalidArgumentException;
use RuntimeException;

abstract class ORM
{
    protected static ?array $columnTypes = null;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];

    // マジックメソッド__setおよび__getをオーバーロードすることで、インスタンス変数の構文にアクセスできるようになります。
    // $ormObject->idと書くと、$this->attributes['id']を返します。
    // https://www.php.net/manual/en/language.oop5.magic.php を参照ください。
    public function __set($name, $value)
    {
        if (key_exists($name, static::$columnTypes)) {
            $this->attributes[$name] = $value;
        }
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }
    // construct with
    public function __construct(array $data = [])
    {
        // カラムは{カラム名}-{データ型}のキーと値のペアです。
        // PHPは順序付きハッシュテーブルを使用しているので、連想配列を順番に保持することに注意してください。
        if (static::$columnTypes === null) {
            static::$columnTypes = $this->fetchColumnTypes();
        }
        foreach ($data as $key => $value) {
            if (array_key_exists($key, static::$columnTypes) || $key === static::$primaryKey) {
                $this->attributes[$key] = $value;
            } else throw new InvalidArgumentException(sprintf("%s does not exist as %'s column", $key, static::class));
        }
        var_dump($this->attributes);
    }

    protected static function getTableName(): string
    {
        $classname = strtolower(basename(str_replace('\\', DIRECTORY_SEPARATOR, static::class)));
        $last_letter = strtolower($classname)[strlen($classname) - 1];
        $plural = ($last_letter === 'y' ? 'ies' : ($last_letter === 's' ? 'es' : 's'));
        return $classname . $plural;
    }

    protected function fetchColumnTypes(): array
    {
        $db = DatabaseManager::getMysqliConnection();
        $columnTypes = [];
        // SHOW COLUMNSクエリは、カラム名やカラムのデータ型など、各カラムに関する特定のデータを返します。
        // これを使って、{列名}-{データ型}の連想配列をセットアップします。
        $result = $db->query("SHOW COLUMNS FROM {$this->getTableName()}");
        while ($row = $result->fetch_assoc()) {
            // var_dump($row);
            if ($row['Field'] === static::$primaryKey) continue;
            $columnTypes[$row['Field']] = $this->getColumnType($row['Type']);
        }
        // var_dump($result);
        // var_dump($columnTypes);
        return $columnTypes;
    }

    protected function getColumnType($type): string
    {
        if (str_contains($type, 'int')) return 'i';
        else if (str_contains($type, 'double') || str_contains($type, 'float') || str_contains($type, 'decimal')) return 'd';
        else return 's';
    }


    public static function create(array $data): ORM
    {
        $db = DatabaseManager::getMysqliConnection();

        $object = new static($data);
        $columnNames = implode(', ', array_keys($object->attributes));
        $placeholders = implode(', ', array_fill(0, count($object->attributes), '?'));

        $stmt = $db->prepare("INSERT INTO {$object->getTableName()} ({$columnNames}) VALUES ({$placeholders})");

        if (!$stmt) throw new RuntimeException(sprintf("Failed to create row for %s", static::class));

        // PHPの連想配列は順序付きハッシュテーブルなので、array_valuesやarray_keysは挿入された順番で項目を返します。
        $stmt->bind_param(implode(array_values(static::$columnTypes)), ...array_values($object->attributes));
        $stmt->execute();

        $object->__set(static::$primaryKey, $db->insert_id);
        return $object;
    }

    public static function find(int $id): ?ORM
    {
        $db = DatabaseManager::getMysqliConnection();
        $tableName = static::getTableName();
        $primaryKey = static::$primaryKey;

        $stmt = $db->prepare("SELECT * FROM {$tableName} WHERE {$primaryKey} = ?");

        if (!$stmt) throw new RuntimeException(sprintf("Failed to find row for %s id %s", static::class));

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        // echo "find({$id})" . PHP_EOL;
        // var_dump($data) . PHP_EOL;
        return $data !== null ? new static($data) : null;
    }

    public function update(array $data): ORM
    {
        $db = DatabaseManager::getMysqliConnection();

        $columns = [];
        $values = [];
        $types = '';
        $primaryKey = static::$primaryKey;

        foreach ($data as $key => $value) {
            if ($key !== $primaryKey && isset(static::$columnTypes[$key])) {
                $columns[] = "{$key} = ?";
                $values[] = $value;
                $types .= static::$columnTypes[$key];
            }
        }


        $sql = "UPDATE {$this->getTableName()} SET " . implode(', ', $columns) . " WHERE {$primaryKey} = ?";
        $stmt = $db->prepare($sql);

        if (!$stmt) throw new RuntimeException(sprintf("Failed to update data for %s id %s", static::class, $this->id));

        // 主キーを追加します。
        $types .= 'i';
        $values[] = $this->id;
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        return $this;
    }

    public function delete(): bool
    {
        $tableName = static::getTableName();
        $primaryKey = static::$primaryKey;

        if (!isset($this->attributes[static::$primaryKey])) return false;

        $primaryKeyValue = $this->attributes[$primaryKey];
        $db = DatabaseManager::getMysqliConnection();


        $stmt = $db->prepare("DELETE FROM {$tableName} WHERE {$primaryKey} = ?");

        if (!$stmt) throw new RuntimeException(sprintf("Failed to prepare to delete row for %s id %s", static::class, $primaryKeyValue));

        $stmt->bind_param('i', $primaryKeyValue);
        return $stmt->execute();
    }
}
