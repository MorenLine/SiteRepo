<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $errors = [];
    
    $product_name = isset($data['product_name']) ? trim($data['product_name']) : '';
    $product_price = isset($data['product_price']) ? $data['product_price'] : '';
    $customer_name = isset($data['customer_name']) ? trim($data['customer_name']) : '';
    $customer_phone = isset($data['customer_phone']) ? trim($data['customer_phone']) : '';
    $consent = isset($data['consent']) ? $data['consent'] : false;
    
    if (empty($customer_name)) {
        $errors['customer_name'] = 'Имя обязательно для заполнения';
    } elseif (strlen($customer_name) < 2) {
        $errors['customer_name'] = 'Имя должно содержать минимум 2 символа';
    } elseif (strlen($customer_name) > 50) {
        $errors['customer_name'] = 'Имя не должно превышать 50 символов';
    } elseif (!preg_match('/^[а-яёa-z\s]+$/ui', $customer_name)) {
        $errors['customer_name'] = 'Имя может содержать только буквы и пробелы';
    }
    
    if (empty($customer_phone)) {
        $errors['customer_phone'] = 'Телефон обязателен для заполнения';
    } else {
        $phone_clean = preg_replace('/[^0-9]/', '', $customer_phone);
        
        if (strlen($phone_clean) < 10) {
            $errors['customer_phone'] = 'Телефон должен содержать минимум 10 цифр';
        } elseif (strlen($phone_clean) > 15) {
            $errors['customer_phone'] = 'Телефон не должен превышать 15 цифр';
        }
    }
    
    if (!$consent) {
        $errors['consent'] = 'Необходимо дать согласие на обработку персональных данных';
    }
    
    if (empty($product_name)) {
        $errors['product_name'] = 'Название товара обязательно';
    }
    
    if (empty($product_price) || !is_numeric($product_price) || $product_price <= 0) {
        $errors['product_price'] = 'Некорректная цена товара';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Ошибки валидации',
            'errors' => $errors
        ]);
        exit;
    }
    
    $product_name = $conn->real_escape_string($product_name);
    $product_price = floatval($product_price);
    $customer_name = $conn->real_escape_string($customer_name);
    $customer_phone = $conn->real_escape_string($customer_phone);
    
    $sql = "INSERT INTO orders (product_name, product_price, customer_name, customer_phone, created_at) 
            VALUES ('$product_name', $product_price, '$customer_name', '$customer_phone', NOW())";
    
    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Заказ успешно создан'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Ошибка при создании заказа: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Неверный метод запроса'
    ]);
}

$conn->close();
?> 