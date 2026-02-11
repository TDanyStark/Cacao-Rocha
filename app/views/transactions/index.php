<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard de Transacciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        (function() {
            const theme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            document.documentElement.setAttribute("data-bs-theme", theme);
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-links {
            flex-grow: 1;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
        }

        .badge-compra {
            background-color: #28a745;
        }

        .badge-venta {
            background-color: #007bff;
        }

        .badge-gasto {
            background-color: #dc3545;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                min-height: auto;
            }
        }

        .table-container {
            overflow-x: auto;
        }

        .filter-section {
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }
    </style>

</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse p-0">
                <div class="position-sticky sidebar-sticky">
                    <div class="d-flex flex-column min-vh-100">
                        <div class="p-3 border-bottom">
                            <h5>Dashboard</h5>
                        </div>
                        <div class="sidebar-links p-3">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#">
                                        <i class="fas fa-wallet me-2"></i>
                                        Cuentas
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="sidebar-footer border-top">
                            <?php if (isset($_SESSION['user_name'])): ?>
                                <p class="mb-0">Bienvenido,
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_name']); ?>
                                    </button>

                                </p>
                            <?php endif; ?>
                            <a href="/cacaorocha/auth/logout" class="btn btn-danger w-100 mt-3">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Transacciones</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($isAdmin): ?>
                            <div class="btn-group me-2">
                                <form id="exportForm" action="/cacaorocha/transactions/exportExcel" method="GET" target="_blank">
                                    <!-- Pasamos los filtros actuales al formulario de exportación -->
                                    <?php
                                    $type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';
                                    $date_start = isset($_GET['date_start']) ? htmlspecialchars($_GET['date_start']) : '';
                                    $date_end = isset($_GET['date_end']) ? htmlspecialchars($_GET['date_end']) : '';
                                    ?>
                                    <input type="hidden" name="type" value="<?= $type ?>">
                                    <input type="hidden" name="date_start" value="<?= $date_start ?>">
                                    <input type="hidden" name="date_end" value="<?= $date_end ?>">
                                    <button type="submit" class="btn btn-md btn-outline-secondary">
                                        <i class="fas fa-file-excel me-1"></i> Exportar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <button type="button" class="btn btn-md btn-primary" data-bs-toggle="modal" data-bs-target="#newTransactionModal">
                            <i class="fas fa-plus me-1"></i> Nueva Transacción
                        </button>
                    </div>
                </div>

                <?php if (isset($_SESSION['error_transactions'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error_transactions']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_transactions']); // Eliminar el mensaje después de mostrarlo 
                    ?>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <!-- Filtros -->
                    <div class="filter-section">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="filter-type" class="form-label">Tipo de transacción</label>
                                <select class="form-select" id="filter-type" name="type">
                                    <option value="">Todos los tipos</option>
                                    <option value="compra" <?= $type === 'compra' ? 'selected' : '' ?>>Compra</option>
                                    <option value="venta" <?= $type === 'venta' ? 'selected' : '' ?>>Venta</option>
                                    <option value="gasto" <?= $type === 'gasto' ? 'selected' : '' ?>>Gasto</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter-date-start" class="form-label">Fecha de inicio</label>
                                <input type="date" class="form-control" id="filter-date-start" name="date_start" value="<?= $date_start ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filter-date-end" class="form-label">Fecha de fin</label>
                                <input type="date" class="form-control" id="filter-date-end" name="date_end" value="<?= $date_end ?>">
                                <div class="error-message" id="date-error">La fecha de inicio no puede ser mayor que la fecha de fin</div>
                            </div>
                            <div class="col-md-3" style="display: flex; align-items: end;">
                                <button type="button" id="applyFilters" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Filtrar
                                </button>
                                <button type="button" id="clearFilters" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-broom me-1"></i> Limpiar filtros
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="summary-section p-3 mb-3 rounded" style="max-width: 600px;">
                                <h5 class="text-center fw-bold">Resumen de Transacciones (filtro)</h5>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Total Ventas</strong></td>
                                            <td class="text-end"><?= number_format($total_cantidad_ventas) ?></td>
                                            <td class="text-end">$<?= number_format($total_ventas) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Costo</strong></td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">$<?= number_format($total_cost_of_sales) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Gastos</strong></td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">$<?= number_format($total_gastos) ?></td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <td><strong>Utilidad</strong></td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">$<?= number_format($total_ventas - $total_gastos - $total_cost_of_sales) ?></td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="summary-section p-3 mb-3 rounded" style="max-width: 600px;">
                                <p class="h2">Saldo inventario Q: <span><?= number_format($total_quantity_compras - $total_quantity_ventas) ?></span></p>
                                <hr />
                                <h5 class="text-center fw-bold mt-5">Compras (filtro)</h5>

                                <p class="h6">Compras Q: <span><?= number_format($total_cantidad_compras) ?></span></p>
                                <p class="h6">Compras: <span>$<?= number_format($total_compras) ?></span></p>
                            </div>
                        </div>
                    </div>


                    <div class="table-container">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Detalle</th>
                                    <th>Cédula</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Precio Total</th>
                                    <th>Precio inventario</th>
                                    <th>Balance Cantidad</th>
                                    <th>Costo Promedio</th>
                                    <th>Costo de Venta</th>
                                    <th>Fecha (dia/mes/año)</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <?php
                                        $utcDate = new DateTime($transaction['created_at'], new DateTimeZone('UTC'));
                                        $utcDate->setTimezone(new DateTimeZone('America/Bogota'));
                                        $transaction['created_at'] = $utcDate->format('Y-m-d H:i:s');
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($transaction['id']); ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = '';
                                                switch ($transaction['type']) {
                                                    case 'compra':
                                                        $badgeClass = 'badge-compra';
                                                        break;
                                                    case 'venta':
                                                        $badgeClass = 'badge-venta';
                                                        break;
                                                    case 'gasto':
                                                        $badgeClass = 'badge-gasto';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars(ucfirst($transaction['type'])); ?></span>
                                            </td>
                                            <td>
                                                <span style="white-space: nowrap; display: block;">
                                                    <?= htmlspecialchars($transaction['detail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($transaction['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($transaction['quantity'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span style="white-space: nowrap; display: block;"><?= $transaction['type'] === "compra" ? "" : "- " ?> $<?= number_format($transaction['unit_price']); ?></span>
                                            </td>
                                            <td>
                                                <span style="white-space: nowrap; display: block;"><?= $transaction['type'] === "compra" ? "" : "- " ?> $<?= number_format($transaction['total_price']); ?></span>
                                            </td>
                                            <td>$<?= number_format($transaction['inventory_price']); ?></td>
                                            <td><?= number_format($transaction['balance_quantity']); ?></td>
                                            <td>$<?= number_format($transaction['average_cost']); ?></td>
                                            <td><?= $transaction['cost_of_sale'] ? '$' . number_format($transaction['cost_of_sale']) : ''; ?></td>

                                            <td>
                                                <span style="white-space: nowrap; display: block;">
                                                    <?= date('d/m/Y H:i', strtotime($transaction['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td style="font-size:.8rem;"><?= $transaction['user_name']; ?></td>

                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-danger delete-transaction" data-id="<?= $transaction['id']; ?>" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="14" class="text-center py-3">No hay transacciones disponibles.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal para nueva transacción -->
    <div class="modal fade" id="newTransactionModal" tabindex="-1" aria-labelledby="newTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTransactionModalLabel">Nueva Transacción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newTransactionForm" action="/cacaorocha/transactions/create" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="transaction-type" class="form-label">Tipo de transacción</label>
                                <select class="form-select" id="transaction-type" name="type" required>
                                    <option value="" selected disabled>Seleccionar tipo</option>
                                    <option value="compra">Compra</option>
                                    <option value="venta">Venta</option>
                                    <option value="gasto">Gasto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="transaction-quantity" class="form-label">Cantidad <span id="quantity-preview"></span></label>
                                <input type="text" class="form-control" id="transaction-quantity" name="quantity" inputmode="decimal" pattern="[0-9.]*" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="transaction-unit-price" class="form-label">Precio Unitario <span id="unit-price-preview"></span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="transaction-unit-price" name="unit_price" inputmode="decimal" pattern="[0-9.]*" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="transaction-cedula" class="form-label">Cédula (opcional)</label>
                                <input type="text" class="form-control" id="transaction-cedula" name="cedula" placeholder="Cédula del comprador">
                            </div>
                        </div>
                        <!-- Agregado en el modal de nueva transacción -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5 class="">
                                    Precio Total:
                                    <span id="transaction-total">$0</span>
                                </h5>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="transaction-detail" class="form-label">Detalle</label>
                            <textarea class="form-control" id="transaction-detail" name="detail" rows="3" required></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="newTransactionForm" class="btn btn-md btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambio de contraseña -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm" action="/cacaorocha/auth/reset_password" method="POST">
                        <div class="mb-3">
                            <label for="current-password" class="form-label">Contraseña Actual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current-password" name="current_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current-password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new-password" class="form-label">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new-password" name="new_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new-password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-password" class="form-label">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm-password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="resetPasswordForm" class="btn btn-md btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success_transactions'])): ?>
                Swal.fire({
                    title: '¡Éxito!',
                    text: '<?= htmlspecialchars($_SESSION['success_transactions']); ?>',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
                <?php unset($_SESSION['success_transactions']); // Eliminar el mensaje después de mostrarlo
                ?>
            <?php endif; ?>
            // Aplicar filtros
            document.getElementById('applyFilters').addEventListener('click', function() {
                const type = document.getElementById('filter-type').value;
                const dateStart = document.getElementById('filter-date-start').value;
                const dateEnd = document.getElementById('filter-date-end').value;

                // Validar que la fecha de inicio no sea mayor a la fecha de fin
                if (dateStart && dateEnd && dateStart > dateEnd) {
                    document.getElementById('date-error').classList.add('show');
                    return;
                } else {
                    document.getElementById('date-error').classList.remove('show');
                }

                // Construir URL con parámetros
                const params = new URLSearchParams();
                if (type) params.append('type', type);
                if (dateStart) params.append('date_start', dateStart);
                if (dateEnd) params.append('date_end', dateEnd);

                // Redirigir con los parámetros
                window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            });

            // Limpiar filtros
            document.getElementById('clearFilters').addEventListener('click', function() {
                document.getElementById('filter-type').value = '';
                document.getElementById('filter-date-start').value = '';
                document.getElementById('filter-date-end').value = '';
                document.getElementById('date-error').classList.remove('show');

                // Redirigir sin parámetros
                window.location.href = window.location.pathname;
            });

            // Calcular precio total en el formulario
            document.getElementById('transaction-quantity').addEventListener('input', updateTotal);
            document.getElementById('transaction-unit-price').addEventListener('input', updateTotal);

            function updateTotal() {
                const quantity = parseFloat(document.getElementById('transaction-quantity').value) || 0;
                const unitPrice = parseFloat(document.getElementById('transaction-unit-price').value) || 0;
                const total = quantity * unitPrice;
                // Si quisiéramos mostrar el total calculado en algún lado:
                // document.getElementById('transaction-total').textContent = '$' + total.toFixed(2);
            }

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('transaction-quantity');
            const unitPriceInput = document.getElementById('transaction-unit-price');
            const totalPriceDisplay = document.getElementById('transaction-total');
            const quantityPreview = document.getElementById('quantity-preview');
            const unitPricePreview = document.getElementById('unit-price-preview');

            function formatNumber(value, decimals = 0) {
                const number = typeof value === "number" ? value : toNumber(value);
                if (!Number.isFinite(number)) return "0";

                const fixed = decimals > 0 ? number.toFixed(decimals) : Math.round(number).toString();
                const parts = fixed.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                return parts.join('.');
            }

            function normalizeDecimalInput(value) {
                if (typeof value !== "string") return "";

                let cleaned = value.replace(',', '.').replace(/[^0-9.]/g, '');
                const firstDotIndex = cleaned.indexOf('.');
                if (firstDotIndex !== -1) {
                    cleaned = cleaned.slice(0, firstDotIndex + 1) + cleaned.slice(firstDotIndex + 1).replace(/\./g, '');
                }

                return cleaned;
            }

            function toNumber(value) {
                const number = parseFloat(value);
                return isNaN(number) ? 0 : number;
            }

            function updateInputPreserveCaret(input, nextValue) {
                const previousValue = input.value;
                if (previousValue === nextValue) return;

                const start = input.selectionStart ?? previousValue.length;
                const diff = previousValue.length - nextValue.length;
                const nextPos = Math.max(0, start - diff);

                input.value = nextValue;
                input.setSelectionRange(nextPos, nextPos);
            }

            function updateTotal() {
                const quantity = toNumber(normalizeDecimalInput(quantityInput.value));
                const unitPrice = toNumber(normalizeDecimalInput(unitPriceInput.value));
                const total = quantity * unitPrice;
                totalPriceDisplay.textContent = `$${formatNumber(total, 2)}`;
            }

            unitPriceInput.addEventListener('input', function(e) {
                const normalized = normalizeDecimalInput(this.value);
                updateInputPreserveCaret(this, normalized);
                unitPricePreview.textContent = `($${formatNumber(normalized, 2)})`;
                updateTotal();
            });


            quantityInput.addEventListener('input', function(e) {
                const normalized = normalizeDecimalInput(this.value);
                updateInputPreserveCaret(this, normalized);
                quantityPreview.textContent = `(${formatNumber(normalized, 2)})`;
                updateTotal();
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const transactionType = document.getElementById("transaction-type");
            const quantityField = document.getElementById("transaction-quantity").closest(".col-md-6");

            transactionType.addEventListener("change", function() {
                if (this.value === "gasto") {
                    quantityField.style.display = "none";
                    document.getElementById("transaction-quantity").value = "1"; // Limpia el campo
                } else {
                    quantityField.style.display = "block";
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-transaction').forEach(button => {
                button.addEventListener('click', function() {
                    const transactionId = this.getAttribute('data-id');

                    Swal.fire({
                        title: "¿Estás seguro?",
                        text: "Esta acción no se puede deshacer.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Sí, eliminar",
                        cancelButtonText: "Cancelar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/cacaorocha/transactions/delete', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `id=${transactionId}`
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire("Eliminado", "La transacción ha sido eliminada.", "success").then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire("Error", data.error || "No se pudo eliminar la transacción.", "error");
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire("Error", "Hubo un problema con la solicitud.", "error");
                                });
                        }
                    });
                });
            });
        });
    </script>

    <!-- script para mirar las contraseñas -->
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
    </script>

    <!-- script para manejar los formularios -->
    <script>
        // Función para manejar el envío de formularios
        function handleFormSubmit() {
            // Seleccionar todos los formularios en la página
            const forms = document.querySelectorAll('form');

            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    // Buscar el botón de envío dentro del formulario o relacionado con él
                    let submitButton;

                    // Primero intentar encontrar el botón dentro del formulario
                    submitButton = this.querySelector('button[type="submit"]');

                    // Si no lo encuentra, buscar botones externos que usen form attribute
                    if (!submitButton) {
                        const formId = this.getAttribute('id');
                        if (formId) {
                            submitButton = document.querySelector(`button[form="${formId}"]`);
                        }
                    }

                    if (submitButton) {
                        // Guardar el texto original del botón
                        const originalText = submitButton.innerHTML;

                        // Deshabilitar el botón
                        submitButton.disabled = true;

                        // Mostrar spinner de carga
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';

                        // Opcional: Restaurar el botón después de un tiempo (por si hay error y no se recarga la página)
                        setTimeout(() => {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalText;
                        }, 30000); // 30 segundos de timeout por si algo falla
                    }
                });
            });
        }

        // Ejecutar cuando el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            handleFormSubmit();
        });
    </script>
</body>

</html>