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

  public function getTransactions()
  {
    $stmt = $this->pdo->prepare("SELECT * FROM transactions");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function getTransactionsByUserId($userId)
  {
    $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
  }

  public function createTransaction(string $type, string $detail, int $quantity, float $unit_price, int $user_id): bool
  {
    $stmt = $this->pdo->prepare("INSERT INTO transactions (type, detail, quantity, unit_price, user_id) 
                                                    VALUES (:type, :detail, :quantity, :unit_price, :user_id)");

    return $stmt->execute([
      'type' => $type,
      'detail' => $detail,
      'quantity' => $quantity,
      'unit_price' => $unit_price,
      'user_id' => $user_id
    ]);
  }
}
