<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
   
   if(isset($_POST['cart_id']) && isset($_POST['qty'])){
      $cart_id = $_POST['cart_id'];
      $qty = $_POST['qty'];
      
      $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
      $update_qty->execute([$qty, $cart_id, $user_id]);
      
      echo 'success';
   }
}
?> 