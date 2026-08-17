<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <style>
        /* Inline CSS styles */
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info label {
            font-weight: bold;
        }

        .info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="info">
            <label>الاسم:</label>
            <b>{{$data["name"]}}</b>
        </div>
        <div class="info">
            <label>البريد الالكتروني:</label>
            <b>{{$data["email"]}}</b>
        </div>
        <div class="info">
            <label>الخدمة:</label>
            <b>{{$data["extra_services"]}}</b>
        </div>
        <div class="info">
            <label>الموضوع:</label>
            <b>{{$data["subject"]}}</b>
        </div>
        <div class="info">
            <label>محتوى الرسالة:</label>
            <p>{{$data["description"]}}</p>
        </div>
    </div>
</body>
</html>
