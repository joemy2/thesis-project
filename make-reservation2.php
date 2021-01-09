<?php

session_start();
include "includes/connect.php";

if (!isset($_SESSION['id_user'])) {
  header("location:login.php");
} else {
  $id_user = $_SESSION['id_user'];
}

$k = mysqli_query($conn,"SELECT * FROM tbl_admin where position = 'administrator' ");
$kk = mysqli_fetch_array($k);

$p = mysqli_query($conn,"SELECT * FROM downpayment");
$pp = mysqli_fetch_array($p);

$today = date("Y-m-d");
$valid_booking_date = date("Y-m-d", strtotime($today."+ 4 days"));

$rid = $_GET['id'];

    if (isset($_POST['submit2'])) {

      $package = $_POST['package'];

      $totalVisit = $_POST['totalVisit'];
      $modePayment = $_POST['modePayment'];

      $d = mysqli_query($conn,"SELECT add_ons.food_qty, add_ons.food_id, food_menu.food_name, food_menu.price from add_ons inner join food_menu where add_ons.r_id='$rid' and add_ons.food_id=food_menu.food_id ");
      $o=0;
      while ($dd = mysqli_fetch_array($d)) {
            $o += $dd['food_qty'] * $dd['price'];  
          }

      $f = mysqli_query($conn,"SELECT COUNT(cater_id) AS d from catering_details where cater_id = '$package' ");
      $ff = mysqli_fetch_array($f);
      $t = $ff['d'];

      $g = mysqli_query($conn,"SELECT * FROM catering where cater_id = '$package' ");
      $gg = mysqli_fetch_array($g);

      $payable = $gg['price'] * $totalVisit;

      if (isset($_POST['food_id'])) {

        $foodCount = count($_POST['food_id']);

        if ($foodCount != $t) {
          echo '<script>alert("The total menu selected is equal to '.$t.'.");history.back()</script>';
        } else {
          foreach ($_POST['food_id'] as $value) {
            mysqli_query($conn,"INSERT INTO custom_r (r_id, food_id) values ('$rid', '$value') ");
          }
          if ($totalVisit < $gg['PMin'] || $totalVisit > $gg['PMax']) {
          echo '<script>alert("The required visitor is invalid!");history.back()</script>';
            } else {
              mysqli_query($conn,"UPDATE reservation set cater_id='$package', mode_of_payment='$modePayment', total_visitor='$totalVisit',
                    payable='$payable', balance='$payable',add_ons_ment = '$o', adOn_mis='unpaid', r_status='pending' where rid='$rid' ") or die(mysqli_error($conn));
    
              header("Location: reservation_view.php?id=".$rid);
            }
        }
      } else {
        if ($totalVisit < $gg['PMin'] || $totalVisit > $gg['PMax']) {
          echo '<script>alert("The required visitor is invalid!");history.back()</script>';
        } else {
          mysqli_query($conn,"UPDATE reservation set cater_id='$package', mode_of_payment='$modePayment', total_visitor='$totalVisit',
                payable='$payable', balance='$payable',add_ons_ment = '$o', adOn_mis='unpaid', r_status='pending' where rid='$rid' ") or die(mysqli_error($conn));

          header("Location: reservation_view.php?id=".$rid);
        }
      }


    }
      
?>

<!DOCTYPE html>
<html>
<head>
  <title>Tugkaran Home Page</title>
  <?php include 'includes/links.php'; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="stylesheet/style0.css">
  <link rel="stylesheet" type="text/css" href="includes/icon/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="admin/css/w3.css">
  <style type="text/css">
    label{
      width:100%;
      text-align: left;
    }
  </style>
</head>
<body class="w3-light-grey">
<?php include 'includes/header.php'; 
    $height = false;
?>

<div class="container w3-light-grey w3-margin-bottom">
  
  <div>
    <div class="row w3-center" id="rowsss"  style="margin:10px;">
        <div class="right-fixed green" style="background:#fcfcfc;margin-bottom:80px">
      <div style="background:#fcfcfc; margin-top:5px;margin-bottom:15px;padding: 30px 50px 30px 50px">

          <h3 class="w3-text-blue">Reservation</h3>
            
          <form action="" method="post" style="margin:0 auto">
            <div class="row">
            
            <div class="col-md-5 w3-border-right">
              <div class="w3-padding">
                
                Select Package
                <?php 

                $r = mysqli_query($conn,"SELECT * FROM catering");
                while ($rr = mysqli_fetch_array($r)) { 
                $f = mysqli_query($conn,"SELECT COUNT(cater_id) AS d from catering_details where cater_id = '".$rr['cater_id']."' ");
                $ff = mysqli_fetch_array($f);
                  ?>
                  
                  <label class="w3-light-grey w3-padding w3-center w3-round w3-btn" style="margin-top:10px">
                    <input type="radio" name="package" value="<?php echo $rr['cater_id'] ?>" required class="cater_id" id="<?php echo $rr['cater_id'] ?>" required> <br>
                    <b class="w3-text-green"><?php echo $rr['event_name']; ?></b> <br>
                    P <?php echo number_format($rr['price'],2) ?> per head <br>
                    <b class="w3-text-pink"> There are <?php echo $ff['d'] ?> menus </b>
                    <p style="margin-top:5px" class="w3-text-blue">Good for <?php echo $rr['PMin'] ?> - <?php echo $rr['PMax'] ?> persons.</p>
                    <input type="hidden" value="<?php echo $ff['d'] ?>" id="s<?php echo $rr['cater_id'] ?>">
                </label>
                <?php
                } 
                ?>
                <br><br>

                <label>
                  <span style="font-size:14px;color:#111">
                    Total Visitor is within the range of selected package
                  </span>   
                  <input type="number" name="totalVisit" placeholder="Total visitor" class="w3-input form-control w3-border w3-border-grey w3-round-small" required>
                </label> <br><br>

                <label>
                  <span class="w3-text-black">
                    Mode of Payment
                  </span>
                  <select class="w3-input w3-border w3-border-grey w3-round-small" name="modePayment">
                    <option>Cash</option>
                    <option>Pera Padala</option>
                  </select>
                </label> <br><br>

                <button class="w3-btn w3-round-small w3-green w3-border" name="submit2">Submit</button>
                <br>
              </div>
            </div>

            <div class="col-md-7">
              <div class="w3-padding">
                <div class="w3-center">
                  <h2 class="caterddddd">Menu</h2>
                  <a href="add_ons.php?q=<?php echo $rid ?>" class="w3-text-blue">Add-ons dishes</a> <br>
                </div>

                <div class="w3-center w3-border w3-padding">
                  <?php  
                  $d = mysqli_query($conn,"SELECT add_ons.food_qty, add_ons.food_id, food_menu.food_name, food_menu.price from add_ons inner join food_menu where add_ons.r_id='$rid' and add_ons.food_id=food_menu.food_id ");

                  $o=0;
                  if (mysqli_num_rows($d) > 0) {
                    while ($dd = mysqli_fetch_array($d)) {
                      $o += $dd['food_qty'] * $dd['price'];
                     ?>
                    <?php echo $dd['food_name']. ' ('.$dd['food_qty'].')(P '.number_format($dd['price'],2).') <span class="w3-text-blue">|</span>' ?>
                  <?php   
                    }
                    echo "<b><br>Total: P ". number_format($o,2)."</b>";
                  } else {
                    echo "No add-ons menu added.";
                  }
                  ?>
                </div> <br>

                <div>
                  <div class="w3-center"><button type='button' class='w3-btn w3-round-small w3-light-grey w3-border w3-center custom'>Custom Menu</button> <br>
                    <span class="w3-text-red messs"></span></div><br>
                    
                    <div class="loadMenu w3-margin-left"></div>
                </div>

              </div>
            </div>
            
          </div>
          <div class="alert alert-danger" style="margin:10px auto;text-align:left; width:100%">
            <input type="checkbox" required class="w3-check">

            Note: If you choose pera padala as your mode of payment use this NAME - <span style="text-decoration:underline;"><?php echo $kk['Name'] ?></span> and CONTACT NUMBER - <span style="text-decoration:underline;"><?php echo $kk['contact'] ?></span>. <br><br> You must pay <?php echo $pp['d_price'] * 100 ?>% of the actual price as an advance payment 4 days before the booking date, if you failed to pay the said advance payment, reservation will be CANCEL.
          </div>
          </form> 
              </div>
            </div>
          </div>
    </div>
      
  </div>  
</div>
<?php include 'includes/footer.php' ?>
</body>
</html>
<script src="jquery.js"></script>
<script src="jquery-ui.js"></script>
<script>
  $(document).ready(function(){
    $("#date1,#date2").datepicker();

    $(".cater_id").click(function(){
      var cater_id = $(this).attr("id");
      var s = $("#s"+cater_id).val();
      $(".messs").text("If customize select only "+s+" menus.");

      var cater_n = $(".cater_n").attr("id");
      $(".caterddddd").text(cater_n);
      
      $.ajax({
        url:"admin/include/load_menu.php",
        method:"POST",
        data: {
          cater_id: cater_id
        },
        success: function(d) {
          $(".loadMenu").html(d);
        }
      });
    });

    $(".custom").click(function(){

      $(".loadMenu").load("admin/include/loadAll.php"); 

    });

    });

</script>
<script>
  $("#date1,#date2").datepicker();
  var sad = "<?php echo $height ?>";
  if (sad == true) {
    $("#rowsss").css("height", "600px");
  }
</script>
