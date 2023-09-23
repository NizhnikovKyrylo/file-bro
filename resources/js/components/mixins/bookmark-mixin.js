export const BookmarkMixin = {
  methods: {
    /**
     * Create a bookmark
     * @param {object} data
     */
    bookmarkCreate(data) {
      const bookmarks = this.panels[data.panel].bookmarks;
      // Inactivate the current tab
      for (let i = 0, n = bookmarks.length; i < n; i++) {
        bookmarks[i].active = false;
      }
      // Create copy of the current bookmark as a new one
      bookmarks.push(this.defaultBookmark(bookmarks[data.i].files.list, true));
    },
    /**
     * Toggle the bookmark "locked" status
     * @param {object} data
     */
    bookmarkLockToggle(data) {
      this.panels[data.panel].bookmarks[data.i].locked = !this.panels[data.panel].bookmarks[data.i].locked;
    },
    /**
     * Remove bookmark
     * @param {object} data
     */
    bookmarkRemove(data) {
      const bookmarks = this.panels[data.panel].bookmarks;
      if (bookmarks.length > 1) {
        // The active tab was removed
        const activeRemoved = bookmarks[data.i].active;
        // Remove tab
        bookmarks.splice(data.i, 1);
        // If active tab was removed
        if (activeRemoved) {
          // Show the last tab as active
          const index = bookmarks.length - 1;
          this.panels[data.panel].shownBookmarkIndex = index;
          bookmarks[index].active = true;
        }
      }
    },
    /**
     * Close all bookmarks except the active one
     * @param {string} panel
     */
    bookmarkRemoveAll(panel) {
      const bookmarks = this.panels[panel].bookmarks;
      // Get the active bookmark index
      let index = 0;
      for (let i = 0, n = bookmarks.length; i < n; i++) {
        if (bookmarks[i].active) {
          index = i;
          break;
        }
      }
      this.panels[panel].shownBookmarkIndex = 0;
      this.panels[panel].bookmarks = [Object.assign({}, bookmarks[index])];
    },
    /**
     * Set new name to the bookmark
     * @param {object} data
     */
    bookmarkRenameHandler(data) {
      this.panels[data.panel].bookmarks[data.i].name = data.value;
    },
    /**
     * Show Bookmark Rename Modal
     * @param {object} data
     */
    bookmarkRenameShowModal(data) {
      this.$refs.renameModal.caption = 'New tab name:';
      this.$refs.renameModal.data = data;
      this.$refs.renameModal.value = this.panels[data.panel].bookmarks[data.i].name;
      this.$refs.renameModal.show = true;
    },
    /**
     * Call bookmark context menu
     * @param {event} e
     * @param {int} i
     * @param {string} panel
     */
    bookmarkRightClick(e, i, panel) {
      e.preventDefault();
      this.$refs.bookmarkContextMenu.index = i;
      this.$refs.bookmarkContextMenu.panel = panel;
      this.$refs.bookmarkContextMenu.left = e.clientX;
      this.$refs.bookmarkContextMenu.show = true;
    },
    /**
     * Generate a bookmark body
     * @param {Array} list
     * @param {boolean} active
     * @returns {object}
     */
    defaultBookmark(list = [], active = false) {
      return {
        active: active,
        name: '/',
        path: '/',
        locked: false,
        files: {
          inserted: [],
          selected: 0,
          order: {
            by: 'name',
            dir: 'asc'
          },
          list: list
        }
      };
    },
    /**
     * Retrieve the active bookmark of the active panel
     * @param {string} panel
     * @returns {object}
     */
    getActiveBookmark(panel) {
      const index = this.panels[panel]?.shownBookmarkIndex || 0;
      const bookmarks = this.panels[panel].bookmarks;

      return typeof bookmarks[index] !== 'undefined' ? bookmarks[index] : {};
    },
    /**
     * Get files of the active bookmark
     * @param {string} panel
     * @returns {Array}
     */
    getBookmarksFiles(panel) {
      // Get active bookmark
      const bookmark = this.getActiveBookmark(panel);
      // If there is a bookmark with such index and this bookmark contain files
      return bookmark.hasOwnProperty('files') ? bookmark.files.list : [];
    }
  }
}