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
  };
  
  protected = {
    files: {
      left: [],
      right: []
    },
    sideBySide: {
      active: 0,
      left: 0,
      right: 0
    }
  }

  mimeTypes = {
    // archive
    'file-archive': ['application/gzip', 'application/java-archive', 'application/rar', 'application/zip', 'application/x-bzip2'],
    // audio
    'file-audio': ['audio/aac', 'audio/ogg', 'audio/flac', 'audio/midi', 'audio/mpeg', 'audio/x-wav', 'audio/aifc', 'audio/x-aiff'],
    // database
    'file-database': [
      'text/csv',
      'application/csv',
      'application/vnd.sun.xml.base',
      'application/vnd.oasis.opendocument.base',
      'application/vnd.oasis.opendocument.database',
      'application/sql'
    ],
    //font
    'file-text': ['font/ttf', 'font/woff', 'font/woff2', 'font/opentype', 'application/vnd.ms-fontobject'],
    //images
    'file-image': ['image/gif', 'image/jp2', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/tiff', 'image/bmp'],
    // pdf
    'file-pdf': ['application/pdf'],
    // scripts
    'file-code': [
      'application/ecmascript',
      'application/hta',
      'application/xhtml+xml',
      'application/xml',
      'application/xslt+xml',
      'text/css',
      'text/x-csrc',
      'text/x-c++src',
      'application/x-asp',
      'text/x-python'
    ],
    // spreadsheet
    'file-cell': [
      'application/vnd.ms-excel',
      'application/vnd.ms-excel.sheet.macroEnabled.12',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.oasis.opendocument.spreadsheet'
    ],
    // text
    'file-alt': ['text/plain', 'text/html', 'text/markdown', 'application/json', 'application/x-x509-ca-cert'],
    // text processor
    'file-word': [
      'application/msword',
      'application/rtf',
      'text/rtf',
      'text/richtext',
      'application/vnd.ms-word.document.macroEnabled.12',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.oasis.opendocument.text',
      'application/vnd.oasis.opendocument.text-master',
      'application/abiword'
    ],
    // video
    'file-video': [
      'video/avi',
      'video/mpeg',
      'video/mp4',
      'video/quicktime',
      'video/ogg',
      'video/webm',
      'video/x-flv',
      'video/x-msvideo',
      'video/x-matroska',
      'video/x-ms-wmv'
    ]
  };

  /**
   * @param wrap - file-browser wrapper
   * @param options - {token - bearer token}
   */
  constructor(wrap, options = {}) {
    // Set panel view
    if (options.hasOwnProperty('panel')) {
      this.options.panel = options.panel;
    }
    // Set routes
    if (options.hasOwnProperty('routes')) {
      this.options.routes = Object.assign(this.options.routes, options.routes);
    }
    // Retrieve a list of files
    const getFileList = this.request(Object.assign(this.options.routes.list, {data: {path: '/'}}));
    getFileList.then(response => {
      if (200 === response.status) {
        document.querySelector(wrap).innerHTML = '<div class="file-browser-wrap">' + this.buildControlPanel() + this.buildBody(response.data) + '</div>';

        this.initSideBySideListeners(wrap);
      }
    });
  }

  initSideBySideListeners(wrap) {
    for (let type in this.sideBySideEvents) {
      for (let event in this.sideBySideEvents[type]) {
        for (let i = 0, n = this.sideBySideEvents[type][event].length; i < n; i++) {
          const handler = this.sideBySideEvents[type][event][i];
          handler.hasOwnProperty('target') && document.querySelector(`${wrap} ${handler.target}`).addEventListener(event, handler.callback)
        }
      }
    }

    document.addEventListener('keyup', e => {
      const key = e.key.toLowerCase();
      // Tab
      for (let i = 0, n = this.sideBySideEvents.panel.keyup.length; i < n; i++) {
        const event = this.sideBySideEvents.panel.keyup[i]
        if (!Array.isArray(event.key)) {
          event.key = [event.key];
        }

        if (event.key.includes(key)) {
          event.callback(wrap, e);
        }
      }
    });
  }

  async request(options) {
    if (!options.hasOwnProperty('url')) {
      throw new ReferenceError('The request requires url endpoint.');
    }

    const input = {
      headers: Object.assign({"Content-Type": "application/json"}, options.headers ?? {}),
      method: 'method' in options ? options.method.toUpperCase() : 'GET',
      body: 'data' in options ? JSON.stringify(options.data) : null
    };

    return new Promise((resolve, reject) => {
      fetch(options.url, input)
        .then(response => response.json().then(body => resolve({status: response.status, data: body})))
        .catch(response => reject(response));
    });
  }

  buildBody(files) {
    this.protected.files.left = files;
    this.protected.files.right = files;
    return this.templates.views[this.options.defaultView].wrap();
  }

  /**
   * Render file-browser controls panel
   * @param panelWrap
   * @returns {string}
   */
  buildControlPanel(panelWrap = '') {
    panelWrap = this.options.panel.map(
      items => items.map(curr => `<li>${this.templates.buttons[curr](curr === this.options.defaultView)}</li>`).join('')
    ).join('<li class="separator-tile"></li>');

    return '<div class="file-browser-actions-wrap"><ul>' + panelWrap + '</ul></div>';
  }

  /**
   * Format bytes as human-readable text.
   * @param {int} bytes
   * @returns {string}
   */
  fileSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const value = Math.floor(Math.log(Math.abs(bytes)) / Math.log(1024));
    return (bytes / Math.pow(1024, value)).toFixed(1) + ' ' + units[value];
  }

  /**
   * Get file icon by file properties
   * @param file
   * @returns {string}
   */
  fileIcon(file) {
    if (file.isDir) {
      return 'folder-icon';
    } else {
      const icon = Object.keys(this.mimeTypes).find(icon => this.mimeTypes[icon].includes(file['mime-type']));
      return icon ? icon : 'file-regular';
    }
  }

  /**
   * Convert Unix timestamp to date format j.M.Y H:i:s
   *
   * @param timestamp
   * @returns {string}
   */
  formatDate(timestamp) {
    const date = new Date(timestamp * 1000);
    return `${date.getDate()}.${date.toLocaleString('default', {month: 'short'})}.${date.getFullYear()}` + ` ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
  }

  sideBySideFunctions = {
    /**
     * Remove "select" class
     * @param panels
     * @returns {*}
     */
    clearSelected: panels => panels.forEach(panel => [...panel.querySelectorAll('tbody tr')].forEach(node => node.classList.remove('select'))),
    /**
     * Set "select" class to row
     * @param wrap
     */
    setSelected: wrap => {
      // Get panel element
      const panel = document.querySelectorAll(`${wrap} .file-browser-panel-wrap`)[this.protected.sideBySide.active];
      // Add "select" class to row
      panel.querySelectorAll('tbody tr')[this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()]].classList.add('select')
    },
    /**
     * Get selected file data
     * @returns {*}
     */
    getFile: () => {
      const side = this.sideBySideFunctions.getSidePosition()
      return this.protected.files[side][this.protected.sideBySide[side]]
    },
    /**
     * Get side position field depends on active side (left is 0; right is 1)
     * @returns {string}
     */
    getSidePosition: () => this.protected.sideBySide.active ? 'right' : 'left',
    /**
     * Toggle "insert" class on a row
     * @param wrap
     * @returns {Element}
     */
    highlightRow: wrap => {
      const panel = document.querySelectorAll(`${wrap} .file-browser-panel-wrap`)[this.protected.sideBySide.active]
      const row = panel.querySelectorAll('tbody tr')[this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()]]
      if (row.classList.contains('insert')) {
        row.classList.remove('insert')
      } else {
        row.classList.add('insert')
      }

      return panel
    },
    /**
     * Move "selected" element down
     * @param panel
     */
    moveDown: panel => {
      let position = this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()]
      // Check if selected row is not the last row
      if (position < panel.querySelectorAll('tbody tr').length - 1) {
        // Increase position
        this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()] = ++position;
      }
      panel.querySelectorAll('tbody tr')[position].classList.add('select')
    }
  }


  sideBySideEvents = {
    bookmarks: {},
    controls: {},
    header: {},
    panel: {
      keyup: [
        {
          key: 'enter',
          callback: (wrap, e) => {
            if (!e.altKey) {
              console.log('go inside');
            }
          }
        },
        {
          key: 'enter',
          callback: (wrap, e) => {
            if (e.altKey) {
              const file = this.sideBySideFunctions.getFile()

              this.request(Object.assign(this.options.routes.info, {data: {path: file.path + file.basename}})).then(response => {
                console.log(response);
              })
            }
          }
        },
        // Insert with "Insert" button
        {
          key: 'insert',
          callback: (wrap, e) => {
            // Clear selected row
            this.sideBySideFunctions.clearSelected(document.querySelectorAll(`${wrap} .file-browser-panel-wrap`))
            // Highlight row and get active panel
            const panel = this.sideBySideFunctions.highlightRow(wrap)
            // Change "selected" and "active" position down
            this.sideBySideFunctions.moveDown(panel)
          }
        },
        // Insert with "Space" button
        {
          key: ' ',
          callback: (wrap, e) => {
            const file = this.sideBySideFunctions.getFile()
            if (file.isDir) {
              this.request(Object.assign(this.options.routes.size, {data: {path: file.path + file.basename}})).then(response => {
                response.data.size = this.fileSize(response.data.size)
                file.data = response.data;
                console.log(this.protected.files.left);
              })
            }
            this.sideBySideFunctions.highlightRow(wrap)
          }
        },
        // Move up
        {
          key: 'arrowup',
          callback: (wrap, e) => {
            // Clear selected row
            this.sideBySideFunctions.clearSelected(document.querySelectorAll(`${wrap} .file-browser-panel-wrap`))
            // Check if selected row is not the first row
            if (this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()] > 0) {
              // Decrease row position
              this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()]--
            }
            this.sideBySideFunctions.setSelected(wrap)
          }
        },
        // Move down
        {
          key: 'arrowdown',
          callback: (wrap, e) => {
            // Clear selected row
            this.sideBySideFunctions.clearSelected(document.querySelectorAll(`${wrap} .file-browser-panel-wrap`))
            // Get current panel
            const panel = document.querySelectorAll(`${wrap} .file-browser-panel-wrap`)[this.protected.sideBySide.active];
            this.sideBySideFunctions.moveDown(panel)
          }
        },
        // Switch panel
        {
          key: 'tab',
          callback: (wrap, e) => {
            // Clear selected row
            this.sideBySideFunctions.clearSelected(document.querySelectorAll(`${wrap} .file-browser-panel-wrap`))
            // Invert active panel
            this.protected.sideBySide.active = +!this.protected.sideBySide.active;
            this.sideBySideFunctions.setSelected(wrap)
          }
        }
      ],
      click: [
        {
          target: '.file-browser-wrap .file-browser-panels-wrap',
          callback: e => {
            if (null !== e.target.closest('tbody')) {
              // Selected row
              const row = e.target.closest('tr')
              // Selected panel
              const panel = e.target.closest('.file-browser-panel-wrap');
              // Get panels list
              const panels = Array.from(panel.closest('.file-browser-panels-wrap').querySelectorAll('.file-browser-panel-wrap'));
              // Set the found index as active panel
              this.protected.sideBySide.active = panels.indexOf(panel);
              // Find out active row
              const rows = Array.from(row.closest('tbody').querySelectorAll('tr'));
              // Set the found index as active row
              this.protected.sideBySide[this.sideBySideFunctions.getSidePosition()] = rows.indexOf(row);
              // Clear selected rows
              this.sideBySideFunctions.clearSelected(panels)
              // Set class to row
              row.classList.add('select')
            }
          }
        }
      ]
    }
  };

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
        // Side-by-side panel bookmarks
        bookmark: {
          item: (name, isActive = !1) => `<li${isActive ? ' class="active"' : ''}>${name}</li>`,
          wrap: items => `<div class="file-browser-bookmark-wrap"><ul>${items.reduce((sum, cur) => sum + this.templates.views.sideBySide.bookmark.item(cur.name, cur.active), '')}</ul></div>`
        },
        // Side-by-side file list item
        listItem: (file, position, isLeft) => {
          const activePanel = this.protected.sideBySide.active ? 'right' : 'left';
          const className = isLeft && !this.protected.sideBySide.active && position === this.protected.sideBySide[activePanel] ? 'class="select"' : ''

          return `<tr ${className} title="${file.basename}\n Modification date/time: ${this.formatDate(file.mtime)}\n Size: ${file.isDir ? 0 : this.fileSize(file.size)}">
          <td><i class="file-browser-icon ${this.fileIcon(file)}"></i></td>
          <td><span>${file.filename}</span></td>
          <td><span>${file.ext}</span></td>
          <td><span>${file.isDir ? '&lt;DIR&gt;' : this.fileSize(file.size)}</span></td>
          <td><span>${this.formatDate(file.mtime)}</span></td>
        </tr>`
        },
        // Side-by-sile file list header
        panel: (bookmark, files, isLeft) => {
          let counters = {folders: 0, files: 0};
          for (let i = 0, n = files.length; i < n; i++) {
            counters[files[i].isDir ? 'folders' : 'files']++;
          }

          return `<div class="file-browser-panel-wrap">
            ${this.templates.views.sideBySide.bookmark.wrap(bookmark)}
            <table class="file-browser-panel-content">
              <thead>
              <tr>
                <th colspan="2"><span>Name</span></th>
                <th><span>Ext</span></th>
                <th><span>Size</span></th>
                <th><span>Date</span></th>
              </tr>
              </thead>
              <tbody>${files.reduce((sum, cur, i) => sum + this.templates.views.sideBySide.listItem(cur, i, isLeft), '')}</tbody>
              <tfoot>
              <tr>
                <th colSpan="5">files: ${counters.files}&nbsp;&nbsp;&nbsp;folders: ${counters.folders}</th>
              </tr>
              </tfoot>
            </table>
          </div>`;
        },
        // Side-by-side wrapper
        wrap: (bookmarks = {left: [{name: '/', active: !0}], right: [{name: '/', active: !0}]}) =>
          `<div data-type="side-by-side">
          <div class="file-browser-panels-wrap">
            ${this.templates.views.sideBySide.panel(bookmarks.left, this.protected.files.left, !0)}
            ${this.templates.views.sideBySide.panel(bookmarks.right, this.protected.files.right, !1)}
          </div>
          <div class="file-browser-controls-wrap">
            <button tabindex="-1" name="rename" type="button"><span>Rename</span></button>
            <button tabindex="-1" name="view" type="button"><span>View</span></button>
            <button tabindex="-1" name="upload" type="button"><span>Upload</span></button>
            <button tabindex="-1" name="copy" type="button"><span>Copy</span></button>
            <button tabindex="-1" name="move" type="button"><span>Move</span></button>
            <button tabindex="-1" name="createDir" type="button"><span>Directory</span></button>
            <button tabindex="-1" name="delete" type="button"><span>Delete</span></button>
          </div>
        </div>`
      }
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
  };
}