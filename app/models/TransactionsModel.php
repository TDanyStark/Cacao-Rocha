<?php
require_once 'config/config.php';

class TransactionsModel
{
  private $pdo;

  public function __construct()
  {
    global $pdo;
    $this->pdo = $pdo;
  }

  public function getTransactions($type = null, $date_start = null, $date_end = null, $min_id = null)
  {
      $type = $type ? "%$type%" : "%"; // Si no hay filtro, buscar todos los tipos

      $sql = "
      SELECT t.*, u.name AS user_name 
      FROM transactions t
      LEFT JOIN users u ON t.user_id = u.id
      WHERE t.type LIKE :type
    ";

      if ($date_start !== null && $date_end !== null) {
        $sql .= " AND DATE(t.created_at) BETWEEN :date_start AND :date_end";
      } elseif ($date_start !== null) {
        $sql .= " AND DATE(t.created_at) >= :date_start";
      } elseif ($date_end !== null) {
        $sql .= " AND DATE(t.created_at) <= :date_end";
      }

      if ($min_id !== null) {
        $sql .= " AND t.id >= :min_id";
      }

      $sql .= " ORDER BY t.created_at DESC";

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':type', $type, PDO::PARAM_STR);
      if ($date_start !== null) {
        $stmt->bindParam(':date_start', $date_start);
      }
      if ($date_end !== null) {
        $stmt->bindParam(':date_end', $date_end);
      }
      if ($min_id !== null) {
        $stmt->bindParam(':min_id', $min_id, PDO::PARAM_INT);
      }
      $stmt->execute();
  
      return $stmt->fetchAll();
  }
  

  public function getInventory()
  {
    $sql = "SELECT 
                SUM(CASE WHEN type IN ('venta', 'ajuste_merma') THEN quantity ELSE 0 END) AS total_quantity_venta,
                SUM(CASE WHEN type = 'compra' THEN quantity ELSE 0 END) AS total_quantity_compra
            FROM transactions";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getCurrentInventoryState(): array
  {
    $lastTransaction = $this->pdo->query("
      SELECT balance_quantity, average_cost, inventory_price
      FROM transactions
      ORDER BY id DESC
      LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    return [
      'balance_quantity' => (int)($lastTransaction['balance_quantity'] ?? 0),
      'average_cost' => (float)($lastTransaction['average_cost'] ?? 0),
      'inventory_price' => (float)($lastTransaction['inventory_price'] ?? 0),
    ];
  }

  public function getLastZeroBalanceTransactionId(): ?int
  {
    $stmt = $this->pdo->query("
      SELECT id
      FROM transactions
      WHERE balance_quantity = 0
      ORDER BY id DESC
      LIMIT 1
    ");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['id'] : null;
  }

  public function deleteTransaction(int $id): bool
  {
    try {
      $this->pdo->beginTransaction();

      // Obtener la transacción antes de eliminarla
  $deletedTransaction = $this->pdo->prepare("
    SELECT * FROM transactions WHERE id = :id
    ");
      $deletedTransaction->execute(['id' => $id]);
      $row = $deletedTransaction->fetch(PDO::FETCH_ASSOC);

      if (!$row) {
        return false; // No se encontró la transacción
      }

      // Obtener el usuario activo de la sesión
      $deletedBy = $_SESSION['user_id'] ?? null;

      // Insertar la transacción en delete_transactions
      $insert = $this->pdo->prepare("
            INSERT INTO delete_transactions (
                id, type, detail, cedula, quantity, unit_price, total_price, 
                inventory_price, balance_quantity, average_cost, 
                cost_of_sale, user_id, created_at, deleted_by
            ) VALUES (
                :id, :type, :detail, :cedula, :quantity, :unit_price, :total_price, 
                :inventory_price, :balance_quantity, :average_cost, 
                :cost_of_sale, :user_id, :created_at, :deleted_by
            )
        ");
      $insert->execute([
        'id' => $row['id'],
        'type' => $row['type'],
        'detail' => $row['detail'],
        'cedula' => $row['cedula'] ?? null,
        'quantity' => $row['quantity'],
        'unit_price' => $row['unit_price'],
        'total_price' => $row['total_price'],
        'inventory_price' => $row['inventory_price'],
        'balance_quantity' => $row['balance_quantity'],
        'average_cost' => $row['average_cost'],
        'cost_of_sale' => $row['cost_of_sale'],
        'user_id' => $row['user_id'],
        'created_at' => $row['created_at'],
        'deleted_by' => $deletedBy
      ]);

      // Eliminar la transacción original
      $stmt = $this->pdo->prepare("DELETE FROM transactions WHERE id = :id");
      $stmt->execute(['id' => $id]);

      // Obtener la última transacción ANTES del ID eliminado
      $lastTransaction = $this->pdo->prepare("
            SELECT balance_quantity, average_cost, inventory_price 
            FROM transactions 
            WHERE id < :id 
            ORDER BY id DESC 
            LIMIT 1
        ");
      $lastTransaction->execute(['id' => $id]);
      $previous = $lastTransaction->fetch(PDO::FETCH_ASSOC);

      // Valores previos (si no hay, se reinicia todo)
      $prev_balance = $previous['balance_quantity'] ?? 0;
      $prev_avg_cost = $previous['average_cost'] ?? 0;
      $prev_inventory_price = $previous['inventory_price'] ?? 0;

      // Recalcular todas las transacciones desde el ID eliminado
      $transactions = $this->pdo->prepare("
            SELECT id, type, quantity, unit_price 
            FROM transactions 
            WHERE id >= :id 
            ORDER BY id ASC
        ");
      $transactions->execute(['id' => $id]);
      $rows = $transactions->fetchAll(PDO::FETCH_ASSOC);

      foreach ($rows as $row) {
        $new_balance = $prev_balance;
        $new_inventory_price = $prev_inventory_price;
        $new_avg_cost = $prev_avg_cost;
        $cost_of_sale = null;

        if ($row['type'] === 'compra') {
          $new_balance += $row['quantity'];
          $new_inventory_price = $prev_inventory_price + ($row['quantity'] * $row['unit_price']);
          $new_avg_cost = ($new_balance > 0) ? ($new_inventory_price / $new_balance) : 0;
        } elseif ($row['type'] === 'venta' || $row['type'] === 'ajuste_merma') {
          $new_balance -= $row['quantity'];
          if ($new_balance < 0) {
            throw new Exception("El recálculo del inventario generó un saldo negativo.");
          }
          $new_inventory_price = $prev_inventory_price - ($row['quantity'] * $prev_avg_cost);
          $new_inventory_price = ($new_inventory_price < 0) ? 0 : $new_inventory_price;
          $cost_of_sale = $row['quantity'] * $prev_avg_cost;
        }

        // Actualizar la transacción recalculada
        $update = $this->pdo->prepare("
                UPDATE transactions 
                SET balance_quantity = :balance_quantity, 
                    inventory_price = :inventory_price, 
                    average_cost = :average_cost, 
                    cost_of_sale = :cost_of_sale 
                WHERE id = :id
            ");
        $update->execute([
          'id' => $row['id'],
          'balance_quantity' => $new_balance,
          'inventory_price' => $new_inventory_price,
          'average_cost' => $new_avg_cost,
          'cost_of_sale' => $cost_of_sale
        ]);

        // Actualizar valores previos para la siguiente iteración
        $prev_balance = $new_balance;
        $prev_inventory_price = $new_inventory_price;
        $prev_avg_cost = $new_avg_cost;
      }

      $this->pdo->commit();
      return true;
    } catch (Exception $e) {
      $this->pdo->rollBack();
      return false;
    }
  }

  public function createTransaction(string $type, string $detail, ?int $quantity, float $unit_price, int $user_id): bool
  {
    // Added optional $cedula parameter is handled by controller; keep signature backward compatible by checking func_get_args if needed
    $args = func_get_args();
    $cedula = $args[5] ?? null;
    // Obtener los últimos valores del inventario
    $lastTransaction = $this->pdo->query("
        SELECT balance_quantity, average_cost, inventory_price 
        FROM transactions 
        ORDER BY id DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    // Valores previos del inventario
    $previous_balance_quantity = $lastTransaction['balance_quantity'] ?? 0;
    $previous_average_cost = $lastTransaction['average_cost'] ?? 0;
    $previous_inventory_price = $lastTransaction['inventory_price'] ?? 0;

    // Inicializar nuevas variables
    $new_balance_quantity = $previous_balance_quantity;
    $new_inventory_price = $previous_inventory_price;
    $new_average_cost = $previous_average_cost;
    $cost_of_sale = null;

    if ($type === 'compra') {
      if ($quantity === null || $quantity <= 0) {
        throw new InvalidArgumentException('La cantidad de compra debe ser mayor a cero.');
      }
      // Nueva cantidad de kilos
      $new_balance_quantity += $quantity;
      // Nuevo precio de inventario
      $new_inventory_price = $previous_inventory_price + ($quantity * $unit_price);
      // Nuevo costo promedio
      $new_average_cost = ($new_balance_quantity > 0) ? ($new_inventory_price / $new_balance_quantity) : 0;
    } elseif ($type === 'venta' || $type === 'ajuste_merma') {
      if ($quantity === null || $quantity <= 0) {
        throw new InvalidArgumentException('La cantidad debe ser mayor a cero.');
      }

      if ($quantity > $previous_balance_quantity) {
        throw new InvalidArgumentException('Stock insuficiente para completar la salida.');
      }

      $effectiveUnitPrice = $type === 'ajuste_merma' ? $previous_average_cost : $unit_price;
      // Nueva cantidad de kilos (se resta)
      $new_balance_quantity -= $quantity;
      // Nuevo precio de inventario
      $new_inventory_price = $previous_inventory_price - ($quantity * $previous_average_cost);
      // si da negativo el new_inventory_price, se pone en 0
      $new_inventory_price = ($new_inventory_price < 0) ? 0 : $new_inventory_price;
      // Costo de venta
      $cost_of_sale = $quantity * $previous_average_cost;
      $unit_price = $effectiveUnitPrice;
    } elseif ($type !== 'gasto') {
      throw new InvalidArgumentException('Tipo de transacción no permitido.');
    }

    // Insertar la transacción con los nuevos valores
    $stmt = $this->pdo->prepare("
        INSERT INTO transactions (type, detail, quantity, unit_price, total_price, user_id, 
                                  cedula, inventory_price, balance_quantity, average_cost, cost_of_sale) 
        VALUES (:type, :detail, :quantity, :unit_price, :total_price, :user_id, 
                :cedula, :inventory_price, :balance_quantity, :average_cost, :cost_of_sale)
    ");

    return $stmt->execute([
      'type' => $type,
      'detail' => $detail,
      'cedula' => $cedula,
      'quantity' => $quantity,
      'unit_price' => $unit_price,
      'total_price' => ($quantity ?? 1) * $unit_price,
      'user_id' => $user_id,
      'inventory_price' => $new_inventory_price,
      'balance_quantity' => $new_balance_quantity,
      'average_cost' => $new_average_cost,
      'cost_of_sale' => $cost_of_sale
    ]);
  }
}
