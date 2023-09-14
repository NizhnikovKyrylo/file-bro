<template>
  <div class="file-browser-commander-view-wrap">
    <template v-for="panel in ['left', 'right']">
      <div class="file-browser-commander-panel-wrap" :class="{active: panels.active === panel}">
        <ul class="commander-bookmarks-list">
          <li
            v-for="bookmark in panels[panel].bookmarks"
            :class="{active: bookmark.active}"
            :title="bookmark.path"
          >
            {{ bookmark.name }}
          </li>
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
              files: {{ getBookmarksFiles(panel).filter(i => !i.isDir).length }};
              &nbsp;&nbsp;&nbsp;&nbsp;
              folders: {{ getBookmarksFiles(panel).filter(i => i.isDir).length }};
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import ListRow from "./commander/ListRow.vue";

export default {
  components: {ListRow},
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
     * Generate a bookmark body
     * @param list
     * @param active
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
      }
    },
    /**
     * Get files of the active bookmark
     * @param panel
     * @returns {[]|*[]}
     */
    getBookmarksFiles(panel) {
      // Get active bookmark index
      const index = this.panels[panel].shownBookmarkIndex;
      // If there is a bookmark with such index and this bookmark contain files
      return typeof this.panels[panel].bookmarks[index] !== 'undefined' && this.panels[panel].bookmarks[index].hasOwnProperty('files')
        ?  this.panels[panel].bookmarks[index].files.list
        : []
    },
    /**
     * Select row click
     * @param data
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

        this.panels.left.bookmarks.push(this.defaultBookmark(files, true))
        this.panels.right.bookmarks.push(this.defaultBookmark(files, true))
      }
    });
  },
  mounted() {
    // Change panel with 'tab' press
    document.onkeydown = e => {
      if ('tab' === e.key.toLowerCase()) {
        e.preventDefault()
        this.panels.active = 'left' === this.panels.active ? 'right' : 'left';
      }
    }
  }
};
</script>