<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $company_name = $_POST['company_name'];
   $company_address = $_POST['company_address'];
   $company_phone = $_POST['company_phone'];
   $pass = sha1($_POST['pass']);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
   $select_admin->execute([$name]);

   if($select_admin->rowCount() > 0){
      $message[] = 'username already exist!';
   }else{
      $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password, company_name, company_address, company_phone) VALUES(?,?,?,?,?)");
      $insert_admin->execute([$name, $pass, $company_name, $company_address, $company_phone]);
      $message[] = 'new admin registered successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register Admin</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Registrar Nova Empresa</h3>
      <input type="text" name="name" required placeholder="nome de usuário" class="box">
      <input type="text" name="company_name" required placeholder="nome da empresa" class="box">
      <input type="text" name="company_address" required placeholder="endereço da empresa" class="box">
      <input type="text" name="company_phone" required placeholder="telefone da empresa" class="box">
      <input type="password" name="pass" required placeholder="senha" class="box">
      <input type="password" name="cpass" required placeholder="confirmar senha" class="box">
      <input type="submit" value="registrar agora" class="btn" name="submit">
   </form>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>
