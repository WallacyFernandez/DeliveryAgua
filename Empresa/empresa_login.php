<?php
include '../components/connect.php';
session_start();

if(isset($_SESSION['empresa_id'])){
   header('location:dashboard.php');
}

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_empresa = $conn->prepare("SELECT * FROM `empresas` WHERE email = ? AND password = ?");
   $select_empresa->execute([$email, $pass]);
   
   if($select_empresa->rowCount() > 0){
      $row = $select_empresa->fetch(PDO::FETCH_ASSOC);
      $_SESSION['empresa_id'] = $row['id'];
      header('location:dashboard.php');
   }else{
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
         <input type="email" name="email" required placeholder="digite seu email" class="box">
         <input type="password" name="pass" required placeholder="digite sua senha" class="box">
         <input type="submit" value="entrar" class="btn" name="submit">
         <p>Não tem uma conta? <a href="register_empresa.php">Registre-se aqui</a></p>
      </form>
   </section>
</body>
</html>