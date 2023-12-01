export const FileOperationsMixin = {
  methods: {
    /**
     * Handle a file moving or copying
     * @param route
     * @param popupData
     * @param callback
     */
    copyOrMoveHandle(route, popupData, callback) {
      const bookmark = this.getBookmark();

      const otherBookmark = this.getBookmark(this.panels.active === 'left' ? 'right' : 'left');

      const fileIndexes = bookmark.files.inserted.length > 1 ? bookmark.files.inserted : [bookmark.files.selected];

      let requests = [];

      this.$refs.fileQueue.caption = popupData.caption;
      this.$refs.fileQueue.title = popupData.title;
      this.$refs.fileQueue.show = true;

      for (let i = 0, n = fileIndexes.length; i < n; i++) {
        if (bookmark.files.list[fileIndexes[i]].path !== this.$refs.copyFileModal.value) {
          const file = bookmark.files.list[fileIndexes[i]];

          this.$refs.fileQueue.files.push({name: file.basename, progress: 0});

          requests.push(this.request(Object.assign(route, {
            data: {
              from: file.path + file.basename,
              to: otherBookmark.path
            },
            onUploadProgress: () => {
              this.$refs.fileQueue.files[i].progress = 100;
            }
          })));
        }
      }

      Promise.all(requests).then(() => callback(bookmark, otherBookmark));
    },
    /**
     * Copy a file or a folder
     */
    fileCopyHandler() {
      this.copyOrMoveHandle(
        this.routes.copy,
        {
          caption: 'File copy queue:',
          title: 'File copying'
        },
        (bookmark, otherBookmark) => {
          this.refreshContent(bookmark);
          this.refreshContent(otherBookmark);
        }
      );
    },
    /**
     * Open modal for the folder or the file copy
     */
    fileCopyShowModal() {
      const bookmark = this.getBookmark();

      this.$refs.copyFileModal.caption = bookmark.files.inserted.length > 1
        ? `Copy ${bookmark.files.inserted.length} selected files/directories?`
        : `Copy selected "${bookmark.files.list[bookmark.files.selected].basename}"?`;

      const targetBookmark = this.getBookmark(this.panels.active === 'left' ? 'right' : 'left');
      this.$refs.copyFileModal.value = targetBookmark.path;
      this.$refs.copyFileModal.show = true;
      const awaitPopupOpen = setInterval(() => {
        if ('querySelector' in this.$refs.copyFileModal.$el) {
          clearInterval(awaitPopupOpen);
          this.$refs.copyFileModal.$el.querySelector('input').focus();
        }
      }, 5);
    },
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
     * Move a file or a folder
     */
    fileMoveHandler() {
      this.copyOrMoveHandle(
        this.routes.move,
        {
          caption: 'File move queue:',
          title: 'File moving'
        },
        (bookmark, otherBookmark) => {
          this.refreshContent(bookmark);
          this.refreshContent(otherBookmark);
        }
      );
    },
    /**
     * Open modal for the folder or the file move
     */
    fileMoveShowModal() {
      const bookmark = this.getBookmark();

      this.$refs.moveFileModal.caption = bookmark.files.inserted.length > 1
        ? `Copy ${bookmark.files.inserted.length} selected files/directories?`
        : `Copy selected "${bookmark.files.list[bookmark.files.selected].basename}"?`;

      const targetBookmark = this.getBookmark(this.panels.active === 'left' ? 'right' : 'left');
      this.$refs.moveFileModal.value = targetBookmark.path;
      this.$refs.moveFileModal.show = true;
      const awaitPopupOpen = setInterval(() => {
        if ('querySelector' in this.$refs.moveFileModal.$el) {
          clearInterval(awaitPopupOpen);
          this.$refs.moveFileModal.$el.querySelector('input').focus();
        }
      }, 5);
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
          alert(`Directory "${data.value}" already exists`);
          return false;
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
        });
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
      }, 5);
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
      Promise.all(requests).then(() => this.refreshContent(bookmark));
    },
    /**
     * Show the file remove modal
     */
    fileRemoveShowModal() {
      const bookmark = this.getBookmark();
      const items = bookmark.files.inserted.length ? bookmark.files.inserted : [bookmark.files.selected];
      const caption = items.length > 1
        ? `${items.length} selected files/folders?` + items.reduce((sum, cur) => sum + `<p>${bookmark.files.list[cur].basename}</p>`, '')
        : `selected "${bookmark.files.list[items[0]].basename}"?`;
      this.$refs.deleteModal.data = {
        items: items,
        panel: this.panels.active
      };
      this.$refs.deleteModal.caption = `Do you really want to remove ${caption}`;
      this.$refs.deleteModal.show = true;
    },
    /**
     * Open file upload dialog
     */
    fileUploadDialogOpen() {
      if (this.$parent.$refs.fileUpload.classList.contains('ready')) {
        this.$parent.$refs.fileUpload.classList.remove('ready');
        this.$parent.$refs.fileUpload.click();
      }
    },
    /**
     * Upload file handler
     * @param files
     * @returns {Promise}
     */
    fileUploadHandler(files) {
      const bookmark = this.getBookmark();
      let requests = [];

      this.$refs.fileQueue.caption = 'File upload queue:';
      this.$refs.fileQueue.title = 'File uploading';
      this.$refs.fileQueue.show = true;

      for (let i = 0, n = files.length; i < n; i++) {
        this.$refs.fileQueue.files.push({name: files[i].name, progress: 0});
        const initSize = files[i].size;
        requests.push(
          this.request(
            Object.assign(this.routes.upload, {
              data: {
                path: bookmark.path,
                name: files[i].name,
                file: files[i]
              },
              onUploadProgress: progressEvent => {
                let progress = parseInt((progressEvent.loaded / initSize) * 100);
                this.$refs.fileQueue.files[i].progress = progress > 100 ? 100 : progress;
              }
            }))
            .catch(error => alert(`Cannot upload file "${files[i].name}". Reason: ${error.response.statusText}`))
        );
      }

      Promise.all(requests).then(() => this.refreshContent(bookmark));
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
     * Create folder handler
     * @param data
     */
    folderCreateHandler(data) {
      const bookmark = this.getBookmark();

      this.request(Object.assign(this.routes.create, {
        data: {
          path: bookmark.path + data.value
        }
      })).then(response => 201 === response.status && this.refreshContent(bookmark));
    },
    /**
     * Show the folder creation array
     */
    folderCreateShowModal() {
      this.$refs.createFolderModal.caption = 'Input new directory name:';
      this.$refs.createFolderModal.value = [];
      this.$refs.createFolderModal.show = true;

      const awaitPopupOpen = setInterval(() => {
        if ('querySelector' in this.$refs.createFolderModal.$el) {
          clearInterval(awaitPopupOpen);
          this.$refs.createFolderModal.$el.querySelector('input').focus();
        }
      }, 5);
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
     * Refresh the active bookmark content
     * @param bookmark
     */
    refreshContent(bookmark) {
      this.getContent(bookmark.path, bookmark.filters.order).then(files => {
        // Bookmark files structure
        const bookmarkFiles = {
          depth: bookmark.files.depth,
          inserted: [],
          list: this.sort(files, bookmark.filters.order),
          order: bookmark.files.order,
          selected: 0
        };
        // Set files to the current bookmark
        bookmark.files = bookmarkFiles;
        const otherBookmark = this.getBookmark(this.panels.active === 'left' ? 'right' : 'left');
        // Set the same file structure if the left and right bookmarks has the same path
        if (bookmark.path === otherBookmark.path) {
          otherBookmark.files = bookmarkFiles;
        }

        bookmark.files.inserted = [];
      });
    },
    pasteFiles(panel, bookmark, data) {
      console.log(panel, bookmark, data)
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