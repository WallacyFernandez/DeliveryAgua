<?php
include 'includes/config.php';
include 'components/connect.php';
include 'includes/auth.php';
include 'includes/validation.php';
include 'includes/password_policy.php';
include 'includes/password.php';
include 'includes/security_log.php';

session_start();

$passwordManager = new PasswordManager();
$logger = new SecurityLogger($conn);

if(isset($_POST['submit'])){
    if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message[] = 'Erro de validação do token de segurança!';
    } else {
        $validator = new InputValidator();
        
        // Validar inputs
        $name = $validator->validateName($_POST['name']);
        $email = $validator->validateEmail($_POST['email']);
        $pass = $validator->validatePassword($_POST['pass']);
        $cpass = $_POST['cpass'];
        
        if($validator->hasErrors()) {
            $message = array_merge($message ?? [], $validator->getErrors());
        } else {
            // Verificar email duplicado
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
            $select_user->execute([$email]);
            
            if($select_user->rowCount() > 0){
                $message[] = 'Email já cadastrado!';
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
                        
                        // Inserir usuário
                        $insert_user = $conn->prepare("INSERT INTO `users`(name, email, password) VALUES(?,?,?)");
                        $insert_user->execute([$name, $email, $hashed_password]);
                        
                        $user_id = $conn->lastInsertId();
                        
                        // Registrar senha no histórico - VERSÃO CORRIGIDA
                        $insert_history = $conn->prepare("INSERT INTO password_history (user_id, empresa_id, password, user_type) VALUES (?, NULL, ?, 'user')");
                        $insert_history->execute([$user_id, $hashed_password]);
                        
                        $conn->commit();
                        $message[] = 'Cadastro realizado com sucesso!';
                        header('location:user_login.php');
                        exit();
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $message[] = 'Erro ao realizar cadastro: ' . $e->getMessage();
                        error_log("Erro no registro: " . $e->getMessage());
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
   <title>Registro de Usuário</title>
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   <?php include 'components/user_header.php'; ?>
   
   <section class="form-container">
      <form action="" method="post">
         <h3>Registre-se Agora</h3>
         <?php
         if(isset($message)){
             foreach($message as $msg){
                 echo '<div class="message">'.$msg.'</div>';
             }
         }
         ?>
         <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
         <input type="text" name="name" required placeholder="digite seu nome" class="box" autocomplete="name">
         <input type="email" name="email" required placeholder="digite seu email" class="box" autocomplete="email">
         <input type="password" name="pass" required placeholder="digite sua senha" class="box" autocomplete="new-password">
         <input type="password" name="cpass" required placeholder="confirme sua senha" class="box" autocomplete="new-password">
         <input type="submit" value="registrar agora" class="btn" name="submit">
         <p>Já tem uma conta? <a href="user_login.php">Faça login aqui</a></p>
      </form>
   </section>
</body>
</html>
