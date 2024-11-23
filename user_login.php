<?php
include 'includes/config.php';
include 'components/connect.php';
include 'includes/auth.php';
include 'includes/password.php';
include 'includes/security_log.php';
include 'includes/brute_force.php';

session_start();

$logger = new SecurityLogger($conn);
$passwordManager = new PasswordManager();
$bruteForce = new BruteForceProtection($conn);

error_log("PASSWORD_PEPPER está definido: " . (defined('PASSWORD_PEPPER') ? 'Sim' : 'Não'));

if(isset($_POST['submit'])) {
    $message = [];
    
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['pass'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Criar pasta de logs se não existir
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }
    
    // Log inicial
    file_put_contents('logs/user_login_debug.log', 
        date('Y-m-d H:i:s') . " - Tentativa de login\n" .
        "Email: " . $email . "\n" .
        "------------------------\n", 
        FILE_APPEND
    );
    
    if ($bruteForce->isBlocked($ip, $email)) {
        $message[] = 'Conta temporariamente bloqueada por excesso de tentativas.';
        $logger->logSecurityEvent('login_blocked', null, "IP: $ip, Email: $email", 'blocked');
        exit();
    }
    
    $loginResult = $bruteForce->consistentTimeResponse(function() use ($conn, $email, $password, $passwordManager, $bruteForce, $ip, $logger) {
        if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $message[] = 'Erro de validação do token de segurança!';
            $logger->logSecurityEvent('csrf_failure', null, 'Token CSRF inválido', 'error');
            return false;
        }
        
        try {
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
            $select_user->execute([$email]);
            
            if($select_user->rowCount() > 0) {
                $row = $select_user->fetch(PDO::FETCH_ASSOC);
                
                // Log dos dados de verificação
                file_put_contents('logs/user_login_debug.log', 
                    date('Y-m-d H:i:s') . " - Dados de verificação\n" .
                    "Hash armazenado: " . $row['password'] . "\n" .
                    "------------------------\n",
                    FILE_APPEND
                );
                
                if($passwordManager->secureVerify($password, $row['password'])) {
                    file_put_contents('logs/user_login_debug.log', 
                        date('Y-m-d H:i:s') . " - Verificação de senha: SUCESSO\n",
                        FILE_APPEND
                    );
                    
                    if($passwordManager->needsRehash($row['password'])) {
                        $newHash = $passwordManager->secureHash($password);
                        $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
                        $update_pass->execute([$newHash, $row['id']]);
                    }
                    
                    $_SESSION['user_id'] = $row['id'];
                    $bruteForce->recordAttempt($ip, $email, true);
                    $logger->logSecurityEvent('login_success', $row['id']);
                    return true;
                } else {
                    file_put_contents('logs/user_login_debug.log', 
                        date('Y-m-d H:i:s') . " - Verificação de senha: FALHA\n",
                        FILE_APPEND
                    );
                }
            }
            
            $bruteForce->recordAttempt($ip, $email, false);
            $logger->logSecurityEvent('login_failed', null, "Tentativa para email: $email", 'error');
            return false;
            
        } catch(Exception $e) {
            file_put_contents('logs/user_login_debug.log', 
                date('Y-m-d H:i:s') . " - ERRO: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
            $logger->logSecurityEvent('login_error', null, $e->getMessage(), 'error');
            error_log($e->getMessage());
            return false;
        }
    });
    
    if ($loginResult) {
        header('location:index.php');
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
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   
   <!-- link do cdn do font awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- link do arquivo css personalizado -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Faça Login Agora</h3>
      <?php
      if(isset($message) && is_array($message)){
          foreach($message as $msg){
              echo '<div class="message">'.$msg.'</div>';
          }
      }
      ?>
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
      <input type="email" name="email" required placeholder="digite seu email" class="box" autocomplete="email">
      <input type="password" name="pass" required placeholder="digite sua senha" class="box" autocomplete="current-password">
      <input type="submit" value="entrar agora" class="btn" name="submit">
      <p>Não tem uma conta? <a href="user_register.php">Registre-se aqui</a></p>
      <p><a href="recuperar_senha.php">Esqueceu sua senha?</a></p>
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
