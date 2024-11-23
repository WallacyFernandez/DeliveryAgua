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
        $cnpj = $validator->validateCNPJ($_POST['cnpj']);
        $phone = $validator->validatePhone($_POST['phone']);
        $company_name = $validator->validateName($_POST['company_name']);
        $company_address = $validator->validateAddress($_POST['company_address']);
        $company_phone = $validator->validatePhone($_POST['company_phone']);
        
        if($validator->hasErrors()) {
            $message = array_merge($message ?? [], $validator->getErrors());
        } else {
            // Verificar email duplicado
            $select_empresa = $conn->prepare("SELECT * FROM `empresas` WHERE email = ?");
            $select_empresa->execute([$email]);
            
            if($select_empresa->rowCount() > 0){
                $message[] = 'Email já cadastrado!';
            } else {
                $passwordPolicy = new PasswordPolicy();
                $passwordErrors = $passwordPolicy->validate($pass);

                if (!empty($passwordErrors)) {
                    $message = array_merge($message ?? [], $passwordErrors);
                } else if($pass != $cpass) {
                    $message[] = 'Senhas não correspondem!';
                } else {
                    // Hash seguro da senha
                    $hashed_password = $passwordManager->secureHash($pass);
                    
                    try {
                        $conn->beginTransaction();
                        
                        // Debug
                        error_log("Tentando registrar empresa: " . $email);
                        
                        // Inserir empresa
                        $insert_empresa = $conn->prepare("INSERT INTO `empresas`(name, email, cnpj, phone, password, company_name, company_address, company_phone) VALUES(?,?,?,?,?,?,?,?)");
                        $insert_empresa->execute([
                            $name, 
                            $email, 
                            $cnpj, 
                            $phone, 
                            $hashed_password,
                            $company_name,
                            $company_address,
                            $company_phone
                        ]);
                        
                        // Debug
                        error_log("Empresa registrada com ID: " . $conn->lastInsertId());
                        
                        $empresa_id = $conn->lastInsertId();
                        
                        // Registrar senha no histórico - VERSÃO CORRIGIDA
                        $insert_history = $conn->prepare("INSERT INTO password_history (user_id, empresa_id, password, user_type) VALUES (NULL, ?, ?, 'empresa')");
                        $insert_history->execute([$empresa_id, $hashed_password]);
                        
                        $conn->commit();
                        $message[] = 'Cadastro realizado com sucesso!';
                        header('location:empresa_login.php');
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
   <title>Registro de Empresa</title>
   <link rel="stylesheet" href="../css/admin_style.css">
   <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;">
</head>
<body>
   <section class="form-container">
      <form action="" method="post">
         <h3>Registro de Empresa</h3>
         <?php
         if(isset($message)){
             foreach($message as $msg){
                 echo '<div class="message">'.$msg.'</div>';
             }
         }
         ?>
         <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
         <input type="text" name="name" required placeholder="nome do responsável" class="box" autocomplete="name">
         <input type="email" name="email" required placeholder="email" class="box" autocomplete="email">
         <input type="text" name="cnpj" required placeholder="CNPJ" class="box" autocomplete="off">
         <input type="text" name="phone" required placeholder="telefone" class="box" autocomplete="tel">
         <input type="text" name="company_name" required placeholder="nome da empresa" class="box" autocomplete="organization">
         <input type="text" name="company_address" required placeholder="endereço da empresa" class="box" autocomplete="street-address">
         <input type="text" name="company_phone" required placeholder="telefone da empresa" class="box" autocomplete="tel">
         <input type="password" name="pass" required placeholder="senha" class="box" autocomplete="new-password">
         <input type="password" name="cpass" required placeholder="confirme a senha" class="box" autocomplete="new-password">
         <input type="submit" value="registrar agora" class="btn" name="submit">
         <p>Já tem uma conta? <a href="empresa_login.php">Faça login aqui</a></p>
      </form>
   </section>

   <script>
   document.querySelector('form').addEventListener('submit', function(e) {
       console.log('Form submitted');
   });
   </script>
</body>
</html>