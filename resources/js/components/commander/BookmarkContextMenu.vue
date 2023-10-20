<template>
  <ul class="context-menu" v-if="show" :style="{left: left + 'px'}" v-click-out-side="() => {show = false}">
    <li @click="create"><span>New</span></li>
    <li @click="copy"><span>Copy</span></li>
    <li @click="rename"><span>Rename</span></li>
    <li @click="lock"><span>Lock/Unlock</span></li>
    <li @click="close"><span>Close</span></li>
    <li @click="closeAll"><span>Close All</span></li>
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
      index: 0,
      left: 10,
      panel: 'left',
      show: false
    };
  },
  emits: ['close', 'closeAll', 'new', 'rename', 'toggleLock'],
  methods: {
    /**
     * Close a bookmark
     */
    close() {
      this.$emit('close', {i: this.index, panel: this.panel});
      this.show = false;
    },
    /**
     * Close all bookmarks except the current one
     */
    closeAll() {
      this.$emit('closeAll', this.panel)
      this.show = false;
    },
    /**
     * Make a copy of a bookmark in the another tab
     */
    copy() {
      this.$emit('new', {i: this.index, panel: this.panel === 'left' ? 'right' : 'left'});
      this.show = false;
    },
    /**
     * Open a new bookmark
     */
    create() {
      this.$emit('new', {i: this.index, panel: this.panel});
      this.show = false;
    },
    /**
     * Toggle the bookmark "lock" status
     */
    lock() {
      this.$emit('toggleLock', {i: this.index, panel: this.panel});
      this.show = false;
    },
    /**
     * Rename a bookmark
     */
    rename() {
      this.$emit('rename', {i: this.index, panel: this.panel});
      this.show = false;
    }
  }
};
</script>