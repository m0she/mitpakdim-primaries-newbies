<html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>כניסה למערכת</title>
    </head>
    <body dir="rtl">
        <h1>כניסה למערכת ניהול רישום מועמדים</h1>
        <form action="admin_login1.php" method="post">
            <table border="0">
                <tr>
                    <td>שם משתמש:</td>
                    <td><input type="text" name="user"/></td>
                </tr>
                <tr>
                    <td>סיסמה:</td>
                    <td><input type="password" name="pass"/></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="כניסה"/>
                        <br/><br/>
                        <span style="font-weight:bold;color:#FF0000">
                            <?php
                                if ($_GET['err'] == "bad_login") {
                                    echo "שם המשתמש או הסיסמה שגויים";
                                }
                            ?>
                        </span>
                    </td>
                </tr>
            </table>
        </form>
    </body>