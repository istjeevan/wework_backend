<!DOCTYPE html>
<html>
<head>
	<title>Contact Us Mail</title>
</head>
<body>
<p>Hello, </p>
<p>Below is contact us details.</p>
<p>Name : {{$name}}</p>
<p>Email : {{$email}}</p>
<p>Phone Number : {{($phone_number)?$phone_number:""}}</p>
<p>Subject : {{$subject}}</p>
<p>Description : {{$description}}</p>
</body>
</html>