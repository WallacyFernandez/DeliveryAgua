<?php
include '../includes/config.php';
include '../components/connect.php';
include '../includes/auth.php';
include '../includes/password.php';
include '../includes/security_log.php';
include '../includes/brute_force.php';

session_start();

$logger = new SecurityLogger($conn);
$passwordManager = new PasswordManager();
$bruteForce = new BruteForceProtection($conn);

if(isset($_POST['submit'])) {
    $email = sanitizeInput($_POST['email']);
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Verificar se está bloqueado
    if ($bruteForce->isBlocked($ip, $email)) {
        $message[] = 'Conta temporariamente bloqueada por excesso de tentativas. Tente novamente mais tarde.';
        $logger->logSecurityEvent('login_blocked', null, "IP: $ip, Email: $email", 'blocked');
        exit();
    }
    
    // Proteção contra timing attacks
    $loginResult = $bruteForce->consistentTimeResponse(function() use ($conn, $email, $passwordManager, $bruteForce, $ip, $logger) {
        if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $message[] = 'Erro de validação do token de segurança!';
            $logger->logSecurityEvent('csrf_failure', null, 'Token CSRF inválido', 'error');
            return false;
        }
        
        $password = $_POST['pass'];
        
        try {
            $select_empresa = $conn->prepare("SELECT * FROM `empresas` WHERE email = ? LIMIT 1");
            $select_empresa->execute([$email]);
            
            if($select_empresa->rowCount() > 0) {
                $row = $select_empresa->fetch(PDO::FETCH_ASSOC);
                
                // Logs detalhados
                file_put_contents('C:/xampp/htdocs/DeliveryAgua/logs/login.log', 
                    date('Y-m-d H:i:s') . " - Tentativa de login\n" .
                    "Email: " . $email . "\n" .
                    "Hash armazenado: " . $row['password'] . "\n" .
                    "Senha fornecida: " . $password . "\n" .
                    "------------------------\n", 
                    FILE_APPEND
                );
                
                if($passwordManager->secureVerify($password, $row['password'])) {
                    file_put_contents('C:/xampp/htdocs/DeliveryAgua/logs/login.log', 
                        date('Y-m-d H:i:s') . " - Verificação de senha: SUCESSO\n", 
                        FILE_APPEND
                    );
                    if($passwordManager->needsRehash($row['password'])) {
                        $newHash = $passwordManager->secureHash($password);
                        $update_pass = $conn->prepare("UPDATE `empresas` SET password = ? WHERE id = ?");
                        $update_pass->execute([$newHash, $row['id']]);
                        $logger->logSecurityEvent('password_rehash', $row['id'], 'Hash atualizado');
                    }
                    
                    $_SESSION['empresa_id'] = $row['id'];
                    $bruteForce->recordAttempt($ip, $email, true);
                    $logger->logSecurityEvent('login_success', $row['id']);
                    return true;
                } else {
                    file_put_contents('C:/xampp/htdocs/DeliveryAgua/logs/login.log', 
                        date('Y-m-d H:i:s') . " - Verificação de senha: FALHA\n", 
                        FILE_APPEND
                    );
                }
            }
            
            $bruteForce->recordAttempt($ip, $email, false);
            $logger->logSecurityEvent('login_failed', null, "Tentativa para email: $email", 'error');
            return false;
            
        } catch(Exception $e) {
            file_put_contents('C:/xampp/htdocs/DeliveryAgua/logs/login.log', 
                date('Y-m-d H:i:s') . " - ERRO: " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
            $logger->logSecurityEvent('login_error', null, $e->getMessage(), 'error');
            error_log($e->getMessage());
            return false;
        }
    });
    
    if ($loginResult) {
        header('location:dashboard.php');
        exit();
    } else {
        $message[] = 'Email ou senha incorretos!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Login Empresa</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <section class="form-container">
      <form action="" method="post">
         <h3>Login Empresa</h3>
         <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
         <input type="email" name="email" required placeholder="digite seu email" class="box">
         <input type="password" name="pass" required placeholder="digite sua senha" class="box">
         <input type="submit" value="entrar" class="btn" name="submit">
         <p>Não tem uma conta? <a href="register_empresa.php">Registre-se aqui</a></p>
      </form>
   </section>

   <script>
   // Adicionar proteção contra reenvio de formulário
   if (window.history.replaceState) {
       window.history.replaceState(null, null, window.location.href);
   }
   </script>
</body>
</html>