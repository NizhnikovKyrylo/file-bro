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
    fileOpen(data) {
      const bookmark = this.getBookmark(data.panel);
      const file = bookmark.files.list[data.i];
      window.open(window.location.origin + this.getConfig().basePath + file.path + file.basename, '_blank');
    },
    fileRenameHandler(data) {
      const files = this.getBookmarksFiles(data.panel);
      const file = files[data.i];
      let filename = data.value.split('.')
      const ext = filename.pop();

      let rename = {
        constraint: false,
        answer: true
      };
      for (let i = 0, n = files.length; i < n; i++) {
        if (files[i].basename === data.value) {
          rename.constraint = true;
          break;
        }
      }

      if (rename.constraint) {
        const type = file.isDir ? 'folder' : 'file';
        rename.answer = confirm(`The ${type} "${data.value}" already exists. Do you want to overwrite it?`);
      }

      if (rename.answer) {

      }
      // file.basename = data.value;
      // file.ext = ext;
      // file.filename = filename.join('.');
      // file.name = file.name.toLowerCase();
    },
    fileRenameShowModal() {
      this.$refs.renameFileModal.caption = 'New file name:';
      this.$refs.renameFileModal.data = {i: this.getBookmark().files.selected, panel: this.panels.active};
      this.$refs.renameFileModal.value = this.getSelected();
      this.$refs.renameFileModal.show = true;
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