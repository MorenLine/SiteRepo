<?php
require_once '../config.php';
session_start();

// Проверяем авторизацию
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>Управление заказами</h1>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: #666; font-weight: bold;">Пользователь: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" style="padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 5px; text-decoration: none; font-size: 14px;">Выйти</a>
            </div>
        </div>
        
        <div id="ordersList"></div>
    </div>

    <script>
        function loadOrders() {
            fetch('get_orders.php')
                .then(response => response.json())
                .then(orders => {
                    const ordersList = document.getElementById('ordersList');
                    ordersList.innerHTML = `
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Телефон</th>
                                    <th>Товар</th>
                                    <th>Цена</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${orders.map(order => `
                                    <tr>
                                        <td>${order.id}</td>
                                        <td>${order.customer_name}</td>
                                        <td>${order.customer_phone}</td>
                                        <td>${order.product_name}</td>
                                        <td>${order.product_price} ₽</td>
                                        <td>${order.created_at}</td>
                                        <td>
                                            <span class="status status-${order.status}">${order.status}</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="dropdown-button">Изменить статус</button>
                                                <div class="dropdown-content">
                                                    <a href="#" onclick="updateStatus(${order.id}, 'new')">Новый</a>
                                                    <a href="#" onclick="updateStatus(${order.id}, 'paid')">Оплачен</a>
                                                    <a href="#" onclick="updateStatus(${order.id}, 'delivered')">Доставлен</a>
                                                    <a href="#" onclick="updateStatus(${order.id}, 'completed')">Завершен</a>
                                                    <a href="#" onclick="updateStatus(${order.id}, 'cancelled')">Отменен</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                });
        }

        function updateStatus(orderId, newStatus) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(() => loadOrders());
        }

        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>
</html> 