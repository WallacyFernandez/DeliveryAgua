<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['submit'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $cpass = sha1($_POST['cpass']);
   $company_name = $_POST['company_name'];
   $company_name = filter_var($company_name, FILTER_SANITIZE_STRING);
   $company_address = $_POST['company_address'];
   $company_address = filter_var($company_address, FILTER_SANITIZE_STRING);
   $company_phone = $_POST['company_phone'];
   $company_phone = filter_var($company_phone, FILTER_SANITIZE_STRING);

   $select_empresa = $conn->prepare("SELECT * FROM `empresas` WHERE email = ?");
   $select_empresa->execute([$email]);

   if($select_empresa->rowCount() > 0){
      $message[] = 'Email já cadastrado!';
   }else{
      if($pass != $cpass){
         $message[] = 'Senhas não conferem!';
      }else{
         $insert_empresa = $conn->prepare("INSERT INTO `empresas`(name, email, password, company_name, company_address, company_phone) VALUES(?,?,?,?,?,?)");
         $insert_empresa->execute([$name, $email, $pass, $company_name, $company_address, $company_phone]);
         $message[] = 'Empresa registrada com sucesso!';
         header('location:empresa_accounts.php');
      }
   }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Registrar Empresa</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/admin_header.php'; ?>

   <section class="form-container">
      <form action="" method="post">
         <h3>Registrar Nova Empresa</h3>
         <input type="text" name="name" required placeholder="nome do responsável" maxlength="20" class="box">
         <input type="email" name="email" required placeholder="email" maxlength="50" class="box">
         <input type="text" name="company_name" required placeholder="nome da empresa" maxlength="100" class="box">
         <input type="text" name="company_address" required placeholder="endereço da empresa" maxlength="100" class="box">
         <input type="text" name="company_phone" required placeholder="telefone da empresa" maxlength="20" class="box">
         <input type="password" name="pass" required placeholder="senha" maxlength="20" class="box">
         <input type="password" name="cpass" required placeholder="confirmar senha" maxlength="20" class="box">
         <input type="submit" value="registrar agora" class="btn" name="submit">
      </form>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 