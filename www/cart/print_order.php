<?php
  // 若購物車是空的，就顯示尚未選購產品
  if (empty($_COOKIE["book_no_list"]))
  {
    echo "<script type='text/javascript'>";
    echo "alert('您尚未選購任何產品');";
    echo "history.back();";		
    echo "</script>";
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
  </head>
  <body background="bg1.jpg">
    <h3>注意事項</h3>
    <ol type="1">
      <li>
        訂購方法一：信用卡。
      </li>
      <li>
        訂購方法二：轉帳。帳號：12345678戶名：聯大資管書店，轉帳完成後請來電洽詢 02-34567890。
      </li>
      <li>
        寄書與補書：您將於付款之後的3-5天收到書籍，若沒有收到，
        請來電洽詢 02-34567890。
      </li>
    </ol>
    <hr>
    <table border="1" bgcolor="white" rules="cols" align="center" cellpadding="5">
    <tr height="25">
				<td colspan="4" align="Center" bgcolor="#CCCC00">個人資料</td>
    </tr>
    <tr height="25">
      <td colspan="4">姓名：<u><?php echo $_COOKIE["name"] ?>
        <?php for ($i = 0; $i <= 100 - 2* strlen($_COOKIE["name"]); $i++) echo "&nbsp;"; ?></u>
      </td>
    </tr>
    <tr height="25">
      <td colspan="4" align="center" bgcolor="#CCCC00">訂單細目</td>
    </tr>
    <tr height="25" align="center" bgcolor="FFFF99">
      <td>書名</td>
      <td>定價</td>
      <td>數量</td>
      <td>小計</td>																
    </tr>			
      <?php
        // 取得購物車資料
        $book_name_array = explode(",", $_COOKIE["book_name_list"]);
        $price_array = explode(",", $_COOKIE["price_list"]);		
        $quantity_array = explode(",", $_COOKIE["quantity_list"]);		
					
        // 顯示購物車內容
        $total = 0;		
        for ($i = 0; $i < count($book_name_array); $i++)
        {
          // 計算小計
          $sub_total = $price_array[$i] * $quantity_array[$i];
					
          // 計算總計
          $total += $sub_total;
					
          // 顯示各欄位資料
          echo "<tr>";	
          echo "<td align='center'>" . $book_name_array[$i] . "</td>";			
          echo "<td align='center'>$" . $price_array[$i] . "</td>";
          echo "<td align='center'>" . $quantity_array[$i] . "</td>";
          echo "<td align='center'>$" . $sub_total . "</td>";
          echo "</tr>";
        }
        echo "<tr align='right' bgcolor='#CCCC00'>";
        echo "<td colspan='4'>總金額 = " . $total . "</td>";	
        echo "</tr>";	
      ?>
    </table>
  </body>
</html>