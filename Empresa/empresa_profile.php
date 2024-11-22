<?php
include '../components/connect.php';
include '../includes/auth.php';

session_start();
checkSessionTimeout(); // Função que criamos anteriormente

if(!isset($_SESSION['empresa_id'])){
   header('location:empresa_login.php');
   exit();
}

$empresa_id = $_SESSION['empresa_id'];

if(isset($_POST['submit'])){
    if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message[] = 'Erro de validação do token de segurança!';
    } else {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        $passwordManager = new PasswordManager();
        $passwordPolicy = new PasswordPolicy();
        
        try {
            // Buscar senha atual do banco
            $select_prev_pass = $conn->prepare("SELECT password FROM `empresas` WHERE id = ?");
            $select_prev_pass->execute([$empresa_id]);
            $row = $select_prev_pass->fetch(PDO::FETCH_ASSOC);

            if(empty($old_pass)){
                $message[] = 'Por favor digite a senha antiga!';
            } elseif(!$passwordManager->secureVerify($old_pass, $row['password'])){
                $message[] = 'Senha antiga não corresponde!';
                $logger->logSecurityEvent('password_change_failed', $empresa_id, 'Senha antiga incorreta');
            } elseif($new_pass !== $confirm_pass){
                $message[] = 'Senhas não correspondem!';
            } else {
                // Validar nova senha
                $passwordErrors = $passwordPolicy->validate($new_pass);
                if (!empty($passwordErrors)) {
                    $message = array_merge($message ?? [], $passwordErrors);
                } elseif (!$passwordPolicy->checkPasswordHistory($new_pass, $empresa_id, $conn)) {
                    $message[] = 'Esta senha já foi utilizada recentemente';
                } else {
                    $conn->beginTransaction();
                    
                    // Hash da nova senha
                    $hashed_password = $passwordManager->secureHash($new_pass);
                    
                    // Atualizar senha
                    $update_pass = $conn->prepare("UPDATE `empresas` SET password = ? WHERE id = ?");
                    $update_pass->execute([$hashed_password, $empresa_id]);
                    
                    // Registrar no histórico
                    $insert_history = $conn->prepare("INSERT INTO password_history (user_id, password) VALUES (?, ?)");
                    $insert_history->execute([$empresa_id, $hashed_password]);
                    
                    $conn->commit();
                    
                    // Regenerar sessão e registrar evento
                    regenerateSession();
                    $logger->logSecurityEvent('password_changed', $empresa_id, 'Senha alterada com sucesso');
                    
                    $message[] = 'Senha atualizada com sucesso!';
                }
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $logger->logSecurityEvent('password_change_error', $empresa_id, $e->getMessage());
            $message[] = 'Erro ao atualizar senha. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Perfil da Empresa</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/empresa_header.php'; ?>

   <section class="form-container">
      <form action="" method="post">
         <h3>Atualizar Perfil</h3>
         <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">
         <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" required placeholder="nome do responsável" maxlength="20" class="box">
         <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" required placeholder="email" maxlength="50" class="box">
         <input type="text" name="company_name" value="<?= $fetch_profile['company_name']; ?>" required placeholder="nome da empresa" maxlength="100" class="box">
         <input type="text" name="company_address" value="<?= $fetch_profile['company_address']; ?>" required placeholder="endereço da empresa" maxlength="100" class="box">
         <input type="text" name="company_phone" value="<?= $fetch_profile['company_phone']; ?>" required placeholder="telefone da empresa" maxlength="20" class="box">
         <input type="password" name="old_pass" placeholder="digite sua senha antiga" maxlength="20" class="box">
         <input type="password" name="new_pass" placeholder="digite sua nova senha" maxlength="20" class="box">
         <input type="password" name="confirm_pass" placeholder="confirme sua nova senha" maxlength="20" class="box">
         <input type="submit" value="atualizar agora" class="btn" name="submit">
      </form>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 