<template>
  <div class="file-browser-commander-view-wrap">
    <template v-for="panel in ['left', 'right']">
      <div class="file-browser-commander-panel-wrap" :class="{active: panels.active === panel}">
        <ul class="commander-bookmarks-list">
          <BookmarkElement
            v-for="(bookmark, i) in panels[panel].bookmarks"
            :active="bookmark.active"
            :locked="bookmark.locked"
            :name="bookmark.name"
            :title="bookmark.path"
            @contextmenu="bookmarkRightClick($event, i, panel)"
          />
        </ul>

        <div class="commander-file-list-wrap">
          <div class="commander-file-list-header">
            <div class="commander-file-list-column">
              <span>Name</span>
              <i class="file-browser-icon arrow-up"></i>
            </div>
            <div class="commander-file-list-column">
              <span>Ext</span>
            </div>
            <div class="commander-file-list-column">
              <span>Size</span>
            </div>
            <div class="commander-file-list-column">
              <span>Date</span>
            </div>
          </div>

          <div class="commander-file-list-content" v-if="panels[panel].bookmarks.length">
            <ListRow
              v-for="(file, i) in getBookmarksFiles(panel)"
              :file="file"
              :index="i"
              :inserted="panels[panel].bookmarks[panels[panel].shownBookmarkIndex].files.inserted.indexOf(i) >= 0"
              :selected="panels.active === panel && i === panels[panel].bookmarks[panels[panel].shownBookmarkIndex].files.selected"
              :panel="panel"
              @selectRow="rowSelected"
            />
          </div>

          <div class="commander-file-list-header">
            <div class="commander-file-list-column">
              files: {{ countFiles(panel, false) }};
              &nbsp;&nbsp;&nbsp;&nbsp;
              folders: {{ countFiles(panel) }};
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>

  <BookmarkContextMenu
    ref="bookmarkContextMenu"
    @close="bookmarkRemove"
    @closeAll="bookmarkRemoveAll"
    @new="bookmarkCreate"
    @rename="bookmarkRenameShowModal"
    @toggleLock="bookmarkLockToggle"
  />

  <InputModal
    ref="renameModal"
    title="Rename tab"
    @apply="bookmarkRenameHandler"
  />

  <InputModal
    ref="deleteModal"
    title="Remove file/directory"
    :hideInput="true"
    @apply="fileRemoveHandler"
  />

  <FileInfoModal ref="fileInfo"/>
</template>

<script>
import BookmarkContextMenu from "./commander/BookmarkContextMenu.vue";
import BookmarkElement from "./commander/BookmarkElement.vue";
import InputModal from "./default-components/InputModal.vue";
import ListRow from "./commander/ListRow.vue";
import {BookmarkMixin} from "./mixins/bookmark-mixin.js";
import FileInfoModal from "./default-components/FileInfoModal.vue";

export default {
  components: {FileInfoModal, InputModal, BookmarkElement, BookmarkContextMenu, ListRow},
  data() {
    return {
      panels: {
        active: 'left',
        left: {
          bookmarks: [],
          shownBookmarkIndex: 0
        },
        right: {
          bookmarks: [],
          shownBookmarkIndex: 0
        }
      }
    };
  },
  props: {
    routes: {
      type: Object,
      required: true
    }
  },
  inject: {
    request: 'request',
    sortFiles: 'sortFiles'
  },
  mixins: [BookmarkMixin],
  methods: {
    /**
     * Count files or folders
     * @param {string} panel
     * @param {boolean} checkFolders
     * @returns {*|number}
     */
    countFiles(panel, checkFolders = true) {
      const files = this.getBookmarksFiles(panel);
      return Array.isArray(files) ? files.filter(i => i.isDir === checkFolders).length : 0;
    },
    /**
     * Send request to remove files or folders
     * @param data
     */
    fileRemoveHandler(data) {
      // Bookmark index
      const index = this.panels[data.panel]?.shownBookmarkIndex || 0;
      // Bookmark files
      const files = this.panels[data.panel].bookmarks[index].files;
      let requests = [];
      for (let i = 0, n = data.items.length; i < n; i++) {
        const file = files.list[data.items[i]];
        requests.push(this.request(Object.assign(this.routes.remove, {data: {path: file.path + file.basename}})));
      }
      Promise.all(requests).then(() => {
        for (let i = 0, n = data.items.length; i < n; i++) {
          files.list.splice(data.items[i], 1);
        }
        files.inserted = [];
      });
    },
    /**
     * Insert row
     * @param {object} bookmark
     * @param {int} i
     * @returns {this}
     */
    insertRow(bookmark, i = null) {
      null === i && (i = bookmark.files.selected);
      const index = bookmark.files.inserted.indexOf(i);
      if (index >= 0) {
        bookmark.files.inserted.splice(index, 1);
      } else {
        bookmark.files.inserted.push(i);
      }

      return this;
    },
    /**
     * Press "pageDown" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    jumpDown(bookmark, e) {
      // Increase position value for 20
      let position = bookmark.files.selected + 20;
      // Check the position is lower than the file number
      position > bookmark.files.list.length - 1 && (position = bookmark.files.list.length - 1);
      // Move to the position below, Scroll to element
      this.scrollToElement(bookmark.files.selected = position, e);
      return this;
    },
    /**
     * Press "pageUp" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    jumpUp(bookmark, e) {
      // Decrease position value for 20
      let position = bookmark.files.selected - 20;
      // Check the position is greater than 0
      position < 0 && (position = 0);
      // Move to the position below, Scroll to element
      this.scrollToElement(bookmark.files.selected = position, e);
      return this;
    },
    /**
     * Press "home" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveToBegin(bookmark, e) {
      this.scrollToElement(bookmark.files.selected = 0, e);
      return this;
    },
    /**
     * Press "end" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveToEnd(bookmark, e) {
      const files = bookmark.files.list.length - 1;
      this.scrollToElement(bookmark.files.selected = files > 0 ? files : 0, e);
      return this;
    },
    /**
     * Press "Arrow down" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveDown(bookmark, e) {
      // If element index is less than files number, move the selection below
      bookmark.files.list.length - 1 > bookmark.files.selected && bookmark.files.selected++;
      // Scroll to element
      this.scrollToElement(bookmark.files.selected, e);
      return this;
    },
    /**
     * Press "Arrow up" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveUp(bookmark, e) {
      // If element index is greater than 0, move the selection upper
      bookmark.files.selected > 0 && bookmark.files.selected--;
      // Scroll to element
      this.scrollToElement(bookmark.files.selected, e);
      return this;
    },
    /**
     * Check is any of popup is open
     * @returns {boolean}
     */
    popupIsOpen() {
      return this.$refs.bookmarkContextMenu.show || this.$refs.deleteModal.show || this.$refs.renameModal.show || this.$refs.fileInfo.show;
    },
    /**
     * Select row click
     * @param {object} data
     */
    rowSelected(data) {
      // Set active panel
      this.panels.active = data.panel;
      const bookmarks = this.panels[this.panels.active].bookmarks;

      for (let i = 0, n = bookmarks.length; i < n; i++) {
        if (bookmarks[i].active) {
          // Reset inserted items
          bookmarks[i].files.inserted = [];
          // Shift key was pressed
          if (data.shift) {
            // Selected row index
            const selected = bookmarks[i].files.selected;
            // Check selection direction
            const selection = bookmarks[i].files.selected > data.i
              ? {max: selected, min: data.i} // Selection direction up
              : {max: data.i, min: selected}; // Selection direction down
            // Highlight the inserted rows
            bookmarks[i].files.inserted = [...Array(selection.max - selection.min + 1).keys()].map(i => i + selection.min);
          }
          // Set selected file position
          bookmarks[i].files.selected = data.i;
          break;
        }
      }
    },
    /**
     * Move focus to the selected row
     * @param {int} i
     * @param {event} e
     */
    scrollToElement(i, e) {
      // File list container
      const box = e.target.querySelector('.file-browser-commander-panel-wrap.active .commander-file-list-content');
      // Target row
      const elem = box.querySelectorAll('.commander-file-list-row')[i];
      // Figure out current element position
      const current = i * elem.offsetHeight - (box.offsetHeight - 60);
      // Moving up point
      const upPoint = box.scrollTop - box.offsetHeight + 150;
      // Moving down event
      current > box.scrollTop && elem.scrollIntoView(true);
      // Moving up event
      current < upPoint
      && null !== elem.previousSibling.previousSibling
      && typeof elem.previousSibling.previousSibling.scrollIntoView !== 'undefined'
      && elem.previousSibling.previousSibling.scrollIntoView(true);

      current < upPoint && i === 0 && elem.scrollIntoView(true);
    }
  },
  beforeMount() {
    this.request(Object.assign(this.routes.list, {data: {path: '/'}})).then(response => {
      if (200 === response.status) {
        // Retrieve file fist from request
        const files = response.data;
        // Sort files by name and folder type. First go folders then files
        files.sort((a, b) => this.sortFiles(a, b)).reverse().sort((a, b) => this.sortFiles(a, b, 'isDir')).reverse();

        this.panels.left.bookmarks.push(this.defaultBookmark(files, true));
        this.panels.right.bookmarks.push(this.defaultBookmark(files, true));
      }
    });
  },
  mounted() {
    // Change panel with 'tab' press
    document.onkeydown = e => {
      const key = e.key.toLowerCase();

      console.log(key);
      if (['arrowdown', 'arrowup', 'delete', 'end', 'escape', 'home', 'pagedown', 'pageup', 'tab', ' '].indexOf(key) >= 0) {
        e.preventDefault();
        const bookmark = this.getActiveBookmark(this.panels.active);

        switch (key) {
          // Press space to highlight or remove insertion, and get size if item is a folder
          case ' ':
            if (!this.popupIsOpen()) {
              this.insertRow(bookmark);
              const file = bookmark.files.list[bookmark.files.selected];
              // Get folder size
              file.isDir && this.request(Object.assign(
                this.routes.size,
                {data: {path: file.path + file.basename}}
              )).then(response => 200 === response.status && (file.size = response.data.size));
            }
            break;
          // Move down with "arrow down" press
          case 'arrowdown':
            e.shiftKey && this.insertRow(bookmark);
            this.moveDown(bookmark, e);
            break;
          // Move up with "arrow up" press
          case 'arrowup':
            e.shiftKey && this.insertRow(bookmark);
            this.moveUp(bookmark, e);
            break;
          // Move to the very end of the file list
          case 'end':
            if (e.shiftKey) {
              for (let i = bookmark.files.selected, n = bookmark.files.list.length; i < n; i++) {
                this.insertRow(bookmark, i);
              }
            }
            this.moveToEnd(bookmark, e);
            break;
          // Close modals on "esc"
          case 'escape':
            if (this.popupIsOpen()) {
              for (let modal of ['bookmarkContextMenu', 'deleteModal', 'renameModal', 'fileInfo']) {
                this.$refs[modal].show = false;
              }
            }
            break;
          // Move to the very beginning of the file list
          case 'home':
            if (e.shiftKey) {
              for (let i = bookmark.files.selected; i > 0; i--) {
                this.insertRow(bookmark, i);
              }
            }
            this.moveToBegin(bookmark, e);
            break;
          // Move down with "page down" press
          case 'pagedown':
            if (e.shiftKey) {
              for (let i = bookmark.files.selected, n = bookmark.files.selected + 20; i < n; i++) {
                i < bookmark.files.list.length && this.insertRow(bookmark, i);
              }
            }
            this.jumpDown(bookmark, e);
            break;
          // Move up with "page up" press
          case 'pageup':
            if (e.shiftKey) {
              for (let i = bookmark.files.selected, n = bookmark.files.selected - 20; i > n; i--) {
                i >= 0 && this.insertRow(bookmark, i);
              }
            }
            this.jumpUp(bookmark, e);
            break;
          // Switch panel with "tab" pressing
          case 'tab':
            this.panels.active = 'left' === this.panels.active ? 'right' : 'left';
            break;
        }
      }
    };

    document.onkeyup = e => {
      const key = e.key.toLowerCase();
      const bookmark = this.getActiveBookmark(this.panels.active);
      switch (key) {
        // Remove file or folder with "delete" key
        case 'delete':
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
          break;
        // Highlight inserted row
        case 'insert':
          this.insertRow(bookmark).moveDown(bookmark, e);
          break;
        case 'enter':
          if (e.altKey && !this.popupIsOpen()) {
            const file = bookmark.files.list[bookmark.files.selected];
            this.request(Object.assign(this.routes.size, {data: {path: file.path + file.basename}})).then(response => {
              file.size = response.data.size;
              this.$refs.fileInfo.file = file;
              if (file.isDir) {
                this.$refs.fileInfo.file.folders = response.data.folders;
                this.$refs.fileInfo.file.files = response.data.files;
              }
              this.$refs.fileInfo.show = true;
            });
          }
          break;
      }
    };
  }
};
</script>