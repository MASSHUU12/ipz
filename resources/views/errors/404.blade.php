{{-- resources/views/errors/404.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>404 - Not Found</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <style>
    body {
      margin: 0;
      background-color: #1e1e1e;
      color: #ffffff;
      font-family: Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .container {
      text-align: center;
      max-width: 600px;
      padding: 2rem;
      background-color: #222;
      border-radius: 1rem;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
    }
    .title {
      font-size: 6rem;
      color: #00c8ff;
      font-weight: bold;
    }
    .subtitle {
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    .description {
      color: #bbb;
      margin-bottom: 2rem;
    }
    .button {
      padding: 0.75rem 2rem;
      background-color: #00c8ff;
      color: #000;
      border: none;
      border-radius: 0.5rem;
      font-weight: bold;
      font-size: 1rem;
      text-decoration: none;
    }
    .button:hover {
      background-color: #00b0e6;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="title">404</div>
    <div class="subtitle">The page has not been found</div>
    <div class="description">The address is incorrect or has been changed</div>
    <a href="{{ url('/') }}" class="button">Return to the dashboard</a>
  </div>
</body>
</html>
