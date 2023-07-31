import {FileHelper} from "./file-helper.js";
import {storage} from "../storage.js";

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

      panels.forEach(panel => Array.from(panel.querySelectorAll('tbody tr')).forEach(node => node.classList.remove('select')));

      return panels;
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
        .querySelectorAll('tbody tr')[i]
        .classList.add('select');
    },
    /**
     * Get element index
     * @param el
     * @returns {number}
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
      ]
    },
    moveSelection(el, side, index) {
      // Select the active panel
      const parent = el.querySelectorAll('.file-browser-panel-wrap')[side];
      // Unselect all rows
      this.clearSelected(parent)
      // Select current row
      this.forceSelectItem(parent, side, index)
    }
  }
};