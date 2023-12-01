<template>
  <ul class="context-menu" v-if="show" :style="{left: left + 'px', top: top + 'px'}" v-click-out-side="() => {show = false}">
    <li class="separator" @click="open"><span>Open</span></li>
    <li @click="move"><span>Move</span></li>
    <li @click="remove"><span>Delete</span></li>
    <li class="separator" @click="rename"><span>Rename</span></li>
    <li @click="copyOrCut(1)"><span>Cut</span></li>
    <li @click="copyOrCut(0)"><span>Copy</span></li>
    <li class="separator" @click="paste"><span>Paste</span></li>
    <li @click="info"><span>Properties</span></li>
  </ul>
</template>

<script>
import clickOutSide from "../mixins/click-outside.js";

export default {
  directives: {
    clickOutSide
  },
  data() {
    return {
      bookmark: 0,
      index: 0,
      left: 10,
      top: 0,
      panel: 'left',
      show: false
    };
  },
  emits: ['paste'],
  methods: {
    copyOrCut(cut) {
      this.show = false;
      const bookmark = this.$parent.panels[this.panel].bookmarks[this.bookmark];

      sessionStorage.setItem('fileBrowserBuffer', JSON.stringify({
        cut: cut,
        panel: this.panel,
        bookmark: this.bookmark,
        indexes: bookmark.files.inserted.length ? bookmark.files.inserted : [bookmark.files.selected]
      }))

      bookmark.files.inserted = []
    },
    /**
     * Get the file or folder info
     */
    info() {
      this.show = false;
      const bookmark = this.$parent.panels[this.panel].bookmarks[this.bookmark];
      bookmark.files.inserted = []
      this.$parent.fileInfo(bookmark.files.list[this.index]);
    },
    /**
     * Move a file or a folder to another folder
     */
    move() {
      this.show = false;
      this.$parent.fileMoveShowModal()
    },
    /**
     * Open folder
     */
    open() {
      this.show = false;
      const bookmark = this.$parent.panels[this.panel].bookmarks[this.bookmark];
      const file = bookmark.files.list[this.index]
      if (file.isDir) {
        this.$parent.folderContent(bookmark, file)
      } else {
        window.open(window.location.origin + this.$parent.getConfig().basePath + file.path + file.basename, '_blank');
      }
    },
    paste() {
      this.show = false;
      if (null !== sessionStorage.getItem('fileBrowserBuffer')) {
        try {
          const buffer = JSON.parse(sessionStorage.getItem('fileBrowserBuffer'))

          this.$emit('paste', this.panel, this.bookmark, buffer)
        } catch (e) {}
      }
    },
    /**
     * Remove a file or a folder
     */
    remove() {
      this.show = false;
      this.$parent.fileRemoveShowModal()
    },
    /**
     * Rename file or folder
     */
    rename() {
      this.show = false;
      this.$parent.fileRenameShowModal()
    }
  }
}
</script>