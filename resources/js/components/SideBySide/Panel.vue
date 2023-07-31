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
        <PanelRow :file="file" :index="i" :side="side" @selectRow="rowClick"/>
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
import {storage} from "../../storage.js";
import {SideBySideOperations} from "../../mixin/side-by-side-operations.js";

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
    // Get file-browser options
    let options = storage.get('side-by-side')
    // Highlight start row
    this.forceSelectItem(this.$el, options.active, options[options.active ? 'right' : 'left']);
  },
  mixins: [SideBySideOperations],
  methods: {
    // Row click event
    rowClick(el) {
      if (null !== el.closest('tbody')) {
        // Get file-browser options
        let options = storage.get('side-by-side')
        // Clear "select" rows
        this.clearSelected(el);
        // Get panel side (0 - left; 1 - right) and row position
        let [side, index] = this.getRowSideAndIndex(el)
        // Save file browser options
        options.active = side;
        options[side ? 'right' : 'left'] = index;
        storage.set('side-by-side', options)
        // Highlight selected row
        this.forceSelectItem(this.$el, side, index)
      }
    }
  }
};
</script>