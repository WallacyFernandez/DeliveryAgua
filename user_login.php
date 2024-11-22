<?php

include 'components/connect.php';
include 'includes/auth.php';

session_start();

if(isset($_SESSION['user_id'])){
   header('location:home.php');
   exit();
}

if(isset($_POST['submit'])){
    if(!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message[] = 'Token de segurança inválido!';
    } else {
        $email = sanitizeInput($_POST['email']);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message[] = 'Email inválido!';
        } else {
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
            $select_user->execute([$email]);
            $row = $select_user->fetch(PDO::FETCH_ASSOC);

            if($select_user->rowCount() > 0){
                if(verifyPassword($_POST['pass'], $row['password'])){
                    loginUser($row);
                    header('location:home.php');
                    exit();
                } else {
                    $message[] = 'Senha incorreta!';
                }
            } else {
                $message[] = 'Email não encontrado!';
            }
        }
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
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
      <input type="email" name="email" required placeholder="digite seu email" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="digite sua senha" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="entrar agora" class="btn" name="submit">
      <p>Não tem uma conta?</p>
      <a href="user_register.php" class="option-btn">Registre-se Agora.</a>
   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
