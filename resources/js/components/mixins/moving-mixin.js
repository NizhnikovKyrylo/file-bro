export const MovingMixin = {
  methods: {
    /**
     * Press "pageDown" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    jumpDown(bookmark, e) {
      // Increase position value for 20
      let position = bookmark.files.selected + 20;
      // Check the position is lower than the file number
      position > bookmark.files.list.length - 1 && (position = bookmark.files.list.length - 1);
      // Move to the position below, Scroll to element
      this.scrollToElement(bookmark.files.selected = position, e);
      return this;
    },
    /**
     * Press "pageUp" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    jumpUp(bookmark, e) {
      // Decrease position value for 20
      let position = bookmark.files.selected - 20;
      // Check the position is greater than 0
      position < 0 && (position = 0);
      // Move to the position below, Scroll to element
      this.scrollToElement(bookmark.files.selected = position, e);
      return this;
    },
    /**
     * Press "home" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveToBegin(bookmark, e) {
      this.scrollToElement(bookmark.files.selected = 0, e);
      return this;
    },
    /**
     * Press "end" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveToEnd(bookmark, e) {
      const files = bookmark.files.list.length - 1;
      this.scrollToElement(bookmark.files.selected = files > 0 ? files : 0, e);
      return this;
    },
    /**
     * Press "Arrow down" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveDown(bookmark, e) {
      // If element index is less than files number, move the selection below
      bookmark.files.list.length - 1 > bookmark.files.selected && bookmark.files.selected++;
      // Scroll to element
      this.scrollToElement(bookmark.files.selected, e);
      return this;
    },
    /**
     * Press "Arrow up" handler
     * @param {object} bookmark
     * @param {event} e
     * @returns {this}
     */
    moveUp(bookmark, e) {
      // If element index is greater than 0, move the selection upper
      bookmark.files.selected > 0 && bookmark.files.selected--;
      // Scroll to element
      this.scrollToElement(bookmark.files.selected, e);
      return this;
    },
  }
}