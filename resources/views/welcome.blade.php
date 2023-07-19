<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/css/main.css"/>

  @vite(['resources/css/file-browser.scss', 'resources/js/file-browser.js'])
  <title>Laravel</title>

</head>
<body class="is-preload">
<!-- Wrapper -->
<div id="wrapper">

  <!-- Header -->
  <header id="header">
    <div class="inner">

      <!-- Logo -->
      <a href="index.html" class="logo">
        <span class="symbol"><img src="/images/logo.svg" alt=""/></span><span class="title">Phantom</span>
      </a>

      <!-- Nav -->
      <nav>
        <ul>
          <li><a href="#menu">Menu</a></li>
        </ul>
      </nav>

    </div>
  </header>

  <!-- Menu -->
  <nav id="menu">
    <h2>Menu</h2>
    <ul>
      <li><a href="index.html">Home</a></li>
      <li><a href="generic.html">Ipsum veroeros</a></li>
      <li><a href="generic.html">Tempus etiam</a></li>
      <li><a href="generic.html">Consequat dolor</a></li>
      <li><a href="elements.html">Elements</a></li>
    </ul>
  </nav>

  <!-- Main -->
  <div id="main">
    <div class="inner">
      <header>
        <h1>This is Phantom, a free, fully responsive site<br/>
          template designed by <a href="http://html5up.net">HTML5 UP</a>.</h1>
        <p>Etiam quis viverra lorem, in semper lorem. Sed nisl arcu euismod sit amet nisi euismod sed cursus arcu
          elementum ipsum arcu vivamus quis venenatis orci lorem ipsum et magna feugiat veroeros aliquam. Lorem ipsum
          dolor sit amet nullam dolore.</p>
      </header>
      <section class="tiles">
        <article class="style1">
          <span class="image">
            <img src="/images/pic01.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Magna</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style2">
          <span class="image">
            <img src="/images/pic02.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Lorem</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style3">
          <span class="image">
            <img src="/images/pic03.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Feugiat</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style4">
          <span class="image">
            <img src="/images/pic04.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Tempus</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style5">
          <span class="image">
            <img src="/images/pic05.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Aliquam</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style6">
          <span class="image">
            <img src="/images/pic06.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Veroeros</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style2">
          <span class="image">
            <img src="/images/pic07.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Ipsum</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style3">
          <span class="image">
            <img src="/images/pic08.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Dolor</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style1">
          <span class="image">
            <img src="/images/pic09.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Nullam</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style5">
          <span class="image">
            <img src="/images/pic10.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Ultricies</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style6">
          <span class="image">
            <img src="/images/pic11.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Dictum</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
        <article class="style4">
          <span class="image">
            <img src="/images/pic12.jpg" alt=""/>
          </span>
          <a href="generic.html">
            <h2>Pretium</h2>
            <div class="content">
              <p>Sed nisl arcu euismod sit amet nisi lorem etiam dolor veroeros et feugiat.</p>
            </div>
          </a>
        </article>
      </section>
    </div>

    <div class="inner" style="margin-top: 80px">
      <section>
        <div class="init"></div>
      </section>
    </div>
  </div>

  <!-- Footer -->
  @include('footer')
</div>

<!-- Scripts -->
<script src="/js/jquery.min.js"></script>
<script src="/js/browser.min.js"></script>
<script src="/js/breakpoints.min.js"></script>
<script src="/js/util.js"></script>
<script src="/js/main.js"></script>

@vite('resources/js/app.js')
</body>
</html>
