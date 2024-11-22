<?php
include '../components/connect.php';
session_start();

$empresa_id = $_SESSION['empresa_id'];

if(!isset($empresa_id)){
   header('location:empresa_login.php');
}

$select_profile = $conn->prepare("SELECT * FROM `empresas` WHERE id = ?");
$select_profile->execute([$empresa_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

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

   $old_pass = $_POST['old_pass'];
   $old_pass = sha1(filter_var($old_pass, FILTER_SANITIZE_STRING));
   $new_pass = $_POST['new_pass'];
   $new_pass = sha1(filter_var($new_pass, FILTER_SANITIZE_STRING));
   $confirm_pass = $_POST['confirm_pass'];
   $confirm_pass = sha1(filter_var($confirm_pass, FILTER_SANITIZE_STRING));

   // Verifica se os campos obrigatórios estão preenchidos
   if(empty($name) || empty($email) || empty($company_name) || empty($company_address) || empty($company_phone)){
      $message[] = 'Por favor, preencha todos os campos!';
   } else {
      // Se estiver alterando a senha
      if(!empty($old_pass) || !empty($new_pass) || !empty($confirm_pass)){
         if($old_pass != $fetch_profile['password']){
            $message[] = 'Senha antiga incorreta!';
         } elseif($new_pass != $confirm_pass){
            $message[] = 'Confirmação de senha não corresponde!';
         } else {
            // Atualiza perfil com nova senha
            $update_profile = $conn->prepare("UPDATE `empresas` SET name = ?, email = ?, company_name = ?, company_address = ?, company_phone = ?, password = ? WHERE id = ?");
            $update_profile->execute([$name, $email, $company_name, $company_address, $company_phone, $confirm_pass, $empresa_id]);
            $message[] = 'Perfil atualizado com sucesso!';
         }
      } else {
         // Atualiza perfil sem alterar senha
         $update_profile = $conn->prepare("UPDATE `empresas` SET name = ?, email = ?, company_name = ?, company_address = ?, company_phone = ? WHERE id = ?");
         $update_profile->execute([$name, $email, $company_name, $company_address, $company_phone, $empresa_id]);
         $message[] = 'Perfil atualizado com sucesso!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Atualizar Perfil</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/empresa_header.php'; ?>

   <section class="form-container">
      <form action="" method="post">
         <h3>Atualizar Perfil</h3>
         <input type="text" name="name" maxlength="20" class="box" placeholder="nome do responsável" value="<?= $fetch_profile['name']; ?>" required>
         <input type="email" name="email" maxlength="50" class="box" placeholder="email" value="<?= $fetch_profile['email']; ?>" required>
         <input type="text" name="company_name" maxlength="100" class="box" placeholder="nome da empresa" value="<?= $fetch_profile['company_name']; ?>" required>
         <input type="text" name="company_address" maxlength="100" class="box" placeholder="endereço da empresa" value="<?= $fetch_profile['company_address']; ?>" required>
         <input type="text" name="company_phone" maxlength="20" class="box" placeholder="telefone da empresa" value="<?= $fetch_profile['company_phone']; ?>" required>
         <input type="password" name="old_pass" maxlength="20" class="box" placeholder="digite sua senha atual">
         <input type="password" name="new_pass" maxlength="20" class="box" placeholder="digite sua nova senha">
         <input type="password" name="confirm_pass" maxlength="20" class="box" placeholder="confirme sua nova senha">
         <input type="submit" value="atualizar" name="submit" class="btn">
      </form>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 