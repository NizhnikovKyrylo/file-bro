export const FileOperationsMixin = {
  methods: {
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
    /**
     * Get a list of the files for the given folder
     * @param {string} path
     * @param {object} order
     * @returns {Promise<unknown>}
     */
    getContent(path, order) {
      return new Promise(
        resolve => this.request(
          Object.assign(
            this.routes.list,
            {data: {path: path}}
          )
        ).then(response => resolve(this.sort(response.data, order)))
      );
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

        let path = '/';
        if (files.length > 0) {
          const parts = files[0].path.split('/').filter(Boolean);
          if (parts.length > 1) {
            parts.pop();
            path += parts.join('/') + '/';
          }
        }

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
          !bookmark.name.length && (bookmark.name = '/');
        }
        bookmark.path = fullPath;

        bookmark.files = {
          depth: depth,
          inserted: [],
          list: files,
          order: bookmark.files.order,
          selected: 0
        };
      })
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

      if (order.dir) {
        files.reverse();
      }

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

      if (order.by !== 'name' && order.dir) {
        files.reverse();
      }

      files.sort((a, b) => (b.isDir - a.isDir));

      return files;
    }
  }
};