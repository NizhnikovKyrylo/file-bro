import {FileHelper} from "./file-helper.js";

export const SideBySideOperations = {
  mixins: [FileHelper],
  methods: {
    /**
     * Clear "select" class from all rows and return panel list
     * @param el
     * @returns {*}
     */
    clearSelected: el => {
      const panels = Array.from(el.closest('.file-browser-panels-wrap').querySelectorAll('.file-browser-panel-wrap'));

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
      const panel = el.querySelectorAll('.file-browser-panel-wrap')[side];
      const scrollPosition = panel.querySelector('.file-browser-panel-content-body-wrap').scrollTop;
      const elementHeight = el.querySelector('.file-browser-panel-content-body-row').offsetHeight;

      const offset = directionUp ? scrollPosition : panel.offsetHeight - scrollPosition;

      // Focus on the selected element
      if (offset < (2 + index) * elementHeight) {
        panel.querySelectorAll('.file-browser-panel-content-body-wrap .file-browser-panel-content-body-row')[index].scrollIntoView(!0);
      }
    },
    /**
     * Highlight selected element
     * @param el
     * @param {int} side
     * @param {int} i
     */
    forceSelectItem(el, side, i) {
      el.closest('.file-browser-panels-wrap')
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
    }
  }
};