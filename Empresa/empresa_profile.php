<?php
include '../components/connect.php';
session_start();

$empresa_id = $_SESSION['empresa_id'];

if(!isset($empresa_id)){
   header('location:empresa_login.php');
}

if(isset($_POST['submit'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $company_name = $_POST['company_name'];
   $company_name = filter_var($company_name, FILTER_SANITIZE_STRING);
   $company_address = $_POST['company_address'];
   $company_address = filter_var($company_address, FILTER_SANITIZE_STRING);
   $company_phone = $_POST['company_phone'];
   $company_phone = filter_var($company_phone, FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE `empresas` SET name = ?, email = ?, company_name = ?, company_address = ?, company_phone = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $company_name, $company_address, $company_phone, $empresa_id]);

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $prev_pass = $_POST['prev_pass'];
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $confirm_pass = sha1($_POST['confirm_pass']);
   $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);

   if($old_pass == $empty_pass){
      $message[] = 'por favor digite a senha antiga!';
   }elseif($old_pass != $prev_pass){
      $message[] = 'senha antiga não corresponde!';
   }elseif($new_pass != $confirm_pass){
      $message[] = 'senhas não correspondem!';
   }else{
      if($new_pass != $empty_pass){
         $update_pass = $conn->prepare("UPDATE `empresas` SET password = ? WHERE id = ?");
         $update_pass->execute([$confirm_pass, $empresa_id]);
         $message[] = 'senha atualizada com sucesso!';
      }else{
         $message[] = 'por favor digite uma nova senha!';
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