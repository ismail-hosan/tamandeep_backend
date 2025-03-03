<!DOCTYPE html>
<html>
<head>
    <title>New Contact Message</title>
</head>
<body>
    <h2>You have received a new contact message</h2>
    <p><strong>Name:</strong>{{ $first_name }} {{ $last_name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Message:</strong></p>
    @foreach ($message as $ms)
    <p>{{ $ms }}</p>
    @endforeach
  
</body>
</html>