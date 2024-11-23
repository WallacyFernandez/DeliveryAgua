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
    $name = sanitizeInput($_POST['name']);
    $password = $_POST['pass'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    if ($bruteForce->isBlocked($ip, $name)) {
        $message[] = 'Conta temporariamente bloqueada por excesso de tentativas.';
        $logger->logSecurityEvent('login_blocked', null, "IP: $ip, Admin: $name", 'blocked');
        exit();
    }
    
    if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message[] = 'Erro de validação do token de segurança!';
        $logger->logSecurityEvent('csrf_failure', null, 'Token CSRF inválido', 'error');
        return false;
    }

    try {
        $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
        $select_admin->execute([$name]);
        
        if($select_admin->rowCount() > 0) {
            $row = $select_admin->fetch(PDO::FETCH_ASSOC);
            
            if($passwordManager->secureVerify($password, $row['password'])) {
                if($passwordManager->needsRehash($row['password'])) {
                    $newHash = $passwordManager->secureHash($password);
                    $update_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
                    $update_pass->execute([$newHash, $row['id']]);
                }
                
                $_SESSION['admin_id'] = $row['id'];
                $bruteForce->recordAttempt($ip, $name, true);
                $logger->logSecurityEvent('login_success', $row['id']);
                header('location:dashboard.php');
                exit();
            }
        }
        
        $bruteForce->recordAttempt($ip, $name, false);
        $logger->logSecurityEvent('login_failed', null, "Tentativa para admin: $name", 'error');
        $message[] = 'Nome de usuário ou senha incorretos!';
        
    } catch(Exception $e) {
        $logger->logSecurityEvent('login_error', null, $e->getMessage(), 'error');
        error_log($e->getMessage());
        $message[] = 'Erro ao processar login. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <section class="form-container">
        <form action="" method="post">
            <h3>Login Administrativo</h3>
            <?php
            if(isset($message)){
                foreach($message as $msg){
                    echo '<div class="message">'.$msg.'</div>';
                }
            }
            ?>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
            <input type="text" name="name" required placeholder="digite seu nome de usuário" class="box">
            <input type="password" name="pass" required placeholder="digite sua senha" class="box">
            <input type="submit" value="entrar" class="btn" name="submit">
        </form>
    </section>
</body>
</html>
