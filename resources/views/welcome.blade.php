<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/css/main.css"/>

  @vite('resources/css/file-browser.scss')
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
        <div class="init">
          <div class="file-browser-wrap">
            <div class="file-browser-actions-wrap">
              <ul>
                <li>
                  <button name="refresh" type="button" title="Refresh">
                    <i class="file-browser-icon refresh-icon"></i>
                  </button>
                </li>
                <li class="separator-tile"></li>
                <li>
                  <button name="treeView" type="button" title="Switch to the tree view">
                    <i class="file-browser-icon tree-view-icon"></i>
                  </button>
                </li>
                <li>
                  <button name="listView" type="button" title="Switch to the list view">
                    <i class="file-browser-icon list-view-icon"></i>
                  </button>
                </li>
                <li>
                  <button name="sideBySideView" type="button" title="Switch to the two panel view">
                    <i class="file-browser-icon side-by-side-view-icon"></i>
                  </button>
                </li>
                <li class="separator-tile"></li>
                <li>
                  <button name="selectAll" type="button" title="Select all files">
                    <i class="file-browser-icon select-all-icon"></i>
                  </button>
                </li>
                <li>
                  <button name="unselectAll" type="button" title="Unselect all files">
                    <i class="file-browser-icon unselect-all-icon"></i>
                  </button>
                </li>
              </ul>
            </div>

            <div data-type="side-by-side">
              <div class="file-browser-panels-wrap">
                <div class="file-browser-panel-wrap">
                  <div class="file-browser-bookmark-wrap">
                    <ul>
                      <li class="active">
                        <span>files</span>
                      </li>
                    </ul>
                  </div>
                  <table class="file-browser-panel-content">
                    <thead>
                    <tr>
                      <th>
                        <span>Name</span>
                      </th>
                      <th>
                        <span>Ext</span>
                      </th>
                      <th>
                        <span>Size</span>
                      </th>
                      <th>
                        <span>Date</span>
                      </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                      <td>
                        <span>[..]</span>
                      </td>
                      <td></td>
                      <td><span>&lt;DIR&gt;</span></td>
                      <td><span>29.aug.2022</span></td>
                    </tr>
                    <tr>
                      <td><span>test</span></td>
                      <td><span>file</span></td>
                      <td><span>1.7 M</span></td>
                      <td><span>29.aug.2022</span></td>
                    </tr>
                    </tbody>

                    <tfoot>
                    <tr>
                      <th colspan="4">
                        files: <span data-counter="files">1</span>&nbsp;&nbsp;&nbsp;folders: <span data-counter="folders">0</span>
                      </th>
                    </tr>
                    </tfoot>
                  </table>
                </div>

                <div class="file-browser-panel-wrap">
                  <div class="file-browser-bookmark-wrap">
                    <ul>
                      <li class="active">
                        <span>files</span>
                      </li>
                    </ul>
                  </div>
                  <table class="file-browser-panel-content">
                    <thead>
                    <tr>
                      <th>
                        <span>Name</span>
                      </th>
                      <th>
                        <span>Ext</span>
                      </th>
                      <th>
                        <span>Size</span>
                      </th>
                      <th>
                        <span>Date</span>
                      </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                      <td>
                        <span>[..]</span>
                      </td>
                      <td></td>
                      <td><span>&lt;DIR&gt;</span></td>
                      <td><span>29.aug.2022</span></td>
                    </tr>
                    <tr>
                      <td><span>test</span></td>
                      <td><span>file</span></td>
                      <td><span>1.7 M</span></td>
                      <td><span>29.aug.2022</span></td>
                    </tr>
                    </tbody>

                    <tfoot>
                    <tr>
                      <th colspan="4">
                        files: <span data-counter="files">1</span>&nbsp;&nbsp;&nbsp;folders: <span data-counter="folders">0</span>
                      </th>
                    </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <div class="file-browser-controls-wrap">
                <button name="rename" type="button">
                  <span>Rename</span>
                </button>
                <button name="view" type="button">
                  <span>View</span>
                </button>
                <button name="upload" type="button">
                  <span>Upload</span>
                </button>
                <button name="copy" type="button">
                  <span>Copy</span>
                </button>
                <button name="move" type="button">
                  <span>Move</span>
                </button>
                <button name="createDir" type="button">
                  <span>Directory</span>
                </button>
                <button name="delete" type="button">
                  <span>Delete</span>
                </button>
              </div>
            </div>

            <div data-type="list-view">
              <table class="file-browser-list-wrap">
                <thead>
                <tr>
                  <th colspan="4">
                    <span>files/</span>
                  </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>
                    <i class="file-browser-icon folder-icon"></i>
                  </td>
                  <td><span>[..]</span></td>
                  <td>
                    <span>0 items</span>
                  </td>
                  <td>
                    <span>22.aug.2022</span>
                  </td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-word"></i>
                  </td>
                  <td><span>file-word.doc</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-video"></i>
                  </td>
                  <td><span>file-video.avi</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td></tr>
                <tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-pdf"></i>
                  </td>
                  <td><span>file-pdf.pdf</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-audio"></i>
                  </td>
                  <td><span>file-audio.mp3</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-image"></i>
                  </td>
                  <td><span>file-image.png</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-cell"></i>
                  </td>
                  <td><span>file-cell.xls</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-code"></i>
                  </td>
                  <td><span>file.php</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-archive"></i>
                  </td>
                  <td><span>file-archive.zip</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-alt"></i>
                  </td>
                  <td><span>file.txt</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                <tr>
                  <td>
                    <i class="file-browser-icon file-regular"></i>
                  </td>
                  <td><span>file.some</span></td>
                  <td><span>1.8 KB</span></td>
                  <td><span>22.aug.2022</span></td>
                </tr>
                </tr>
                </tbody>
              </table>
            </div>

            <div data-type="tree-view">

            </div>
          </div>
        </div>
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

<script src="/js/file-browser.js"></script>
<script>
  new FileBrowser()
</script>

</body>
</html>
