<!DOCTYPE html>
<html>
<head>
	<title>Complete with setting password</title>
</head>
<body>
<p> Hi {{$user_name}} ! </p><br/>
<p> As you register with {{$provider_name}} (google or facebook) below is your default password in-case you want to login with password.
However you can change this password in your profile or by reset password. It will not impact on your {{$provider_name}} login. </p><br/>
<p>Your Password: {{$password}}</p><br/>
<p> Thanks. </p><br/>
<p> {{ config('app.name') }} </p><br/>
</body>
</html>