<style type="text/css">
<!--
.style1 {
font-family: Arial, Helvetica, sans-serif;
font-weight: bold;
}
.style2 {
font-size: 12px;
font-family: Arial, Helvetica, sans-serif;
color: #FF0000;
}
.style4 {
font-size: 12px;
font-family: Arial, Helvetica, sans-serif;
color: #339900;
}
-->
</style>
<br><br />
<table width="500" border="0" align="center" cellpadding="10" cellspacing="1" bgcolor="#999999">
<tr>
<td bgcolor="#F3F3F3"><form name="form1" method="post" action="index.php">
<input type="hidden" name="toDoAction" value="login"><table width="480" border="0" cellspacing="0" cellpadding="0">
<tr>
<td colspan="4"><span class="style1">Enter login details below<br>
<br>
</span>
<?php if($invalidLogin === TRUE) { ?>
<span class="style2">We're sorry, the login details you entered were incorrect. Please check your login details and try again.</span><br />
<br />
<?php }
if($loggedOut === TRUE) { ?>
<span class="style4">You have successfully been logged out.</span><br />
<br />
<?php
}?></td>
</tr><tr>
<td>User</td>
<td><input type="text" name="username" id="textfield"></td>
<td>Pass</td>
<td><input type="password" name="password" id="textfield"></td>
</tr>
<tr>
<td colspan="4"><div align="center">
<p><br>
<input type="submit" name="Submit" value="Login">
</p>
</div></td>
</tr>
</table></form></td>
</tr>
</table>