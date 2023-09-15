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

          <template v-if="panels[panel].bookmarks.length">
            <ListRow
              v-for="(file, i) in getBookmarksFiles(panel)"
              :file="file"
              :index="i"
              :selected="panels.active === panel && i === panels[panel].bookmarks[panels[panel].shownBookmarkIndex].files.selected"
              :panel="panel"
              @selectRow="rowSelected"
            />
          </template>

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

  <InputModal ref="inputModal" @apply="bookmarkRenameHandle"/>
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
      // Inactivate the current tab
      for (let i = 0, n = this.panels[data.panel].bookmarks.length; i < n; i++) {
        this.panels[data.panel].bookmarks[i].active = false;
      }
      // Create copy of the current bookmark as a new one
      this.panels[data.panel].bookmarks.push(this.defaultBookmark(this.panels[data.panel].bookmarks[data.i].files.list, true));
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
     * @param data
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
    bookmarkRenameHandle(data) {
      this.panels[data.panel].bookmarks[data.i].name = data.value;
    },
    /**
     * Show Bookmark Rename Modal
     * @param {object} data
     */
    bookmarkRenameShowModal(data) {
      this.$refs.inputModal.title = 'Rename tab';
      this.$refs.inputModal.caption = 'New tab name';
      this.$refs.inputModal.data = data;
      this.$refs.inputModal.value = this.panels[data.panel].bookmarks[data.i].name;
      this.$refs.inputModal.show = true;
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
     * Get files of the active bookmark
     * @param {string} panel
     * @returns {Array}
     */
    getBookmarksFiles(panel) {
      // Get active bookmark index
      const index = this.panels[panel]?.shownBookmarkIndex || 0;
      // If there is a bookmark with such index and this bookmark contain files
      return typeof this.panels[panel].bookmarks[index] !== 'undefined' && this.panels[panel].bookmarks[index].hasOwnProperty('files')
        ? this.panels[panel].bookmarks[index].files.list
        : [];
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
      if ('tab' === e.key.toLowerCase()) {
        e.preventDefault();
        this.panels.active = 'left' === this.panels.active ? 'right' : 'left';
      }
    };
  }
};
</script>