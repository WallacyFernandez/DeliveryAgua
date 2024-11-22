<?php
include '../includes/config.php';
include '../components/connect.php';
include '../includes/auth.php';
include '../includes/validation.php';
include '../includes/password_policy.php';
include '../includes/password.php';
include '../includes/security_log.php';

session_start();

$passwordManager = new PasswordManager();
$logger = new SecurityLogger($conn);

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit();
}

if(isset($_POST['submit'])){
    if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message[] = 'Erro de validação do token de segurança!';
    } else {
        $validator = new InputValidator();
        
        $name = $validator->validateName($_POST['name']);
        $pass = $validator->validatePassword($_POST['pass']);
        $cpass = $_POST['cpass'];
        
        if($validator->hasErrors()) {
            $message = array_merge($message ?? [], $validator->getErrors());
        } else {
            $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
            $select_admin->execute([$name]);
            
            if($select_admin->rowCount() > 0){
                $message[] = 'Nome de usuário já existe!';
            } else {
                $passwordPolicy = new PasswordPolicy();
                $passwordErrors = $passwordPolicy->validate($pass);

                if (!empty($passwordErrors)) {
                    $message = array_merge($message ?? [], $passwordErrors);
                } else if($pass != $cpass) {
                    $message[] = 'Senhas não correspondem!';
                } else {
                    $hashed_password = $passwordManager->secureHash($pass);
                    
                    try {
                        $conn->beginTransaction();
                        
                        $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?,?)");
                        $insert_admin->execute([$name, $hashed_password]);
                        
                        $admin_id = $conn->lastInsertId();
                        
                        $insert_history = $conn->prepare("INSERT INTO admin_password_history (admin_id, password) VALUES (?, ?)");
                        $insert_history->execute([$admin_id, $hashed_password]);
                        
                        $conn->commit();
                        $message[] = 'Novo administrador registrado com sucesso!';
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $message[] = 'Erro ao registrar: ' . $e->getMessage();
                        error_log("Erro no registro de admin: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Registro de Administrador</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/admin_header.php'; ?>
   <section class="form-container">
      <form action="" method="post">
         <h3>Registrar Novo Administrador</h3>
         <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
         <input type="text" name="name" required placeholder="nome de usuário" class="box" autocomplete="username">
         <input type="password" name="pass" required placeholder="senha" class="box" autocomplete="new-password">
         <input type="password" name="cpass" required placeholder="confirmar senha" class="box" autocomplete="new-password">
         <input type="submit" value="registrar agora" class="btn" name="submit">
      </form>
   </section>
</body>
</html>
