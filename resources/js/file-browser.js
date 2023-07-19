export class FileBrowser {
  /**
   * Default class options
   * @type {{routes: {}, defaultView: string, panel: []}}
   */
  options = {
    defaultView: 'sideBySide',
    panel: [['refresh'], ['treeView', 'listView', 'sideBySide'], ['selectAll', 'unselectAll']],
    routes: {
      copy: {method: 'post', url: '/file-browser/copy'},
      create: {method: 'post', url: '/file-browser/create'},
      info: {method: 'post', url: '/file-browser/info'},
      list: {method: 'post', url: '/file-browser/list'},
      move: {method: 'post', url: '/file-browser/move'},
      remove: {method: 'post', url: '/file-browser/remove'},
      search: {method: 'post', url: '/file-browser/search'},
      size: {method: 'post', url: '/file-browser/size'},
      upload: {method: 'post', url: '/file-browser/upload'}
    }
  }

  mimeTypes = {
    // archive
    'file-archive': ['application/gzip', 'application/java-archive', 'application/rar', 'application/zip', 'application/x-bzip2'],
    // audio
    'file-audio': ['audio/aac', 'audio/ogg', 'audio/flac', 'audio/midi', 'audio/mpeg', 'audio/x-wav', 'audio/aifc', 'audio/x-aiff'],
    // database
    'file-database': ['text/csv', 'application/csv', 'application/vnd.sun.xml.base', 'application/vnd.oasis.opendocument.base', 'application/vnd.oasis.opendocument.database', 'application/sql'],
    //font
    'file-text': ['font/ttf', 'font/woff', 'font/woff2', 'font/opentype', 'application/vnd.ms-fontobject'],
    //images
    'file-image': ['image/gif', 'image/jp2', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/tiff', 'image/bmp'],
    // pdf
    'file-pdf': ['application/pdf'],
    // scripts
    'file-code': ['application/ecmascript', 'application/hta', 'application/xhtml+xml', 'application/xml', 'application/xslt+xml', 'text/css', 'text/x-csrc', 'text/x-c++src', 'application/x-asp', 'text/x-python'],
    // spreadsheet
    'file-cell': ['application/vnd.ms-excel', 'application/vnd.ms-excel.sheet.macroEnabled.12', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.oasis.opendocument.spreadsheet'],
    // text
    'file-alt': ['text/plain', 'text/html', 'text/markdown', 'application/json', 'application/x-x509-ca-cert'],
    // text processor
    'file-word': ['application/msword', 'application/rtf', 'text/rtf', 'text/richtext', 'application/vnd.ms-word.document.macroEnabled.12', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.text-master', 'application/abiword'],
    // video
    'file-video': ['video/avi', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/ogg', 'video/webm', 'video/x-flv', 'video/x-msvideo', 'video/x-matroska', 'video/x-ms-wmv']
  }

  /**
   * @param wrap - file-browser wrapper
   * @param options - {token - bearer token}
   */
  constructor(wrap, options = {}) {
    // Set panel view
    if (options.hasOwnProperty('panel')) {
      this.options.panel = options.panel
    }
    // Set routes
    if (options.hasOwnProperty('routes')) {
      this.options.routes = Object.assign(this.options.routes, options.routes)
    }

    const getFileList = this.request(Object.assign(this.options.routes.list, {data: {path: '/'}}))
    getFileList.then(response => {
      if (200 === response.status) {
        document.querySelector(wrap).innerHTML = '<div class="file-browser-wrap">' +
          this.buildControlPanel() +
          this.buildBody(response.data) +
          '</div>'
      }
    })
  }

  async request(options) {
    if (!options.hasOwnProperty('url')) {
      throw new ReferenceError('The request requires url endpoint.')
    }

    const input = {
      headers: Object.assign({"Content-Type": "application/json"}, options.headers ?? {}),
      method: 'method' in options ? options.method.toUpperCase() : 'GET',
      data: 'data' in options ? JSON.stringify(options.data) : null,
    }

    return new Promise((resolve, reject) => {
      fetch(options.url, input)
        .then(response => response.json().then(body => resolve({status: response.status, data: body})))
        .catch(response => reject(response))
    })
  }

  buildBody(files) {
    return this.templates.views[this.options.defaultView].wrap(
      'sideBySide' !== this.options.defaultView
        ? files
        : {left: files, right: files}
    )
  }

  /**
   * Render file-browser controls panel
   * @param panelWrap
   * @returns {string}
   */
  buildControlPanel(panelWrap = '') {
    for (let i = 0, n = this.options.panel.length; i < n; i++) {
      panelWrap += this.options.panel[i].reduce((sum, curr) => sum + `<li>${this.templates.buttons[curr](curr === this.options.defaultView)}</li>`, '')

      if (i !== (this.options.panel.length - 1)) {
        panelWrap += '<li class="separator-tile"></li>'
      }
    }

    return '<div class="file-browser-actions-wrap"><ul>' + panelWrap + '</ul></div>';
  }

  /**
   * Format bytes as human-readable text.
   *
   * @param {int} bytes
   * @returns {string}
   */
  fileSize(bytes) {
    if (Math.abs(bytes) < 1024) {
      return bytes;
    }

    const units = ['K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
    let u = -1;

    do {
      bytes /= 1024;
      ++u;
    } while (Math.round(Math.abs(bytes) * 10) / 10 >= 1024 && u < units.length - 1);

    return bytes.toFixed(1) + ' ' + units[u];
  }

  /**
   * Convert Unix timestamp to date format j.M.Y H:i:s
   *
   * @param timestamp
   * @returns {string}
   */
  formatDate(timestamp) {
    const date = new Date(timestamp * 1000);
    return `${date.getDate()}.${date.toLocaleString('default', {month: 'short'})}.${date.getFullYear()}` +
      ` ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`
  }

  fileIcon(file) {
    let icon = 'file-regular';
    if (file.isDir) {
      icon = 'folder-icon'
    } else {
      for (let icon in this.mimeTypes) {

      }
    }

    return icon;
  }

  /**
   *
   * @type {object}
   */
  templates = {
    wrapper: '<div class="file-browser-wrap">',
    buttons: {
      button: (name, title, icon, isActive) => `<button name="${name}"${isActive ? ' class="active"' : ''} type="button" title="${title}"><i class="file-browser-icon ${icon}"></i></button>`,
      listView: a => this.templates.buttons.button('listView', 'Switch to the list view', 'list-view-icon', a),
      refresh: a => this.templates.buttons.button('refresh', 'Refresh', 'refresh-icon', a),
      selectAll: a => this.templates.buttons.button('selectAll', 'Select all files', 'select-all-icon', a),
      sideBySide: a => this.templates.buttons.button('sideBySideView', 'Switch to the two panel view', 'side-by-side-view-icon', a),
      treeView: a => this.templates.buttons.button('treeView', 'Switch to the tree view', 'tree-view-icon', a),
      unselectAll: a => this.templates.buttons.button('unselectAll', 'Unselect all files', 'unselect-all-icon', a)
    },
    views: {
      sideBySide: {
        bookmark: {
          item: (name, isActive = !1) => `<li${isActive ? ' class="active"' : ''}>${name}</li>`,
          wrap: items => {
            items = items.reduce((sum, cur) => sum + this.templates.views.sideBySide.bookmark.item(cur.name, cur.active), '');
            return `<div class="file-browser-bookmark-wrap"><ul>${items}</ul></div>`
          }
        },
        listItem: file => {
          console.log(
            this.fileIcon(file),
            file.filename,
            file.ext,
            file.isDir ? '&lt;DIR&gt;' : this.fileSize(file.size),
            this.formatDate(file.mtime)
          )
        },
        panel: (bookmark, files) => {
          files.reduce((sum, cur) => this.templates.views.sideBySide.listItem(cur))
          return `<div class="file-browser-panel-wrap">
          ${this.templates.views.sideBySide.bookmark.wrap(bookmark)}
          <table class="file-browser-panel-content">
            <thead>
            <tr>
              <th><span>Name</span></th>
              <th><span>Ext</span></th>
              <th><span>Size</span></th>
              <th><span>Date</span></th>
            </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>`
        },
        wrap: (files, bookmarks = {left: [{name: '/', active: !0}], right: [{name: '/', active: !0}]}) => {
          console.log(files.left)
          return `<div data-type="side-by-side">
            <div class="file-browser-panels-wrap">
              ${this.templates.views.sideBySide.panel(bookmarks.left, files.left)}
              ${this.templates.views.sideBySide.panel(bookmarks.right, files.right)}
            </div>
          </div>`;
        }
      }
    },
    sideBySide: {
      view: `<div data-type="side-by-side">
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
                      <td><span></span></td>
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
                      <th colSpan="4">
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
                      <th colSpan="4">
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
            </div>`
    },
    tree: {
      view: `<div class="active" data-type="tree-view">
              <div class="file-browser-panels-wrap">
                <div class="file-browser-tree-list"></div>

                <div class="file-browser-tree-content"></div>
              </div>
            </div>`
    },
    list: {
      view: `<div data-type="list-view">
              <table class="file-browser-list-wrap">
                <thead>
                <tr>
                  <th colSpan="4">
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
                </tbody>
              </table>
            </div>`
    }
  }
}