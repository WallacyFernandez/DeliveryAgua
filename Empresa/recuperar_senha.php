<?php
include '../components/connect.php';
include '../includes/auth.php';
include '../includes/password.php';
include '../includes/security_log.php';
include '../includes/password_recovery.php';

session_start();

$logger = new SecurityLogger($conn);
$passwordManager = new PasswordManager();
$recovery = new PasswordRecovery($conn, $logger, $passwordManager);

if(isset($_POST['submit'])) {
    $email = sanitizeInput($_POST['email']);
    
    try {
        $select_empresa = $conn->prepare("SELECT * FROM `empresas` WHERE email = ? LIMIT 1");
        $select_empresa->execute([$email]);
        
        // Sempre enviar email para não revelar existência da conta
        if($select_empresa->rowCount() > 0) {
            $empresa = $select_empresa->fetch(PDO::FETCH_ASSOC);
            $token = $recovery->generateResetToken($empresa['id'], $email);
            
            // Enviar email com link de recuperação
            $resetLink = "https://seusite.com/Empresa/redefinir_senha.php?token=" . $token;
            // Implementar envio de email aqui
            
            $logger->logSecurityEvent('recovery_email_sent', $empresa['id']);
        } else {
            // Simular delay para evitar timing attacks
            sleep(1);
            $logger->logSecurityEvent('recovery_email_not_found', null, "Email: $email");
        }
        
        $message[] = 'Se existe uma conta com este email, você receberá instruções para redefinir sua senha.';
        
    } catch (Exception $e) {
        $message[] = 'Erro ao processar solicitação. Tente novamente.';
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Recuperar Senha</title>
   <!-- Seus estilos aqui -->
</head>
<body>
   <form action="" method="post">
      <h3>Recuperar Senha</h3>
      <?php
      if(isset($message)){
         foreach($message as $message){
            echo '<div class="message">'.$message.'</div>';
         }
      }
      ?>
      <input type="email" name="email" required placeholder="Digite seu email" class="box">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <input type="submit" name="submit" value="Enviar" class="btn">
      <p>Lembrou sua senha? <a href="empresa_login.php">Faça login</a></p>
   </form>
</body>
</html> 