<?php

namespace app\models;

use PDO;
use general\Config;
use general\Database\Database;

/**
 * Класс базовой модели
 */
class Model
{
    const TABLE = null;

	protected PDO $db;

	public function __construct()
    {
        $config = Config::get('database');
        $this->db = (new Database($config))->getConnection();
    }

    /**
     * Создание записи
     *
	 * @param array $data Входные данные
	 * @return int Статус результата
	 */
	public function create(array $data): int
	{
		$columns = implode(', ', array_keys($data));
		$placeholders = ':' . implode(', :', array_keys($data));
		$query = "INSERT INTO " . static::TABLE . " ({$columns}) VALUES ({$placeholders})";

		$stmt = $this->db->prepare($query);

		foreach ($data as $key => $value) {
			$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
			$stmt->bindValue(":$key", $value, $type);
		}

		if ($stmt->execute()) {
			$lastInsertedId = $this->db->lastInsertId();
			return $lastInsertedId ? (int)$lastInsertedId : 0;
		} else {
			return 0;
		}
	}

	/**
     * Создание нескольких записей
     *
	 * @param array $rows Входные данные
	 * @return bool Статус результата
	 */
	public function createMultiple(array $rows): bool
	{
		// Проверяем, не пустой ли массив данных
		if (empty($rows)) {
			return false;
		}

		$columns = implode(', ', array_keys($rows[0]));
		$values = [];
		$bindValues = [];

		foreach ($rows as $rowIndex => $row) {
			$rowValues = [];
			foreach ($row as $columnName => $value) {
				$param = ":{$columnName}_{$rowIndex}";
				$rowValues[] = $param;
				$bindValues[$param] = $value; // Собираем значения для подготовленного запроса
			}
			$values[] = '(' . implode(', ', $rowValues) . ')'; // Формируем строку значений для каждой строки
		}

		$valuesString = implode(', ', $values); // Объединяем все строки в одну строку запроса
		$query = "INSERT INTO " . static::$table . " ({$columns}) VALUES {$valuesString}";

		$stmt = $this->db->prepare($query); // Подготавливаем запрос

		// Привязываем значения параметров
		foreach ($bindValues as $key => $value) {
			$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
			$stmt->bindValue($key, $value, $type);
		}

		return $stmt->execute(); // Выполняем запрос и возвращаем результат
	}

	/**
     * Получение записи
     *
	 * @param string $field Поле
	 * @param mixed $value Значение
	 * @param bool $withUniqueByCase Статус уникальности значения по регистру
	 * @return mixed
	 */
	public function read(string $field, mixed $value, bool $withUniqueByCase = false): mixed
	{
		if ($withUniqueByCase) {
			$value = mb_strtolower($value);
			$field = "LOWER({$field})";
		}

		$query = "SELECT * FROM " . static::$table . " WHERE {$field} = :{$field}";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(":$field", $value);
		$stmt->execute();

		return $stmt->fetch();
	}

	/**
     * Обновление записи
     *
	 * @param int $id ID записи
	 * @param array $data Поля со значениями для обновления
	 * @return bool Статус обновления
	 */
	public function update(int $id, array $data): bool
	{
		$updates = '';
		foreach ($data as $key => $value) {
			$updates .= "{$key} = :{$key}, ";
		}
		$updates = rtrim($updates, ', ');

		$query = "UPDATE " . static::$table . " SET {$updates} WHERE id = :id";

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id', $id);

		foreach ($data as $key => $value) {
			$stmt->bindValue(":$key", $value);
		}

		return $stmt->execute();
	}

	/**
     * Удаление записи
     *
	 * @param int $id ID записи
	 * @return bool Статус результата
	 */
	public function delete(int $id): bool
	{
		$query = "DELETE FROM " . static::$table . " WHERE id = :id";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id', $id);

		return $stmt->execute();
	}

	/**
     * Осуществляет поиск по всей таблице
     *
	 * @param array $filters Массив фильтров
	 * @param string $sortField Поле для сортировки
	 * @param string $sortDirection Направление сортировки
	 * @param int $page Номер страницы
	 * @param int $perPage Количество элементов на страницу
	 * @return array Результирующий массив
	 */
	public function findAll(
		array  $filters = [],
		string $sortField = 'created_at',
		string $sortDirection = 'DESC',
		int    $page = 1,
		int    $perPage = 10
	): array
	{
		$whereConditions = [];
		$queryParams = [];

		foreach ($filters as $field => $value) {
			// Для диапазона значений ($value - массив с ключами 'from' и 'to')
			if (is_array($value) && isset($value['from']) && isset($value['to'])) {
				$whereConditions[] = "{$field} BETWEEN :{$field}_from AND :{$field}_to";
				$queryParams["{$field}_from"] = $value['from'];
				$queryParams["{$field}_to"] = $value['to'];
            // Для текстового поиска (можно использовать LIKE для частичного совпадения)
			} elseif (is_string($value)) {
				$whereConditions[] = "{$field} LIKE :{$field}";
				$queryParams[$field] = "%$value%";
            // Для числовых значений
			} elseif (is_numeric($value)) {
				$whereConditions[] = "{$field} = :{$field}";
				$queryParams[$field] = $value;
			}
		}

		$whereSQL = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';

		// Запрос для подсчета общего количества элементов
		$countQuery = "SELECT COUNT(*) FROM " . static::$table . $whereSQL;
		$countStmt = $this->db->prepare($countQuery);
		foreach ($queryParams as $param => $value) {
			$countStmt->bindValue(":{$param}", $value);
		}
		$countStmt->execute();
		$totalCount = $countStmt->fetchColumn(); // Получаем общее количество элементов

		// Сортировка
		$sortSQL = " ORDER BY {$sortField} {$sortDirection}";
		$offset = ($page - 1) * $perPage;
		$limitSQL = " LIMIT {$perPage} OFFSET {$offset}";

		$query = "SELECT * FROM " . static::$table . $whereSQL . $sortSQL . $limitSQL;
		$stmt = $this->db->prepare($query);

		foreach ($queryParams as $param => $value) {
			$stmt->bindValue(":{$param}", $value);
		}

		$stmt->execute();
		$items = $stmt->fetchAll() ?? [];

		// Возвращаем как результаты запроса, так и общее количество элементов
		return ['items' => $items, 'totalCount' => $totalCount];
	}
}