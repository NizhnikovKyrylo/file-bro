export const FileHelper = {
  data() {
    return {
      mimeTypes: {
        // archive
        'file-archive': ['application/gzip', 'application/java-archive', 'application/rar', 'application/zip', 'application/x-bzip2'],
        // audio
        'file-audio': ['audio/aac', 'audio/ogg', 'audio/flac', 'audio/midi', 'audio/mpeg', 'audio/x-wav', 'audio/aifc', 'audio/x-aiff'],
        // database
        'file-database': [
          'text/csv',
          'application/csv',
          'application/vnd.sun.xml.base',
          'application/vnd.oasis.opendocument.base',
          'application/vnd.oasis.opendocument.database',
          'application/sql'
        ],
        //font
        'file-text': ['font/ttf', 'font/woff', 'font/woff2', 'font/opentype', 'application/vnd.ms-fontobject'],
        //images
        'file-image': ['image/gif', 'image/jp2', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/tiff', 'image/bmp'],
        // pdf
        'file-pdf': ['application/pdf'],
        // scripts
        'file-code': [
          'application/ecmascript',
          'application/hta',
          'application/xhtml+xml',
          'application/xml',
          'application/xslt+xml',
          'text/css',
          'text/x-csrc',
          'text/x-c++src',
          'application/x-asp',
          'text/x-python'
        ],
        // spreadsheet
        'file-cell': [
          'application/vnd.ms-excel',
          'application/vnd.ms-excel.sheet.macroEnabled.12',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'application/vnd.oasis.opendocument.spreadsheet'
        ],
        // text
        'file-alt': ['text/plain', 'text/html', 'text/markdown', 'application/json', 'application/x-x509-ca-cert'],
        // text processor
        'file-word': [
          'application/msword',
          'application/rtf',
          'text/rtf',
          'text/richtext',
          'application/vnd.ms-word.document.macroEnabled.12',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'application/vnd.oasis.opendocument.text',
          'application/vnd.oasis.opendocument.text-master',
          'application/abiword'
        ],
        // video
        'file-video': [
          'video/avi',
          'video/mpeg',
          'video/mp4',
          'video/quicktime',
          'video/ogg',
          'video/webm',
          'video/x-flv',
          'video/x-msvideo',
          'video/x-matroska',
          'video/x-ms-wmv'
        ]
      }
    };
  },
  methods: {
    /**
     * Get file icon by file properties
     * @param file
     * @returns {string|string|string}
     */
    fileIcon(file) {
      if (file.isDir) {
        return 'folder-icon';
      } else {
        const icon = Object.keys(this.mimeTypes).find(icon => this.mimeTypes[icon].includes(file['mime-type']));
        return icon ? icon : 'file-regular';
      }
    },
    /**
     * Format bytes as human-readable text.
     * @param {int} size
     * @returns {string}
     */
    fileSize(size) {
      const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
      const value = size > 0 ? Math.floor(Math.log(Math.abs(size)) / Math.log(1024)) : 0;
      return (size > 0 ? (size / Math.pow(1024, value)).toFixed(1) : 0) + ' ' + units[value];
    },
    /**
     * Convert Unix timestamp to date format j.M.Y H:i:s
     * @param {int} time
     * @returns {string}
     */
    formatDate(time) {
      const date = new Date(time * 1000);
      return `${date.getDate()}.${date.toLocaleString('default', {month: 'short'})}.${date.getFullYear()}` + ` ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
    }
  }
};