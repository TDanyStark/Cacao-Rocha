<?php
require_once 'app/helpers/AuthHelper.php';
require_once 'app/models/TransactionsModel.php';

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransactionsController
{
    public function index()
    {
        checkAuth();
        try {
            $transactionsModel = new TransactionsModel();

            // Obtener filtros de la URL
            $type = $_GET['type'] ?? null;
            $date_start = !empty($_GET['date_start']) ? $_GET['date_start'] : null;
            $date_end = !empty($_GET['date_end']) ? $_GET['date_end'] : null;

            // Validar fechas
            if ($date_start && $date_end && strtotime($date_start) > strtotime($date_end)) {
                $_SESSION['error_transactions'] = "El filtro de inicio no puede ser mayor al de fin.";
                header("Location: /cacaorocha/transactions");
                exit;
            }

            $transactions = $transactionsModel->getTransactions($type, $date_start, $date_end);
            $total_compras = 0;
            $total_ventas = 0;
            $total_gastos = 0;
            $total_cantidad_compras = 0;
            $total_cantidad_ventas = 0;
            $total_cost_of_sales = 0;

            foreach ($transactions as $transaction) {
                switch ($transaction['type']) {
                    case 'compra':
                        $total_compras += $transaction['total_price'];
                        $total_cantidad_compras += $transaction['quantity'];
                        break;
                    case 'venta':
                        $total_ventas += $transaction['total_price'];
                        $total_cantidad_ventas += $transaction['quantity'];
                        $total_cost_of_sales += $transaction['cost_of_sale'];
                        break;
                    case 'gasto':
                        $total_gastos += $transaction['total_price'];
                        break;
                }
            }

            $inventory = $transactionsModel->getInventory();
            $total_quantity_ventas = $inventory['total_quantity_venta'];
            $total_quantity_compras = $inventory['total_quantity_compra'];

            $isAdmin = $_SESSION['role'] == 'admin';

            require_once 'app/views/transactions/index.php';
        } catch (Exception $e) {
            Logger::error("Error en TransactionsController::index - " . $e->getMessage());
            require_once 'app/views/error/index.php';
            exit;
        }
    }

    public function create($data)
    {
        try {
            $transactionsModel = new TransactionsModel();
            $user_id = $_SESSION['user_id'];
            $type = $data['type'] ?? null;
            $detail = $data['detail'] ?? null;
            $quantity = $data['quantity'] ?? null;
            $unit_price = $data['unit_price'] ?? null;

            // si el type es gasto, la cantidad debe ser null
            if ($type == 'gasto') {
                $quantity = null;
            }

            // quitarle los puntos y comas al precio unitario
            $unit_price = str_replace(',', '', $unit_price);
            $unit_price = str_replace('.', '', $unit_price);

            if ($transactionsModel->createTransaction(
                $type,
                $detail,
                $quantity,
                $unit_price,
                $user_id
            )) {
                $_SESSION['success_transactions'] = "Transacción creada exitosamente.";
                header("Location: /cacaorocha/transactions"); // Redirigir si se creó exitosamente
                exit;
            } else {
                $_SESSION['error_transactions'] = "No se pudo crear la transacción.";
                header("Location: /cacaorocha/transactions");
                exit;
            }
        } catch (Exception $e) {
            Logger::error("Error en TransactionsController::create - " . $e->getMessage());
            $_SESSION['error_transactions'] = "Ocurrió un error al intentar crear la transacción.";
            header("Location: /cacaorocha/transactions");
            exit;
        }
    }

    public function delete()
    {
        try {
            if (isset($_POST['id'])) {
                $transactionId = intval($_POST['id']);
                $transactionsModel = new TransactionsModel();

                if ($transactionsModel->deleteTransaction($transactionId)) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => "No se pudo eliminar la transacción."]);
                }
            } else {
                echo json_encode(["success" => false, "error" => "Solicitud no válida."]);
            }
        } catch (Exception $e) {
            Logger::error("Error en TransactionsController::delete - " . $e->getMessage());
            echo json_encode(["success" => false, "error" => "Ocurrió un error en el servidor."]);
        }
        exit;
    }

    public function exportExcel()
    {
        try {
            $transactionsModel = new TransactionsModel();
            // Obtener filtros de la URL
            $type = $_GET['type'] ?? null;
            $date_start = !empty($_GET['date_start']) ? $_GET['date_start'] : null;
            $date_end = !empty($_GET['date_end']) ? $_GET['date_end'] : null;


            // Validar fechas
            if ($date_start && $date_end && strtotime($date_start) > strtotime($date_end)) {
                $_SESSION['error_transactions'] = "El filtro de inicio no puede ser mayor al de fin.";
                header("Location: /cacaorocha/transactions");
                exit;
            }

            $transactions = $transactionsModel->getTransactions($type, $date_start, $date_end);

            if (empty($transactions)) {
                die("No hay transacciones para exportar.");
            }

            // Crear una nueva hoja de cálculo
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $headers = ["ID", "Tipo", "Detalle", "Cantidad", "Precio Unitario", "Precio Total", "Usuario", "Fecha"];
            $sheet->fromArray($headers, NULL, 'A1');

            // Agregar los datos
            $row = 2;
            foreach ($transactions as $transaction) {
                $type = $transaction['type'];
                $unit_price = $transaction['unit_price'];
                $total_price = $transaction['total_price'];
                if ($type == 'gasto' || $type == 'venta') {
                    $unit_price = '-' . $unit_price;
                    $total_price = '-' . $total_price;
                }

                $sheet->fromArray([
                    $transaction['id'],
                    $transaction['type'],
                    $transaction['detail'],
                    $transaction['quantity'],
                    $unit_price,
                    $total_price,
                    $transaction['user_id'],
                    $transaction['created_at'],
                ], NULL, 'A' . $row);
                $row++;
            }

            ob_end_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="transactions.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            Logger::error("Error en TransactionsController::exportExcel - " . $e->getMessage());
            echo "Error al exportar: " . $e->getMessage();
        }
    }
}
