

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>email</title>
    <style>
        body
        {
            background-color: rgb(221, 235, 235);
        }
        .box
        {
            width: 80%;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
            text-align: right;
            box-shadow: 0 1px 5px 1px rgba(0, 0, 0, 0.23);
            
        }

        p 
        {
            padding: 10px 0;
        }
        p b 
        {
            color: green;
            padding: 0 10px;
        }
    </style>
</head>
<body dir="rtl">
   <div class="box">
       <p>الأسم : <b>{{ $name }}</b></p>
    <p>البريد الألكتروني <b> {{ $email }}</b></p>
    <p>رقم الهاتف : <b>{{ $phone }}</b></p>
    <h2>عنوان الرسالة : <b>{{ $subjects }}</b></h2>
    <p>
      {{  $messages}}
    </p>
   </div>
</body>
</html>