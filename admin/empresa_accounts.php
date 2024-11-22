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
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
   $company_name = $_POST['company_name'];
   $company_name = filter_var($company_name, FILTER_SANITIZE_STRING);
   $company_address = $_POST['company_address'];
   $company_address = filter_var($company_address, FILTER_SANITIZE_STRING);
   $company_phone = $_POST['company_phone'];
   $company_phone = filter_var($company_phone, FILTER_SANITIZE_STRING);

   $select_empresa = $conn->prepare("SELECT * FROM `empresas` WHERE email = ?");
   $select_empresa->execute([$email]);
   
   if($select_empresa->rowCount() > 0){
      $message[] = 'email já cadastrado!';
   }else{
      if($pass != $cpass){
         $message[] = 'senhas não conferem!';
      }else{
         $insert_empresa = $conn->prepare("INSERT INTO `empresas`(name, email, password, company_name, company_address, company_phone) VALUES(?,?,?,?,?,?)");
         $insert_empresa->execute([$name, $email, $pass, $company_name, $company_address, $company_phone]);
         $message[] = 'nova empresa registrada com sucesso!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Empresas</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/admin_header.php'; ?>

   <section class="accounts">
      <h1 class="heading">Contas de Empresas</h1>
      <div class="box-container">
         <div class="box">
            <p>Registrar Nova Empresa</p>
            <a href="register_empresa.php" class="option-btn">Registrar</a>
         </div>
         <?php
            $select_accounts = $conn->prepare("SELECT * FROM `empresas`");
            $select_accounts->execute();
            if($select_accounts->rowCount() > 0){
               while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
         ?>
         <div class="box">
            <p> ID Empresa : <span><?= $fetch_accounts['id']; ?></span> </p>
            <p> Nome : <span><?= $fetch_accounts['name']; ?></span> </p>
            <p> Empresa : <span><?= $fetch_accounts['company_name']; ?></span> </p>
            <p> Email : <span><?= $fetch_accounts['email']; ?></span> </p>
            <div class="flex-btn">
               <a href="empresa_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('Deletar esta conta?')" class="delete-btn">deletar</a>
            </div>
         </div>
         <?php
            }
         }else{
            echo '<p class="empty">nenhuma empresa cadastrada!</p>';
         }
         ?>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 