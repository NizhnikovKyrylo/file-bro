<template>
  <div class="file-browser-panel-wrap">
    <div class="file-browser-bookmark-wrap">
      <ul>
        <li v-for="(bookmark, i) in bookmarks" :class="{active: bookmark.active}">
          {{ bookmark.name }}
        </li>
      </ul>
    </div>
    <table class="file-browser-panel-content">
      <thead>
      <tr>
        <th colspan="2"><span>Name</span></th>
        <th><span>Ext</span></th>
        <th><span>Size</span></th>
        <th><span>Date</span></th>
      </tr>
      </thead>
      <tbody>
      <template v-for="(file, i) in files">
        <PanelRow :file="file" :index="i" :side="side"/>
      </template>
      </tbody>
      <tfoot>
      <tr>
        <th colSpan="5">files: {{ counters.files }}&nbsp;&nbsp;&nbsp;folders: {{ counters.folders }}</th>
      </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>
import PanelRow from "./PanelRow.vue";

export default {
  components: {PanelRow},
  data() {
    return {
      counters: {
        files: 0,
        folders: 0
      }
    };
  },
  props: {
    bookmarks: {
      type: Array,
      default: []
    },
    files: {
      type: Array,
      default: []
    },
    side: {
      type: Number,
      required: true
    }
  },
  mounted() {
    for (let i = 0, n = this.files.length; i < n; i++) {
      const type = this.files[i].isDir ? 'folders' : 'files';
      this.counters[type]++;
    }
  }
};
</script>