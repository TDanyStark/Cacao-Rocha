-- Fix integral de inventario negativo y soporte ajuste_merma
-- Fecha: 2026-03-14
-- Motor esperado: MariaDB/MySQL
--
-- Que hace este script:
-- 1) Crea respaldos de transactions y delete_transactions (una sola vez)
-- 2) Amplia enums para incluir ajuste_merma
-- 3) Corrige ventas/mermas historicas con balance negativo ajustando quantity
-- 4) Recalcula secuencialmente balance_quantity, inventory_price, average_cost y cost_of_sale
-- 5) Si aun quedan negativos: ROLLBACK + SIGNAL de error
-- 6) Si todo queda bien: COMMIT

DROP PROCEDURE IF EXISTS sp_fix_inventory_negative_20260314;

DELIMITER //

CREATE PROCEDURE sp_fix_inventory_negative_20260314()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_id INT;
  DECLARE v_type VARCHAR(30);
  DECLARE v_qty INT;
  DECLARE v_unit_price DECIMAL(20,6);

  DECLARE prev_balance INT DEFAULT 0;
  DECLARE prev_avg_cost DECIMAL(20,6) DEFAULT 0;
  DECLARE prev_inventory_price DECIMAL(20,6) DEFAULT 0;

  DECLARE new_balance INT;
  DECLARE new_avg_cost DECIMAL(20,6);
  DECLARE new_inventory_price DECIMAL(20,6);
  DECLARE new_cost_of_sale DECIMAL(20,6);

  DECLARE v_negative_count INT DEFAULT 0;

  DECLARE cur CURSOR FOR
    SELECT id, type, IFNULL(quantity, 0), unit_price
    FROM transactions
    ORDER BY id ASC;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  START TRANSACTION;

  -- 1) Backups (idempotente: solo se crean si no existen)
  CREATE TABLE IF NOT EXISTS transactions_backup_fix_20260314 AS
  SELECT * FROM transactions;

  CREATE TABLE IF NOT EXISTS delete_transactions_backup_fix_20260314 AS
  SELECT * FROM delete_transactions;

  -- 2) Soporte de nuevo tipo ajuste_merma en ambas tablas
  ALTER TABLE transactions
    MODIFY COLUMN type ENUM('compra','venta','gasto','ajuste_merma') NOT NULL;

  ALTER TABLE delete_transactions
    MODIFY COLUMN type ENUM('compra','venta','gasto','ajuste_merma') NOT NULL;

  -- 3) Corrige solo filas con balance negativo en salidas (venta/merma)
  --    quantity_nueva = quantity_actual + balance_negativo
  --    Ejemplo: qty 18660, balance -60 => qty 18600
  UPDATE transactions
  SET
    detail = CONCAT(detail, ' | FIX 2026-03-14: ajuste por saldo negativo historico'),
    quantity = quantity + balance_quantity,
    total_price = (quantity + balance_quantity) * unit_price
  WHERE type IN ('venta', 'ajuste_merma')
    AND balance_quantity < 0
    AND quantity IS NOT NULL
    AND (quantity + balance_quantity) >= 0;

  -- 4) Recalculo completo del kardex/ledger en orden cronologico por id
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO v_id, v_type, v_qty, v_unit_price;
    IF done = 1 THEN
      LEAVE read_loop;
    END IF;

    SET new_balance = prev_balance;
    SET new_avg_cost = prev_avg_cost;
    SET new_inventory_price = prev_inventory_price;
    SET new_cost_of_sale = NULL;

    IF v_type = 'compra' THEN
      SET new_balance = prev_balance + v_qty;
      SET new_inventory_price = prev_inventory_price + (v_qty * v_unit_price);
      SET new_avg_cost = IF(new_balance > 0, new_inventory_price / new_balance, 0);

    ELSEIF v_type IN ('venta', 'ajuste_merma') THEN
      SET new_balance = prev_balance - v_qty;
      SET new_inventory_price = prev_inventory_price - (v_qty * prev_avg_cost);
      IF new_inventory_price < 0 THEN
        SET new_inventory_price = 0;
      END IF;

      SET new_cost_of_sale = v_qty * prev_avg_cost;
      SET new_avg_cost = IF(new_balance > 0, prev_avg_cost, 0);

    ELSEIF v_type = 'gasto' THEN
      -- Gasto no afecta inventario de kilos
      SET new_balance = prev_balance;
      SET new_inventory_price = prev_inventory_price;
      SET new_avg_cost = prev_avg_cost;
      SET new_cost_of_sale = NULL;
    END IF;

    UPDATE transactions
    SET
      balance_quantity = new_balance,
      inventory_price = ROUND(new_inventory_price, 0),
      average_cost = ROUND(new_avg_cost, 4),
      cost_of_sale = CASE
        WHEN v_type IN ('venta', 'ajuste_merma') THEN ROUND(new_cost_of_sale, 0)
        ELSE NULL
      END,
      total_price = CASE
        WHEN v_type IN ('compra', 'venta', 'ajuste_merma') THEN ROUND(v_qty * v_unit_price, 0)
        ELSE total_price
      END
    WHERE id = v_id;

    SET prev_balance = new_balance;
    SET prev_avg_cost = new_avg_cost;
    SET prev_inventory_price = new_inventory_price;
  END LOOP;

  CLOSE cur;

  -- 5) Validacion final: no deben quedar balances negativos
  SELECT COUNT(*) INTO v_negative_count
  FROM transactions
  WHERE balance_quantity < 0;

  IF v_negative_count > 0 THEN
    ROLLBACK;
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Fix abortado: aun existen balance_quantity negativos. Se hizo ROLLBACK.';
  ELSE
    COMMIT;
  END IF;
END //

DELIMITER ;

-- Ejecutar fix
CALL sp_fix_inventory_negative_20260314();

-- Reportes de verificacion
SELECT 'negativos_restantes' AS metric, COUNT(*) AS value
FROM transactions
WHERE balance_quantity < 0;

SELECT id, type, quantity, unit_price, total_price, inventory_price, balance_quantity, average_cost, cost_of_sale, created_at
FROM transactions
ORDER BY id DESC
LIMIT 30;

-- Limpieza opcional: deja el procedimiento creado para auditoria/reuso.
-- Si deseas eliminarlo despues de correr una vez, descomenta la siguiente linea:
-- DROP PROCEDURE IF EXISTS sp_fix_inventory_negative_20260314;
