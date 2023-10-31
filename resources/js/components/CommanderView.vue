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
            @click="bookmarkSwitch($event, i, panel)"
          />
        </ul>

        <div class="commander-file-list-wrap">
          <table class="commander-file-list">
            <thead>
            <tr>
              <TableHeadCol name="Name" type="name" :panel="panels.active" @setOrder="orderBy"/>
              <TableHeadCol name="Ext" type="ext" :panel="panels.active" @setOrder="orderBy"/>
              <TableHeadCol name="Size" type="size" :panel="panels.active" @setOrder="orderBy"/>
              <TableHeadCol name="Date" type="ctime" :panel="panels.active" @setOrder="orderBy"/>
            </tr>
            </thead>
            <tbody v-if="panels[panel].bookmarks.length">
            <ListRow
              v-for="(file, i) in getBookmarksFiles(panel)"
              :file="file"
              :index="i"
              :inserted="panels[panel].bookmarks[panels[panel].shownBookmarkIndex].files.inserted.indexOf(i) >= 0"
              :selected="panels.active === panel && i === panels[panel].bookmarks[panels[panel].shownBookmarkIndex].files.selected"
              :panel="panel"
              @selectRow="rowSelected"
              @rowContextMenu="openRowContextMenu"
              @openDir="folderOpen"
              @openFile="fileOpen"
            />
            </tbody>
            <tfoot>
            <tr>
              <td colspan="4">
                files: {{ countFiles(getBookmarksFiles(panel), false) }};
                &nbsp;&nbsp;&nbsp;&nbsp;
                folders: {{ countFiles(getBookmarksFiles(panel)) - (getBookmark()?.files?.depth > 0 ? 1 : 0) }};
              </td>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </template>
  </div>
  <div class="file-browser-commands-bar-wrap">
    <ul>
      <li @click="fileRenameShowModal"><span>Rename F2</span></li>
      <li @click="fileOpen"><span>View F3</span></li>
      <li @click="fileUploadDialogOpen"><span>Upload F4</span></li>
      <li @click="fileCopyShowModal"><span>Copy F5</span></li>
      <li @click="fileMoveShowModal"><span>Move F6</span></li>
      <li @click="folderCreateShowModal"><span>Folder F7</span></li>
      <li @click="fileRemoveShowModal"><span>Delete F8</span></li>
    </ul>
  </div>

  <BookmarkContextMenu
    ref="bookmarkContextMenu"
    @close="bookmarkRemove"
    @closeAll="bookmarkRemoveAll"
    @new="bookmarkCreate"
    @rename="bookmarkRenameShowModal"
    @toggleLock="bookmarkLockToggle"
  />

  <ListRowContextMenu ref="listRowContextMenu"/>

  <InputModal ref="copyFileModal" title="Copy file(s)" @apply="fileCopyHandler"/>

  <InputModal ref="createFolderModal" title="Create new directory" @apply="folderCreateHandler"/>

  <InputModal ref="deleteModal" title="Remove file/directory" :hideInput="true" @apply="fileRemoveHandler"/>

  <InputModal ref="moveFileModal" title="Move file(s)" @apply="fileMoveHandler"/>

  <InputModal ref="renameFileModal" title="Rename file" @apply="fileRenameHandler"/>

  <InputModal ref="renameTabModal" title="Rename tab" @apply="bookmarkRenameHandler"/>

  <FileInfoModal ref="fileInfo"/>

  <FileQueueModal ref="fileQueue"/>
</template>

<script>
import BookmarkContextMenu from "./commander/BookmarkContextMenu.vue";
import BookmarkElement from "./commander/BookmarkElement.vue";
import FileInfoModal from "./default-components/FileInfoModal.vue";
import InputModal from "./default-components/InputModal.vue";
import ListRow from "./commander/ListRow.vue";
import {BookmarkMixin} from "./commander/mixins/bookmark-mixin.js";
import {MovingMixin} from "./commander/mixins/moving-mixin.js";
import {FileOperationsMixin} from "./commander/mixins/file-operations-mixin.js";
import TableHeadCol from "./commander/TableHeadCol.vue";
import FileQueueModal from "./default-components/FileQueueModal.vue";
import ListRowContextMenu from "./commander/ListRowContextMenu.vue";

export default {
  components: {ListRowContextMenu, FileQueueModal, TableHeadCol, BookmarkElement, BookmarkContextMenu, FileInfoModal, InputModal, ListRow},
  data() {
    return {
      header: [
        {name: 'Name', type: 'name'},
        {name: 'Ext', type: 'ext'},
        {name: 'Size', type: 'size'},
        {name: 'Date', type: 'ctime'}
      ],
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
    getConfig: 'getConfig',
    countFiles: 'countFiles',
    request: 'request',
    sortFiles: 'sortFiles'
  },
  mixins: [BookmarkMixin, FileOperationsMixin, MovingMixin],
  methods: {
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
     * Show the context menu of the list row
     * @param data
     */
    openRowContextMenu(data) {
      // Set active panel
      this.panels.active = data.panel;
      const bookmark = this.getBookmark(data.panel);
      // Set selected file position
      bookmark.files.selected = data.i;

      this.$refs.listRowContextMenu.top = data.event.pageY;
      this.$refs.listRowContextMenu.left = data.event.pageX;
      this.$refs.listRowContextMenu.panel = data.panel;
      this.$refs.listRowContextMenu.bookmark = this.panels[data.panel].bookmarks.findIndex(item => item.active);
      this.$refs.listRowContextMenu.index = data.i;
      this.$refs.listRowContextMenu.show = true
    },
    /**
     * Set column order
     * @param by
     */
    orderBy(by) {
      const bookmark = this.getBookmark(this.panels.active);
      const order = bookmark.filters.order;
      if (by === order.by) {
        order.dir = !order.dir;
      } else {
        order.by = by;
      }
      this.getContent(bookmark.path, order).then(files => {
        bookmark.files.list = files;
      });
    },
    /**
     * Check is any of popup is open
     * @returns {boolean}
     */
    popupIsOpen() {
      return this.$refs.bookmarkContextMenu.show
        || this.$refs.copyFileModal.show
        || this.$refs.createFolderModal.show
        || this.$refs.deleteModal.show
        || this.$refs.fileInfo.show
        || this.$refs.fileQueue.show
        || this.$refs.moveFileModal.show
        || this.$refs.renameFileModal.show
        || this.$refs.renameTabModal.show;
    },
    /**
     * Select row click
     * @param {object} data
     */
    rowSelected(data) {
      // Set active panel
      this.panels.active = data.panel;
      const bookmark = this.getBookmark(data.panel);
      // Reset inserted items
      !data.ctrl && (bookmark.files.inserted = []);
      // Check "shift" was pressed
      if (data.shift) {
        // Selected row index
        const selected = bookmark.files.selected;
        // Check selection direction
        const selection = bookmark.files.selected > data.i
          ? {max: selected, min: data.i} // Selection direction up
          : {max: data.i, min: selected}; // Selection direction down
        // Highlight the inserted rows
        bookmark.files.inserted = [...Array(selection.max - selection.min + 1).keys()].map(i => i + selection.min);
      }
      // Check "ctrl" was pressed
      if (data.ctrl) {
        // Selected row index
        this.insertRow(bookmark, data.i);
      }
      // Set selected file position
      bookmark.files.selected = data.i;
    },
    /**
     * Move focus to the selected row
     * @param {int} i
     * @param {event} e
     */
    scrollToElement(i, e) {
      // File list container
      const box = e.target.querySelector('.file-browser-commander-panel-wrap.active .commander-file-list-wrap');
      // Figure out current element position
      const current = i * box.querySelectorAll('tbody tr')[i].offsetHeight;
      // Moving event
      if (current > box.offsetHeight - 200 || current < box.scrollTop) {
        box.scrollTop = current;
      }
    }
  },
  beforeMount() {
    this.getContent('/', {by: 'name', dir: false}).then(files => {
      this.panels.left.bookmarks.push(this.defaultBookmark(files, true));
      this.panels.right.bookmarks.push(this.defaultBookmark(files, true));
    });
  },
  mounted() {
    // Change panel with 'tab' press
    document.onkeydown = e => {
      const key = e.key.toLowerCase();

      const allowedKeys = [
        'arrowup', 'arrowright', 'arrowdown', 'arrowleft', 'home', 'end', 'pageup', 'pagedown',
        'backspace', 'enter', 'tab', ' ',
        'delete', 'escape',
        'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8',
      ];
      console.log(key);
      if (!this.popupIsOpen() && allowedKeys.indexOf(key) >= 0) {
        e.preventDefault();
        const bookmark = this.getBookmark(this.panels.active);

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
          // Move into the folder with "arrow right" press
          case 'arrowleft':
          case 'backspace':
            !bookmark.locked && bookmark.files.depth > 0 && this.folderContent(bookmark, bookmark.files.list[0])
            break;
          case 'arrowright':
            if (!bookmark.locked) {
              const file = bookmark.files.list[bookmark.files.selected];
              file.isDir && this.folderContent(bookmark, file);
            }
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
          case 'enter':
            if (!this.popupIsOpen()) {
              const file = bookmark.files.list[bookmark.files.selected];
              if (e.altKey) {
                this.fileInfo(file);
              } else if (file.isDir && !bookmark.locked) {
                this.folderContent(bookmark, file);
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
          // Open the rename modal with "F2" pressing
          case 'f2':
            this.fileRenameShowModal()
            break;
          // Open file in a new tab with "F3" pressing
          case 'f3':
            this.fileOpen()
            break;
          // Open a file dialog window with "F4" pressing
          case 'f4':
            this.fileUploadDialogOpen()
            break;
          // Open the copy files modal with "F5" pressing
          case 'f5':
            this.fileCopyShowModal()
            break;
          // Open the move files modal with "F6" pressing
          case 'f6':
            this.fileMoveShowModal();
            break;
          // Open the creation folder modal with "F7" pressing
          case 'f7':
            this.folderCreateShowModal();
            break;
          // Remove file or folder with "F8" key
          case 'f8':
            this.fileRemoveShowModal()
            break;
        }
      }
    };

    document.onkeyup = e => {
      const key = e.key.toLowerCase();
      const bookmark = this.getBookmark(this.panels.active);
      switch (key) {
        // Remove file or folder with "delete" key
        case 'delete':
          if (!this.popupIsOpen()) {
            this.fileRemoveShowModal()
          }
          break;
        // Highlight inserted row
        case 'insert':
          this.insertRow(bookmark).moveDown(bookmark, e);
          break;
        // Close modals on "esc"
        case 'escape':
          if (this.popupIsOpen()) {
            for (let modal of ['bookmarkContextMenu', 'copyFileModal', 'deleteModal', 'fileInfo', 'renameFileModal', 'renameTabModal']) {
              this.$refs[modal].show = false;
            }
          }
          break;
      }
    };
  }
};
</script>