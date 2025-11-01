-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 01-11-2025 a las 01:28:11
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u744125515_cacaorocha`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `delete_transactions`
--

CREATE TABLE `delete_transactions` (
  `id` int(11) NOT NULL,
  `type` enum('compra','venta','gasto') NOT NULL,
  `detail` text NOT NULL,
  `cedula` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(11,0) NOT NULL,
  `total_price` decimal(11,0) DEFAULT NULL,
  `inventory_price` decimal(15,0) DEFAULT NULL,
  `balance_quantity` int(11) DEFAULT NULL,
  `average_cost` decimal(11,4) DEFAULT NULL,
  `cost_of_sale` decimal(15,0) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `delete_transactions`
--

INSERT INTO `delete_transactions` (`id`, `type`, `detail`, `cedula`, `quantity`, `unit_price`, `total_price`, `inventory_price`, `balance_quantity`, `average_cost`, `cost_of_sale`, `user_id`, `created_at`, `deleted_by`) VALUES
(29, 'compra', 'twew', NULL, 1000, 15600, 15600000, 15600000, 1000, 15600.0000, NULL, 1, '2025-03-11 11:57:56', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `type` enum('compra','venta','gasto') NOT NULL,
  `detail` text NOT NULL,
  `cedula` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(11,0) NOT NULL,
  `total_price` decimal(11,0) DEFAULT NULL,
  `inventory_price` decimal(15,0) DEFAULT NULL,
  `balance_quantity` int(11) DEFAULT NULL,
  `average_cost` decimal(11,4) DEFAULT NULL,
  `cost_of_sale` decimal(15,0) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Daniel Amado', 'daniel.amadove@gmail.com', '123456', 'admin', '2025-03-06 01:27:49'),
(2, 'Josue Amado', 'josue.amadove@gmail.com', '123456', 'admin', '2025-03-06 19:56:26'),
(3, 'Rolando Rocha', 'rolando.rocha@gmail.com', '123456', 'admin', '2025-03-10 23:17:41'),
(4, 'Julieta Rocha', 'julieta.rocha@gmail.com', 'cacaorocha', 'user', '2025-03-10 23:17:41'),
(5, 'Sandra Lozada', 'sandra.lozada@gmail.com', 'cacaorocha', 'user', '2025-03-10 23:17:41');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `delete_transactions`
--
ALTER TABLE `delete_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_delete_transactions_user` (`user_id`),
  ADD KEY `fk_delete_transactions_deleted_by` (`deleted_by`);

--
-- Indices de la tabla `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `delete_transactions`
--
ALTER TABLE `delete_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `delete_transactions`
--
ALTER TABLE `delete_transactions`
  ADD CONSTRAINT `fk_delete_transactions_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_delete_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
