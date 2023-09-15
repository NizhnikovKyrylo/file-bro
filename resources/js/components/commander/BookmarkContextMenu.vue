<template>
  <ul class="bookmark-context-menu" v-if="show" :style="{left: left + 'px'}" v-click-out-side="() => {show = false}">
    <li @click="create"><span>New</span></li>
    <li @click="copy"><span>Copy</span></li>
    <li @click="rename"><span>Rename</span></li>
    <li><span>Lock/Unlock</span></li>
    <li><span>Close</span></li>
    <li><span>Close All</span></li>
  </ul>
</template>

<script>
import clickOutSide from "../mixins/click-outside.js";

export default {
  directives: {
    clickOutSide,
  },
  data() {
    return {
      index: 0,
      left: 10,
      panel: 'left',
      show: false
    }
  },
  emits: ['new', 'rename'],
  methods: {
    /**
     * Make a copy of a bookmark in the another tab
     */
    copy() {
      this.$emit('new', {i: this.index, panel: this.panel === 'left' ? 'right' : 'left'})
    },
    /**
     * Open a new bookmark
     */
    create() {
      this.$emit('new', {i: this.index, panel: this.panel})
    },
    /**
     * Rename a bookmark
     */
    rename() {
      this.$emit('rename', {i: this.index, panel: this.panel})
      this.show = false
    }
  }
}
</script>