- Top panel bar
  - Elements
  - Reload
  - tree view
  - list view
  - side by side
- Tree view
  - HTML structure
  - Get files
  ```
    panels: {
      left: {
        folders: []
        active: path string (string)
      }
      right: {
        active: 0...n acive file index (int)
        files: []
      }
    }
  ```
- List view
  - HTML structure
  - Get files
  ```
    panels: {
      active: acive file path (string) 
      files: []
    }
  ```
- Side-by-side
  - HTML structure
  - Get files
    ```
    panels: {
      left: {
        active: true/false (the panel is active),
        bookmarks: [
          {
            active: true/false (the bookmark is active),
            name: Folder name (string)
            path: '/some path', (string)
            locked: true/false,
            files: {
              active: 0...n acive file index (int)
              list: []
            }
          }
        ]
      }
      },
      right: {
       --- // ---
      }
    }
    ```
  - bookmarks
    - context menu
    - copy
    - rename
    - lock/unlock
    - close
    - close all
  - click panel to select row
  - press "tab"
  - move "down" button
  - move "up" button
  - pageDown
  - pageUp
  - end
  - home
  - insert
  - del
  - insert with "space" + get size
  - get file properties via alt+enter
  - go into folder
    - enter
    - right button
  - go upper folder
    - press "left" button
    - press "backspace" button
  - mouse double-click
    - folder
    - file
  - mouse left click
    - with shift
    - with control
  - Sort/Change file order
    - name
    - ext
    - size
    - date
  - bottom panel
    - rename F2
    - view file F3
    - upload file F4
    - copy F5
    - move F6
    - create folder F7
    - remove F8
  - context menu
    - open
    - edit
    - ========
    - move
    - copy
    - delete
    - rename
    - ========
    - cut
    - copy
    - paste
    - ========
    - show file properties