<?php

class Database
{
    private PDO $pdo;

    /**
     * Конструктор класса.
     * @param string $path Путь к файлу базы данных SQLite.
     */
    public function __construct(string $path)
    {
        $this->pdo = new PDO("sqlite:" . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Выполняет SQL-запрос.
     * @param string $sql SQL-запрос.
     */
    public function Execute(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    /**
     * Выполняет SQL-запрос и возвращает результат в виде массива.
     * @param string $sql SQL-запрос.
     * @return array Ассоциативный массив с результатом.
     */
    public function Fetch(string $sql): array
    {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Создаёт запись в таблице.
     * @param string $table Название таблицы.
     * @param array $data Ассоциативный массив данных.
     * @return int ID новой записи.
     */
    public function Create(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Читает запись из таблицы по ID.
     * @param string $table Название таблицы.
     * @param int $id Идентификатор записи.
     * @return array|null Ассоциативный массив данных или null, если не найдено.
     */
    public function Read(string $table, int $id): ?array
    {
        $sql = "SELECT * FROM $table WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Обновляет запись в таблице.
     * @param string $table Название таблицы.
     * @param int $id Идентификатор записи.
     * @param array $data Ассоциативный массив новых данных.
     */
    public function Update(string $table, int $id, array $data): void
    {
        $fields = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;
        $sql = "UPDATE $table SET $fields WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    /**
     * Удаляет запись по ID.
     * @param string $table Название таблицы.
     * @param int $id Идентификатор записи.
     */
    public function Delete(string $table, int $id): void
    {
        $sql = "DELETE FROM $table WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Возвращает количество записей в таблице.
     * @param string $table Название таблицы.
     * @return int Количество записей.
     */
    public function Count(string $table): int
    {
        $sql = "SELECT COUNT(*) FROM $table";
        $stmt = $this->pdo->query($sql);
        return (int)$stmt->fetchColumn();
    }
}
