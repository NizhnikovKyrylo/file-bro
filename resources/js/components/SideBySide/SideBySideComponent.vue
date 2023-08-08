<template>
  <div data-type="side-by-side">
    <div class="file-browser-panels-wrap">
      <Panel tabindex="0" :files="files.left" :bookmarks="bookmarks.left" :side="0"/>
      <Panel tabindex="1" :files="files.right" :bookmarks="bookmarks.right" :side="1"/>
    </div>
    <div class="file-browser-controls-wrap">
      <button tabindex="-1" name="rename" type="button"><span>Rename</span></button>
      <button tabindex="-1" name="view" type="button"><span>View</span></button>
      <button tabindex="-1" name="upload" type="button"><span>Upload</span></button>
      <button tabindex="-1" name="copy" type="button"><span>Copy</span></button>
      <button tabindex="-1" name="move" type="button"><span>Move</span></button>
      <button tabindex="-1" name="createDir" type="button"><span>Directory</span></button>
      <button tabindex="-1" name="delete" type="button"><span>Delete</span></button>
    </div>
  </div>
</template>

<script>
import {storage} from "../../storage.js";
import Panel from "./Panel.vue"
import {SideBySideOperations} from "../../mixin/side-by-side-operations.js";

export default {
  components: {Panel},
  data() {
    return {
      bookmarks: {
        left: [],
        right: []
      },
      files: {
        left: [],
        right: []
      }
    }
  },
  beforeMount() {
    if (null === storage.get('bookmarks')) {
      storage.set('bookmarks', {
        left: [{active: true, name: '/', path: '/'}],
        right: [{active: true, name: '/', path: '/'}]
      })
    }
    this.bookmarks = storage.get('bookmarks');
    this.files = storage.get('files');
  },
  mixins: [SideBySideOperations],
  mounted() {
    document.addEventListener('keyup', e => {
      const key = e.key.toLowerCase();
      // Press "tab" key
      if ('tab' === key && !e.altKey && !e.ctrlKey && !e.shiftKey) {
        const options = storage.get('side-by-side');
        // Switch the active panel
        options.active = +!options.active;
        // Get next tab "tabindex" attribute
        const panels = this.$el.querySelectorAll('.file-browser-panel-wrap')
        // Set prev tab greater "tabindex" value
        panels[+!options.active].setAttribute('tabindex', +panels[options.active].getAttribute('tabindex') + 1)
        // Move selection to another tab
        this.moveSelection(this.$el, options.active, options[options.active ? 'right' : 'left'])

        storage.set('side-by-side', options)
      }
      // Press arrow up
      if ('arrowup' === key) {
        const options = storage.get('side-by-side');
        const side = options.active;
        options[side ? 'right' : 'left']--;
        // Move selection up
        options[side ? 'right' : 'left'] >= 0 && this.moveSelection(this.$el, side, options[side ? 'right' : 'left'])

        storage.set('side-by-side', options)
      }
      // Press arrow down
      if ('arrowdown' === key) {
        const options = storage.get('side-by-side');
        const side = options.active;
        const rowsCount = this.$el.querySelectorAll('.file-browser-panel-wrap')[side].querySelectorAll('tbody tr').length;
        options[side ? 'right' : 'left']++;
        options[side ? 'right' : 'left'] < rowsCount && this.moveSelection(this.$el, side, options[side ? 'right' : 'left'])

        storage.set('side-by-side', options)
      }
    })
  }
}
</script>