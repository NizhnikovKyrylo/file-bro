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
              .filter(i => i.isDir).length
              files: {{ typeof getBookmarksFiles(panel) }} {{ Array.isArray(getBookmarksFiles(panel)) }};

              &nbsp;&nbsp;&nbsp;&nbsp;
              folders: {{ typeof getBookmarksFiles(panel) }} {{ Array.isArray(getBookmarksFiles(panel)) }};
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
    caption="New tab name"
    title="Rename tab"
    @apply="bookmarkRenameHandler"
  />

  <InputModal
    ref="deleteModal"
    title="Remove file/directory"
    :hideInput="true"
    @apply="fileRemoveHandler"
  />
</template>

<script>
import BookmarkContextMenu from "./commander/BookmarkContextMenu.vue";
import BookmarkElement from "./commander/BookmarkElement.vue";
import InputModal from "./default-components/InputModal.vue";
import ListRow from "./commander/ListRow.vue";

export default {
  components: {InputModal, BookmarkElement, BookmarkContextMenu, ListRow},
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
    fileRemoveHandler(data) {
      console.log(data);
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
     * Select row click
     * @param {object} data
     */
    rowSelected(data) {
      // Set active panel
      this.panels.active = data.panel;
      const bookmarks = this.panels[this.panels.active].bookmarks;
      for (let i = 0, n = bookmarks.length; i < n; i++) {
        if (bookmarks[i].active) {
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
      const box = e.target.querySelector('.file-browser-commander-panel-wrap.active .commander-file-list-content');
      const elem = box.querySelectorAll('.commander-file-list-row')[i];
      const current = i * elem.offsetHeight - (box.offsetHeight - 60);
      const upPoint = box.scrollTop - box.offsetHeight + 150;

      current > box.scrollTop && elem.scrollIntoView(true);

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
      if (['tab', 'arrowdown', 'arrowup', 'end', 'home', 'pagedown', 'pageup'].indexOf(key) >= 0) {
        e.preventDefault();

        const bookmark = this.getActiveBookmark(this.panels.active);

        switch (key) {
          case 'arrowdown':
            e.shiftKey && this.insertRow(bookmark);
            this.moveDown(bookmark, e);
            break;
          case 'arrowup':
            e.shiftKey && this.insertRow(bookmark);
            this.moveUp(bookmark, e);
            break;
          case 'end':
            this.moveToEnd(bookmark, e);
            break;
          case 'home':
            this.moveToBegin(bookmark, e);
            break;
          case 'pagedown':
            if (e.shiftKey) {
              for (let i = bookmark.files.selected, n = bookmark.files.selected + 20; i < n; i++) {
                i < bookmark.files.list.length && this.insertRow(bookmark, i)
              }
            }
            this.jumpDown(bookmark, e);
            break;
          case 'pageup':
            if (e.shiftKey) {
              for (let i = bookmark.files.selected, n = bookmark.files.selected - 20; i > n; i--) {
                i >= 0 && this.insertRow(bookmark, i);
              }
            }
            this.jumpUp(bookmark, e);
            break;
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
        case 'insert':
          this.insertRow(bookmark).moveDown(bookmark, e);
          break;
      }
    };
  }
};
</script>