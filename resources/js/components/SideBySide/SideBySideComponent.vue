<template>
  <div data-type="side-by-side">
    <div class="file-browser-panels-wrap">
      <Panel :files="files.left" :bookmarks="bookmarks.left" :side="0"/>
      <Panel :files="files.right" :bookmarks="bookmarks.right" :side="1"/>
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
  mounted() {
    document.addEventListener('keyup', e => {
      const key = e.key.toLowerCase();

      console.log(key);
    })
  }
}
</script>