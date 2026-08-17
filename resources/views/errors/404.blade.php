<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404</title>
    <style>
        @font-face {
            font-family: 'JannaLTBold';
            src: url('{{ asset('font/Alexandria-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

     /* 404 Error Page */
#oopss {
  background: linear-gradient(-45deg, #edc306, #F7C800);
  position: fixed;
  left: 0px;
  top: 0;
  width: 100%;
  height: 100%;
  line-height: 1.5em;
  z-index: 9999;
}
#oopss #error-text {
  font-size: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  font-family: "JannaLTBold", sans-serif;
  color: #ffffff;
  direction: rtl;
}
#oopss #error-text img {
  margin: 85px auto 20px;
  height: 342px;
}
#oopss #error-text span {
  position: relative;
  font-size: 3.3em;
  font-weight: 900;
  margin-bottom: 50px;
}
#oopss #error-text p.p-a {
  font-size: 19px;
  margin: 30px 0 15px 0;
}
#oopss #error-text p.p-b {
  font-size: 15px;
}
#oopss #error-text .back {
  background: #fff;
  color: #000;
  font-size: 30px;
  text-decoration: none;
  margin: 2em auto 0;
  padding: 0.7em 2em;
  border-radius: 500px;
  box-shadow: 0 20px 70px 4px rgba(0, 0, 0, 0.1), inset 7px 33px 0 0px ##d9b828;
  font-weight: 900;
  transition: all 300ms ease;
}
#oopss #error-text .back:hover {
  transform: translateY(-13px);
  box-shadow: 0 35px 90px 4px rgba(0, 0, 0, 0.3), inset 0px 0 0 3px #191a19;
}



    </style>
</head>

<body translate="no">

    <div id='oopss'>
        <div id='error-text'>
            <img src="https://cdn.rawgit.com/ahmedhosna95/upload/1731955f/sad404.svg" alt="404">
            <span>404</span>
            <p class="p-a">هذه الصفحة غير موجودة! إما أن تم حذفها أو تغيير العنوان الخاص بها.</p>
            @if(auth()->check())

            <a class="back" href="{{ route('filament.admin.pages.dashboard') }}">الرجوع الى لوحة التحكم</a>
            @else
            <a class="back" href="{{ route('home') }}">الرجوع الصفحة الرئيسية</a>
            @endif

        </div>
    </div>
</body>

</html>
