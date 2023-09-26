export const FileOperationsMixin = {
  methods: {
    /**
     * Send request to remove files or folders
     * @param data
     */
    fileRemoveHandler(data) {
      const bookmark = this.getBookmark(data.panel)
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
      const bookmark = this.getBookmark(data.panel)
      const file = bookmark.files.list[data.i];
      window.open(window.location.origin + this.getConfig().basePath + file.path + file.basename, '_blank')
    },
    /**
     *
     * @param bookmark
     * @param file
     */
    folderContent(bookmark, file) {
      const fullPath = file.path + file.basename;
      this.request(Object.assign(this.routes.list, {data: {path: fullPath}})).then(response => {
        if (200 === response.status) {
          let depth = file.filename === '[..]' ? bookmark.files.depth - 1 : bookmark.files.depth + 1;

          let files = response.data;

          let path = file.path;
          if (files.length) {
            path = files[0].path.split('/').filter(i => i !== null && i !== '');
            if (path.length > 1) {
              path.splice(-1);
              path = '/' + path.join('/') + '/';
            } else {
              path = '/';
            }
          }

          files.sort((a, b) => this.sortFiles(a, b)).reverse().sort((a, b) => this.sortFiles(a, b, 'isDir')).reverse();

          if (depth > 0) {
            files.unshift({
              atime: file.atime,
              basename: '',
              ctime: file.ctime,
              ext: "",
              filename: '[..]',
              isDir: true,
              'mime-type': null,
              mtime: file.mtime,
              name: file.name,
              path: path,
              size: file.size,
              type: file.type
            });
          }

          if (!bookmark.renamed) {
            bookmark.name = fullPath.replace(/\/$/, '');
            bookmark.name = bookmark.name.substring(bookmark.name.lastIndexOf('/') + 1);
            bookmark.name.length && (bookmark.name = '/');
          }
          bookmark.path = fullPath;

          bookmark.files = {
            depth: depth,
            inserted: [],
            list: files,
            order: bookmark.files.order,
            selected: 0
          };
        }
      });
    },
    /**
     * Open folder
     * @param data
     */
    folderOpen(data) {
      const bookmark = this.getBookmark(data.panel)
      !this.popupIsOpen() && !bookmark.locked && this.folderContent(bookmark, bookmark.files.list[data.i]);
    }
  }
}