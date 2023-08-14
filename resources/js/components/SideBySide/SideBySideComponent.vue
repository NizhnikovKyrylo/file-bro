<template>
  <div data-type="side-by-side" @keyup="keyEvent" @keydown.enter.alt.exact.prevent="getInfo">
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
import Panel from "./Panel.vue";
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
    };
  },
  emits: ['showFileInfo'],
  props: {
    routes: {
      type: Object,
      required: true
    }
  },
  mixins: [SideBySideOperations],
  methods: {
    keyEvent(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      const key = e.key.toLowerCase();
      const options = storage.get('side-by-side');
      console.log(key);
      switch (key) {
        case ' ':this.insertAndGetSize(options);break;
        case 'arrowdown':this.moveDown(options);break;
        case 'arrowup':this.moveUp(options);break;
        case 'end':this.moveToEnd(options);break;
        case 'enter': this.folderData(options, e); break;
        case 'home':this.moveToStart(options);break;
        case 'insert':this.insert(options);break;
        case 'pagedown':this.pageDown(options);break;
        case 'pageup':this.pageUp(options);break;
        case 'tab':this.switchTab(options);break;
      }
    },
    folderData(options, e) {
      if (!e.altKey) {
        const side = options.active ? 'right' : 'left';
        const file = this.files[side][options[side]];

        if (file.isDir) {
          const path = file.nested ? file.path : file.path + file.basename

          this.request(Object.assign(this.routes.list, {data: {path: path}})).then(response => {
            if (200 === response.status) {
              this.bookmarks[side][0].name = file.basename;
              this.bookmarks[side][0].path = path + '/';

              let files = [];

              path !== '/' && files.push({
                path: "/",
                atime: file.atime,
                basename: file.basename,
                ctime: file.ctime,
                ext: "",
                filename: "[..]",
                isDir: true,
                'mime-type': null,
                mtime: file.mtime,
                nested: true,
                size: 4096,
                type: file.type
              })

              let index = 0;
              for (let i = 0, n = response.data.length; i < n; i++) {
                files.push(response.data[i])
                if (file.basename === files[i].basename) {
                  index = i;
                }
              }

              this.files[side] = files;

              options[side] = index;

              storage.set('side-by-side', options);
              setTimeout(() => {
                this.clearSelected(this.$el)
                this.forceSelectItem(this.$el, options.active, index);
              }, 20)
            }
          })
        }
      }
    },
    /**
     * Get folder or file info
     */
    getInfo() {
      const options = storage.get('side-by-side');
      const side = options.active ? 'right' : 'left';
      let file = this.files[side][options[side]];

      this.getFileInfo(file, this.routes.size).then(fileData => {
        this.$emit('showFileInfo', fileData)
      })
    },
    /**
     * Insert element and move down
     * @param options
     */
    insert(options) {
      this.insertElement(options);
      this.moveDown(options);
    },
    /**
     * Insert element and get element size
     * @param options
     */
    insertAndGetSize(options) {
      const side = options.active;
      const file = this.files[side ? 'right' : 'left'][options[side ? 'right' : 'left']];

      this.getFileInfo(file, this.routes.size).then(fileData => {
        this.files[side ? 'right' : 'left'][options[side ? 'right' : 'left']] = fileData
      })

      this.insertElement(options);
    },
    /**
     * Move down by pressing "Arrow Down" key
     * @param options
     */
    moveDown(options) {
      const side = options.active;
      const rowsCount = this.rowsCount(this.$el, side);
      options[side ? 'right' : 'left']++;
      if (options[side ? 'right' : 'left'] >= rowsCount) {
        options[side ? 'right' : 'left'] = rowsCount - 1;
      }

      this.moveAndFocus(this.$el, side, options[side ? 'right' : 'left'], false);

      storage.set('side-by-side', options);
    },
    /**
     * Move to the end of list by pressing "End" key
     * @param options
     */
    moveToEnd(options) {
      const side = options.active;
      options[side ? 'right' : 'left'] = this.rowsCount(this.$el, side) - 1;

      this.moveAndFocus(this.$el, side, options[side ? 'right' : 'left'], false);

      storage.set('side-by-side', options);
    },
    /**
     * Move to the start of list by pressing "Home" key
     * @param options
     */
    moveToStart(options) {
      const side = options.active;
      options[side ? 'right' : 'left'] = 0;

      this.moveAndFocus(this.$el, side, 0);

      storage.set('side-by-side', options);
    },
    /**
     * Move up by pressing "Arrow Up" key
     * @param options
     */
    moveUp(options) {
      const side = options.active;
      options[side ? 'right' : 'left']--;
      // Move selection up
      if (options[side ? 'right' : 'left'] < 1) {
        options[side ? 'right' : 'left'] = 0;
      }

      this.moveAndFocus(this.$el, side, options[side ? 'right' : 'left']);

      storage.set('side-by-side', options);
    },
    /**
     * Jump down by pressing "Page Down" key
     * @param options
     */
    pageDown(options) {
      const side = options.active;
      const rowsCount = this.rowsCount(this.$el, side);
      let shift = Math.floor(this.$el.querySelector('.file-browser-panel-content-body-wrap').offsetHeight / this.rowHeight(this.$el));

      options[side ? 'right' : 'left'] += shift;
      if (rowsCount <= options[side ? 'right' : 'left']) {
        options[side ? 'right' : 'left'] = rowsCount - 1;
      }

      this.moveAndFocus(this.$el, side, options[side ? 'right' : 'left'], false);

      storage.set('side-by-side', options);
    },
    /**
     * Jump up by pressing "Page Up" key
     * @param options
     */
    pageUp(options) {
      const side = options.active;
      let shift = Math.floor(this.$el.querySelector('.file-browser-panel-content-body-wrap').offsetHeight / this.rowHeight(this.$el));

      options[side ? 'right' : 'left'] -= shift;
      if (options[side ? 'right' : 'left'] < 1) {
        options[side ? 'right' : 'left'] = 0;
      }

      this.moveAndFocus(this.$el, side, options[side ? 'right' : 'left']);

      storage.set('side-by-side', options);
    },
    /**
     * Switch panel by press "tab" key
     */
    switchTab(options) {
      // Switch the active panel
      options.active = +!options.active;
      // Get next tab "tabindex" attribute
      const panels = this.$el.querySelectorAll('.file-browser-panel-wrap');
      // Set prev tab greater "tabindex" value
      panels[+!options.active].setAttribute('tabindex', +panels[options.active].getAttribute('tabindex') + 1);
      // Move selection to another tab
      this.moveSelection(this.$el, options.active, options[options.active ? 'right' : 'left']);

      storage.set('side-by-side', options);
    }
  },
  beforeMount() {
    if (null === storage.get('bookmarks')) {
      storage.set('bookmarks', {
        left: [{active: true, name: '/', path: '/'}],
        right: [{active: true, name: '/', path: '/'}]
      });
    }
    this.bookmarks = storage.get('bookmarks');
    this.files = storage.get('files');
  }
};
</script>