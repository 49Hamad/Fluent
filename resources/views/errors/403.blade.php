<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>غير مسموح</title>
    <style>
        @import url("https://fonts.googleapis.com/css?family=Comfortaa");
* {
  box-sizing: border-box;
}

body,
html {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow: hidden;
}
@font-face {
  font-family: 'JannaLTBold';
  src: url('{{ asset('font/Alexandria-Bold.ttf') }}') format('truetype');
  font-weight: bold;
  font-style: normal;
}
body {
  background-color: #1c7e48;
  font-family: 'JannaLTBold', sans-serif;
}

.container {
  z-index: 1;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  padding: 10px;
  min-width: 300px;
}
.container div {
  display: inline-block;
}
.container .lock {
  opacity: 1;
}
.container h1 {
  font-family: "Comfortaa", cursive;
  font-size: 100px;
  text-align: center;
  color: #eee;
  font-weight: 100;
  margin: 0;
}
.container p {
  color: #fff;
}

.lock {
  transition: 0.5s ease;
  position: relative;
  overflow: hidden;
  opacity: 0;
}
.lock.generated {
  transform: scale(0.5);
  position: absolute;
  -webkit-animation: 2s move linear;
          animation: 2s move linear;
  -webkit-animation-fill-mode: forwards;
          animation-fill-mode: forwards;
}
h2{
    line-height: 1.5rem;
    font-size: 15px;
}
.lock ::after {
  content: "";
  background: #a74006;
  opacity: 0.3;
  display: block;
  position: absolute;
  height: 100%;
  width: 50%;
  top: 0;
  left: 0;
}
.lock .bottom {
  background: #D68910;
  height: 40px;
  width: 60px;
  display: block;
  position: relative;
  margin: 0 auto;
}
.lock .top {
  height: 60px;
  width: 50px;
  border-radius: 50%;
  border: 10px solid #fff;
  display: block;
  position: relative;
  top: 30px;
  margin: 0 auto;
}
.lock .top::after {
  padding: 10px;
  border-radius: 50%;
}

@-webkit-keyframes move {
  to {
    top: 100%;
  }
}

@keyframes move {
  to {
    top: 100%;
  }
}
@media (max-width: 420px) {
  .container {
    transform: translate(-50%, -50%) scale(0.8);
  }

  .lock.generated {
    transform: scale(0.3);
  }
}

.dashboard-link {
    display: inline-block;
    padding: 10px 20px;
    margin: 10px 0;
    font-size: 16px;
    color: white;
    background-color: #53d08b;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    box-shadow: 0px 2px 9px 0px #22512e;
}

    .dashboard-link:hover {
        background-color: #5add95;
    }


    </style>
</head>
<body dir="rtl">
    <div class="container">
        <h1>3<div class="lock"><div class="top"></div><div class="bottom"></div>
          </div>4</h1><h2 style="color: #fff"> نعتذر، ولكن لا تمتلك الصلاحيات الكافية للوصول إلى هذه الصفحة في الوقت الحالي.</h2>
          @if(auth()->check())

          <a class="dashboard-link" href="{{ route('filament.admin.pages.dashboard') }}">الرجوع الى لوحة التحكم</a>
          @else
          <a class="dashboard-link" href="{{ route('home') }}">الرجوع الصفحة الرئيسية</a>
          @endif
      </div>

      <script>
        const interval = 500;

function generateLocks() {
  const lock = document.createElement('div'),
        position = generatePosition();
  lock.innerHTML = '<div class="top"></div><div class="bottom"></div>';
  lock.style.top = position[0];
  lock.style.left = position[1];
  lock.classList = 'lock'// generated';
  document.body.appendChild(lock);
  setTimeout(()=>{
    lock.style.opacity = '1';
    lock.classList.add('generated');
  },100);
  setTimeout(()=>{
    lock.parentElement.removeChild(lock);
  }, 2000);
}
function generatePosition() {
  const x = Math.round((Math.random() * 100) - 10) + '%';
  const y = Math.round(Math.random() * 100) + '%';
  return [x,y];
}
setInterval(generateLocks,interval);
generateLocks();
      </script>
</body>
</html>
