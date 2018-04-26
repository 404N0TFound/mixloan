<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<script type="text/javascript">
    function onSubmit() {
        var a = document.getElementById("notify");
        //alert(a.getAttribute("href"));
        a.setAttribute("href", a.getAttribute("href") + "?" + document.getElementById("ExtData").value);
        //alert(a.getAttribute("href"));
        return true;
    } 
</script>

<head>
    <title>接口对接demo</title>
</head>
<body>
    <form id="form1" action="Order.php" method="post"  target="_blank">
    <div>
		<table>
            <tr><td colspan='2'>----------------------------------------------------------------</td></tr>
			<tr>
				<th colspan='2' align='center'>测试下订单接口(SDK版本)</th> 
			</tr>
			<tr>
				<td>商户订单金额:</td>
				<td><input type="text" name="Amount" size="15" /></td>
			</tr>
			<tr>
				<td>商户订单描述:</td>
				<td><input type="text" name="OrderDesc" size="40" /></td>
			</tr>
			<tr>
				<td><input type="submit" value="模拟手机APP下单" /></td>
			</tr>
		</table>
    </div>
    </form>

    <form id="form1" action="OrderH5.php" method="post"  target="_blank">
    <div>
		<table>
            <tr><td colspan='2'>----------------------------------------------------------------</td></tr>
			<tr>
				<th colspan='2' align='center'>测试下订单接口(H5版本)</th> 
			</tr>
			<tr>
				<td>商户订单金额:</td>
				<td><input type="text" name="Amount" size="15" /></td>
			</tr>
			<tr>
				<td>商户订单描述:</td>
				<td><input type="text" name="OrderDesc" size="40" /></td>
			</tr>
			<tr>
				<td><input type="submit" value="模拟H5网页下单" /></td>
			</tr>
		</table>
    </div>
    </form>
    
	<table>
        <tr><td colspan='2'>----------------------------------------------------------------</td></tr>
		<tr>
			<th colspan='2' align='center'>测试订单结果通知</th> 
		</tr>
        <tr>
        <td>成功通知参数串:</td>
        <td><textarea rows="8" cols="80" id="ExtData">Version=2.0.0&MerchantId=302020000058&MerchOrderId=1407893794150&Amount=1.00&ExtData=5rWL6K+V&OrderId=302014081300038222&Status=02&PayTime=20140814111645&SettleDate=20140909&Sign=iDQ6gBAebnh1kzSb4XN0PP3bTIXTkwG9iE8PDnNZBEiTWpBknH4XoBAotC5G/RF4E+HUa7f9esJWEI1mKw84EMDt+gBY2KABe7fejIdzqS8AH5niJEJkWAKwm4qYQTkT4Ate9lshcOZDfcyZ7eqblXXHUYOFBsYtslANOsb+/IA=</textarea></td>
        </tr>
	</table>
    <a id='notify' href="Notify.php" onclick="return onSubmit();"  target="_blank">模拟订单结果通知</a>

    <form id="form2" action="test.php" method="post" target="_blank">
    <div>
    <table>
        <tr><td colspan='2'>----------------------------------------------------------------</td></tr>
			<tr>
				<td><input type="submit" value="DemoTest调用测试" /></td>
			</tr>
        <tr><td colspan='2'>----------------------------------------------------------------</td></tr>
	</table>
    </div>
    </form>

</body>
</html>
