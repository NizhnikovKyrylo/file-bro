export const FileOperationsMixin = {
  methods: {
    /**
     * Show folder or file info
     * @param file
     */
    fileInfo(file) {
      this.request(Object.assign(this.routes.size, {data: {path: file.path + file.basename}})).then(response => {
        file.size = response.data.size;
        this.$refs.fileInfo.file = file;
        if (file.isDir) {
          this.$refs.fileInfo.file.folders = response.data.folders;
          this.$refs.fileInfo.file.files = response.data.files;
        }
        this.$refs.fileInfo.show = true;
      });
    },
    /**
     * Open file in browser
     * @param data
     */
    fileOpen(data = {}) {
      const bookmark = this.getBookmark('panel' in data ? data.panel : null);
      const file = bookmark.files.list['i' in data ? data.i : bookmark.files.selected];
      !file.isDir && window.open(window.location.origin + this.getConfig().basePath + file.path + file.basename, '_blank');
    },
    /**
     * Rename a folder or a file
     * @param data
     * @returns {boolean}
     */
    fileRenameHandler(data) {
      const files = this.getBookmarksFiles(data.panel);
      const file = files[data.i];
      // Check if renamed folder already existing
      for (let i = 0, n = files.length; i < n; i++) {
        if (files[i].isDir && files[i].basename === data.value && file.basename !== data.value) {
          alert(`Directory "${data.value}" already exists`)
          return false
        }
      }

      if (file.basename !== data.value) {
        this.request(Object.assign(this.routes.move, {
          data: {
            from: file.path + file.basename,
            to: file.path + data.value
          }
        })).then(response => {
          if (200 === response.status) {
            let path = response.data.path.split('/');
            let filename = path.pop().split('.');
            path.join('/');
            let ext = filename.length < 2 ? '' : filename.split('.').pop();

            file.basename = data.value;
            file.ext = ext;
            file.filename = filename.join('.');
            file.path = path;
          }
        })
      }
    },
    /**
     * Open modal for the folder or the file rename
     */
    fileRenameShowModal() {
      this.$refs.renameFileModal.caption = 'New file name:';
      this.$refs.renameFileModal.data = {i: this.getBookmark().files.selected, panel: this.panels.active};
      this.$refs.renameFileModal.value = this.getSelected();
      this.$refs.renameFileModal.show = true;
      const awaitPopupOpen = setInterval(() => {
        if ('querySelector' in this.$refs.renameFileModal.$el) {
          clearInterval(awaitPopupOpen);
          this.$refs.renameFileModal.$el.querySelector('input').focus();
        }
      }, 5)
    },
    /**
     * Send request to remove files or folders
     * @param data
     */
    fileRemoveHandler(data) {
      const bookmark = this.getBookmark(data.panel);
      let requests = [];
      for (let i = 0, n = data.items.length; i < n; i++) {
        const file = bookmark.files.list[data.items[i]];
        requests.push(this.request(Object.assign(this.routes.remove, {data: {path: file.path + file.basename}})));
      }
      Promise.all(requests).then(() => {
        for (let i = 0, n = data.items.length; i < n; i++) {
          bookmark.files.list.splice(data.items[i], 1);
        }
        bookmark.files.inserted = [];
      });
    },
    /**
     * Open file upload dialog
     */
    fileUploadDialogOpen() {
      if (this.$parent.$refs.fileUpload.classList.contains('ready')) {
        this.$parent.$refs.fileUpload.classList.remove('ready')
        this.$parent.$refs.fileUpload.click();
      }
    },
    /**
     * Upload file handler
     * @param files
     * @returns {Promise}
     */
    fileUploadHandler(files) {
      let requests = [];
      const bookmark = this.getBookmark();
      for (let i = 0, n = files.length; i < n; i++) {
        requests.push(this.request(Object.assign(this.routes.upload, {
          data: {
            path: bookmark.path,
            name: files[i].name,
            file: files[i]
          }
        })))
      }

      Promise.all(requests).then(() => {
        this.getContent(bookmark.path, bookmark.filters.order).then(files => {
          bookmark.files = {
            depth: bookmark.files.depth,
            inserted: [],
            list: this.sort(files, bookmark.filters.order),
            order: bookmark.files.order,
            selected: 0
          };
        })
      })
    },
    /**
     * Get folder content
     * @param {object} bookmark
     * @param {object} file
     */
    folderContent(bookmark, file) {
      const fullPath = file.path + file.basename;

      this.getContent(fullPath, bookmark.filters.order).then(files => {
        let depth = file.filename === '[..]' ? bookmark.files.depth - 1 : bookmark.files.depth + 1;

        if (!bookmark.renamed) {
          bookmark.name = fullPath.replace(/\/$/, '');
          bookmark.name = bookmark.name.substring(bookmark.name.lastIndexOf('/') + 1);
          !bookmark.name.length && (bookmark.name = '/');
        }
        bookmark.path = fullPath;

        bookmark.files = {
          depth: depth,
          inserted: [],
          list: this.sort(files, bookmark.filters.order),
          order: bookmark.files.order,
          selected: 0
        };
      });
    },
    /**
     * Open folder
     * @param data
     */
    folderOpen(data) {
      const bookmark = this.getBookmark(data.panel);
      !this.popupIsOpen() && !bookmark.locked && this.folderContent(bookmark, bookmark.files.list[data.i]);
    },
    /**
     * Get a list of the files for the given folder
     * @param {string} path
     * @param {object} order
     * @returns {Promise<unknown>}
     */
    getContent(path, order) {
      return new Promise(resolve => this.request(Object.assign(this.routes.list, {data: {path: path}})).then(response => resolve(this.sort(response.data, order))));
    },
    /**
     * Get the file by its index or the selected file info
     * @param {string|null} panel
     * @param {int|null} bookmarkIndex
     * @param {int|null} fileIndex
     * @returns {string}
     */
    getSelected(panel = null, bookmarkIndex = null, fileIndex = null) {
      const bookmark = this.getBookmark(panel, bookmarkIndex);
      null === fileIndex && (fileIndex = bookmark.files.selected);
      return bookmark.files.list[fileIndex].basename;
    },
    /**
     * Sort files
     * @param files
     * @param order
     * @returns {*}
     */
    sort(files, order) {
      files.sort((a, b) => {
        const nameCompare = a.name.localeCompare(b.name);
        if (nameCompare !== 0) {
          return nameCompare;
        }
      });

      order.dir && files.reverse();

      switch (order.by) {
        case 'ext':
          files.sort((a, b) => {
            const compare = a[order.by].localeCompare(b[order.by]);
            if (compare !== 0) {
              return compare;
            }
          });
          break;
        case 'ctime':
        case 'size':
          files.sort((a, b) => this.sortFiles(a, b, [order.by]));
          break;
      }

      order.by !== 'name' && order.dir && files.reverse();

      files.sort((a, b) => (b.isDir - a.isDir));

      let result = [], temp = null;
      for (let i = 0, n = files.length; i < n; i++) {
        if (files[i].filename === '[..]') {
          temp = files[i];
        } else {
          result.push(files[i]);
        }
      }
      null !== temp && result.unshift(temp);

      return result;
    }
  }
};