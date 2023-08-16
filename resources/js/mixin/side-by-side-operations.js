import {FileHelper} from "./file-helper.js";
import {Requests} from "./requests.js";
import {storage} from "../storage.js";

export const SideBySideOperations = {
  mixins: [FileHelper, Requests],
  methods: {
    /**
     * Clear "select" class from all rows and return panel list
     * @param el
     * @returns {*}
     */
    clearSelected: el => {
      const parent = null === el.closest('.file-browser-panels-wrap')
        ? el.querySelector('.file-browser-panels-wrap')
        : el.closest('.file-browser-panels-wrap')

      const panels = Array.from(parent.querySelectorAll('.file-browser-panel-wrap'));

      panels.forEach(
        panel => Array
          .from(panel.querySelectorAll('.file-browser-panel-content-body-wrap .file-browser-panel-content-body-row'))
          .forEach(node => node.classList.remove('select'))
      );

      return panels;
    },
    /**
     * Focus on the file row
     * @param el
     * @param {int} side
     * @param {int} index
     * @param {boolean} directionUp
     */
    focusElement(el, side, index, directionUp = true) {
      const parent = null === el.closest('.file-browser-panels-wrap')
        ? el.querySelector('.file-browser-panels-wrap')
        : el.closest('.file-browser-panels-wrap')

      const panel = parent.querySelectorAll('.file-browser-panel-wrap')[side];
      const scrollPosition = panel.querySelector('.file-browser-panel-content-body-wrap').scrollTop;
      const elementHeight = this.rowHeight(el);

      const condition = directionUp
        ? (1 + index) * elementHeight <= scrollPosition
        : (2 + index) * elementHeight > (panel.offsetHeight - scrollPosition);

      // Focus on the selected element
      condition && panel
        .querySelectorAll('.file-browser-panel-content-body-wrap .file-browser-panel-content-body-row')[index]
        .scrollIntoView(true);
    },
    /**
     * Highlight selected element
     * @param el
     * @param {int} side
     * @param {int} i
     */
    forceSelectItem(el, side, i) {
      const parent = null === el.closest('.file-browser-panels-wrap')
        ? el.querySelector('.file-browser-panels-wrap')
        : el.closest('.file-browser-panels-wrap')

      parent
        .querySelectorAll('.file-browser-panel-wrap')[side]
        .querySelectorAll('.file-browser-panel-content-body-wrap .file-browser-panel-content-body-row')[i]
        .classList.add('select');
    },
    /**
     * Get element index
     * @param el
     * @returns {int}
     */
    getIndex: el => Object.values(el.parentNode.childNodes).filter(value => value.nodeType === 1).indexOf(el),
    /**
     * Get file size
     * @param file
     * @param route
     */
    getFileInfo(file, route) {
      return new Promise(resolve => {
        if (file.isDir && !file.hasOwnProperty('recognized')) {
          this.request(Object.assign(route, {data: {path: file.path + file.basename}})).then(response => {
            if (200 === response.status) {
              resolve(Object.assign(file, response.data, {recognized: true}));
            }
          });
        } else {
          resolve(file)
        }
      })
    },
    /**
     * Get folder tree
     * @param file
     * @returns {Promise<unknown>}
     */
    getFolderContent(file) {
      return new Promise (resolve  => {
        let path = file.filename === '[..]' ? file.path.replace(/\/$/, "").split('/').slice(0, -1).join('/') : file.path + file.basename;
        if (!path || !path.length) {
          path = '/';
        }

        this.request(Object.assign(this.routes.list, {data: {path: path}})).then(response => {
          if (200 === response.status) {
            let files = [];

            path !== '/' && files.push({
              path: path,
              atime: file.atime,
              basename: file.basename,
              ctime: file.ctime,
              ext: "",
              filename: "[..]",
              isDir: true,
              'mime-type': null,
              mtime: file.mtime,
              size: 4096,
              type: file.type
            })

            let index = 0;
            for (let i = 0, n = response.data.length; i < n; i++) {
              files.push(response.data[i])
              if (file.path === (files[i].path + files[i].basename)) {
                index = i;
              }
            }

            resolve({
              files: files,
              path: path,
              index: index
            })
          }
        })
      })
    },
    /**
     * Get element side and index as array
     * @param el
     * @returns {number[]}
     */
    getRowSideAndIndex(el) {
      return [
        this.getIndex(el.closest('.file-browser-panel-wrap')),
        this.getIndex(el)
      ];
    },
    /**
     * Add or remove "insert" class of the element
     * @param options
     */
    insertElement(options) {
      const side = options.active;
      this.$el.querySelectorAll('.file-browser-panel-wrap')[side]
        .querySelectorAll('.file-browser-panel-content-body-wrap .file-browser-panel-content-body-row')[options[side ? 'right' : 'left']]
        .classList.toggle('insert')
    },
    /**
     * Select row
     * @param el
     * @param side
     * @param index
     */
    moveSelection(el, side, index) {
      // Select the active panel
      const parent = el.querySelectorAll('.file-browser-panel-wrap')[side];
      // Unselect all rows
      this.clearSelected(parent);
      // Select current row
      this.forceSelectItem(parent, side, index);
    },
    moveAndFocus(el, side, index, directionUp = true) {
      this.moveSelection(this.$el, side, index);

      this.focusElement(this.$el, side, index, directionUp);
    },
    /**
     * Get rows number
     * @param el
     * @param side
     * @returns {int}
     */
    rowsCount(el, side) {
      return el.querySelectorAll('.file-browser-panel-wrap')[side]
        .querySelectorAll('.file-browser-panel-content-body-wrap .file-browser-panel-content-body-row')
        .length;
    },
    /**
     * Get row height
     * @param el
     * @returns {number}
     */
    rowHeight(el) {
      return el.querySelector('.file-browser-panel-content-body-row').offsetHeight;
    }
  }
};