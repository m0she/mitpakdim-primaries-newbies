<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>רישום למועמדים חדשים</title>
	<script type="text/javascript" src="candidates.js"></script>
	<script type="text/javascript">
		function clear_list(list) {
			var c = list.firstChild;
			while (c) {
				var next = c.nextSibling;
				list.removeChild(c);
				c = next;
			}
		}
		
		function load_parties() {
			var parties = list_parties();
			
			if (parties) {
				var lst = document.getElementById("party");
				clear_list(lst);
				
				for (i = 0; i < parties.length; i++) {
					var option = document.createElement('OPTION');
					option.value = parties[i].id;
					option.innerHTML = parties[i].name;
					lst.appendChild(option);
				}
				
				lst = document.getElementById("region");
				clear_list(lst);
				
				lst.disabled = 'disabled';
				
				reload_regions();
			}
		}
	
		function reload_regions() {
			var lst = document.getElementById("region");
			lst.disabled = 'disabled';
			
			var party = document.getElementById("party").value;
			var regions = list_regions(party);
			
			if (regions) {
				var lst = document.getElementById("region");
				lst.disabled = false;
				clear_list(lst);
				
				for (i = 0; i < regions.length; i++) {
					var option = document.createElement('OPTION');
					option.value = regions[i].id;
					option.innerHTML = regions[i].name;
					lst.appendChild(option);
				}
			}
		}
		
		function register() {
			var fname = document.getElementById("fname").value;
			var lname = document.getElementById("lname").value;
			var zehut = document.getElementById("zehut").value;
			var party = document.getElementById("party").value;
			var region = document.getElementById("region").value;
			var email = document.getElementById("email").value;
			var phone = document.getElementById("phone").value;
			var website = document.getElementById("website").value;
			var facebook = document.getElementById("facebook").value;
			try {
				var res = new_candidate(fname, lname, zehut, party, region, email, phone, website, facebook);
				
				if (res) {
					//alert("User registered. ID: " + res);
					window.location = "reg2.php";
				} else {
					alert("אירעה תקלה");
				}
			} catch (e) {
				alert("אירעה תקלה: " + e.message);
			}
		}
	</script>
</head>
<body dir="rtl" style="font-family:Helvetica,Arial;font-size:12pt" onload="load_parties();">
	<img src="http://www.mitpakdim.co.il/site/logo.png"/><br/><br/>
	בטופס זה יש למלא את פרטי המועמד/ת במדוייק. כל הפרטים יבדקו ויוצלבו מול המפלגות, ובמקרה של אי התאמה הרישום יפסל. במקרים של חוסר וודאות או צורך לבירורים נוספים, אנו ניצור עם המועמד/ת קשר.‪<br/>
	חשוב להזין דוא"ל שיש לכם לכם גישה אליו לצורך המשך תהליך ההרשמה, הדוא"ל לא חייב להיות של המועמד.‪<br/>
	<br/>
	לאחר מילוי הפרטים הראשונים תתבקשו להעלות שלושה קבצים:
	<ul>
		<li>סריקה של תעודת הזהות של המועמד‫/‬ת</li>
		<li>מסמך קורות חיים של המועמד‫/‬ת ‫(‬בפורמט PDF‫)</li>
		<li>תמונה של המועמד‫/‬ת בגודל XXXxXXX פיקסלים</li>
	</ul>
	הקבצים חייבים להיות בפורמט המצויין ובגודל המצויין. לא ניתן יהיה לסיים את התהליך ללא הקבצים הללו. לכן, ‪<b>‬אנו ממליצים להתחיל את התהליך רק כאשר יש בידיכם את כל המסמכים והנתונים הדרושים‪</b>.‪<br/>
	<br/>
	לאחר העלאת הקבצים ‪<b>‬יוצגו בפניכם מספר נושאים עליהם תצטרכו לחוות את דעתכם‪</b>‬. חשוב להשקיע זמן ומחשבה במילוי דעותיכם, שכן ‪<b>‬לאחר בחירתן לא ניתן יהיה לשנות את ההעדפות‪</b>‬.<br/>‬
	<br/>
	<div style="width:500px; height:300px; border-color:#000000; border-style:solid; border-width: 2px">
	<b>שלב א': פרטים אישיים של המועמד/ת</b><br/>
	<table border="0" style="font-family:Helvetica,Arial;font-size:12pt">
		<tr>
			<td>שם פרטי:</td>
			<td><input type="text" name="fname" id="fname" size="30"/></td>
		</tr>
		<tr>
			<td>שם משפחה:</td>
			<td><input type="text" name="lname" id="lname" size="30"/></td>
		</tr>
		<tr>
			<td>מספר זהות:</td>
			<td><input type="text" name="zehut" id="zehut" size="9" maxlength="9" /></td>
		</tr>
		<tr>
			<td>מפלגה:</td>
			<td>
				<select name="party" id="party" onchange="reload_regions();">
				</select>
			</td>
		</tr>
		<tr>
			<td>מחוז התמודדות:</td>
			<td>
				<select name="region" id="region">
					<option value="0">ללא מחוז</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>דואר אלקטרוני פעיל לצורך אישור:</td>
			<td><input type="text" name="email" id="email" size="30"/></td>
		</tr>
		<tr>
			<td>טלפון:</td>
			<td><input type="text" name="phone" id="phone" size="30"/></td>
		</tr>
		<tr>
			<td>אתר אינטרנט:</td>
			<td><input type="text" name="website" id="website" size="30"/></td>
		</tr>
		<tr>
			<td>כתובת דף פייסבוק:</td>
			<td><input type="text" name="facebook" id="facebook" size="30"/></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="button" id="submit" value="המשך" onclick="register();" />
			</td>
		</tr>
	</table>
	</div>
</body>
</html>