<?php
require_once '../config.php';
session_start();

// Если пользователь уже авторизован, перенаправляем в админ-панель
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        // Проверяем пользователя в базе данных
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                // Авторизация успешна
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Неверный пароль';
            }
        } else {
            $error = 'Пользователь не найден';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Вход в админ-панель</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="modal-form">
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="Имя пользователя" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Пароль" required>
            </div>
            
            <button type="submit" class="submit-button">Войти</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="../index.html" class="admin-link">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html> 